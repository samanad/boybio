<?php
/*
 * Dynamic service worker: offline cache + official-host lock (Telegram-like).
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class ServiceWorker extends Controller {

    public function index() {

        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $hosts = function_exists('resilience_official_hosts') ? resilience_official_hosts() : [];
        $hosts_json = json_encode(array_values($hosts), JSON_UNESCAPED_SLASHES);
        $cache_name = 'cloub-resilience-v1';
        $offline_url = url('official?offline=1');

        echo <<<JS
/* Cloub resilience service worker */
const OFFICIAL_HOSTS = {$hosts_json};
const CACHE_NAME = '{$cache_name}';
const OFFLINE_HINT = '{$offline_url}';

function hostAllowed(hostname) {
  if (!OFFICIAL_HOSTS || !OFFICIAL_HOSTS.length) return true;
  hostname = (hostname || '').toLowerCase().replace(/:\\d+\$/, '');
  return OFFICIAL_HOSTS.some(function (allowed) {
    allowed = String(allowed).toLowerCase();
    return hostname === allowed || hostname.endsWith('.' + allowed);
  });
}

self.addEventListener('install', function (event) {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE_NAME).then(function (cache) {
    return cache.addAll(['/']).catch(function () { return undefined; });
  }));
});

self.addEventListener('activate', function (event) {
  event.waitUntil((async function () {
    const keys = await caches.keys();
    await Promise.all(keys.filter(function (k) { return k !== CACHE_NAME; }).map(function (k) { return caches.delete(k); }));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', function (event) {
  const request = event.request;
  if (request.method !== 'GET') return;

  let url;
  try { url = new URL(request.url); } catch (e) { return; }

  if (!hostAllowed(url.hostname)) {
    event.respondWith(new Response(
      '<!doctype html><meta charset="utf-8"><title>Unofficial mirror</title><body style="font-family:sans-serif;padding:2rem"><h1>Unofficial host</h1><p>This copy is not on an official Cloub domain. Open the authentic site.</p><p><a href="' + OFFLINE_HINT + '">Verify authenticity</a></p></body>',
      { status: 403, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    ));
    return;
  }

  const isNavigate = request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html');
  const isAsset = /\\.(css|js|png|jpe?g|gif|webp|svg|woff2?|ttf|ico)(\\?|$)/i.test(url.pathname);

  if (isNavigate) {
    event.respondWith((async function () {
      try {
        const fresh = await fetch(request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(request, fresh.clone());
        return fresh;
      } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        const fallback = await caches.match('/');
        if (fallback) return fallback;
        return new Response(
          '<!doctype html><meta charset="utf-8"><title>Offline</title><body style="font-family:sans-serif;padding:2rem"><h1>You are offline</h1><p>Showing cached content when available. Reconnect to refresh.</p><p><a href="' + OFFLINE_HINT + '">Official domains</a></p></body>',
          { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      }
    })());
    return;
  }

  if (isAsset) {
    event.respondWith((async function () {
      const cache = await caches.open(CACHE_NAME);
      const cached = await cache.match(request);
      if (cached) {
        fetch(request).then(function (fresh) { if (fresh && fresh.ok) cache.put(request, fresh.clone()); }).catch(function () {});
        return cached;
      }
      try {
        const fresh = await fetch(request);
        if (fresh && fresh.ok) cache.put(request, fresh.clone());
        return fresh;
      } catch (err) {
        return cached || Response.error();
      }
    })());
  }
});

self.addEventListener('message', function (event) {
  if (!event.data || event.data.type !== 'cloub-soft-refresh') return;
  event.waitUntil((async function () {
    const cache = await caches.open(CACHE_NAME);
    const keys = await cache.keys();
    await Promise.all(keys.slice(0, 40).map(function (req) {
      return fetch(req).then(function (fresh) {
        if (fresh && fresh.ok) return cache.put(req, fresh.clone());
      }).catch(function () {});
    }));
  })());
});
JS;

        /* Optionally append uploaded PWA sw.js body if present */
        if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled) {
            $service_worker_file = UPLOADS_PATH . \Altum\Uploads::get_path('pwa') . 'sw.js';
            if(file_exists($service_worker_file)) {
                echo "\n/* --- bundled PWA sw.js --- */\n";
                readfile($service_worker_file);
            }
        }

        die();
    }
}
