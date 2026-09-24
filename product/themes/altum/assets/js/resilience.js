(function (window, document) {
  'use strict';

  var cfg = window.ALTUM_RESILIENCE || {};
  if (!cfg.enabled) return;

  var DB_NAME = cfg.cache_prefix || 'cloub-resilience-v1';
  var STORE = 'pages';
  var PASS_KEY = DB_NAME + ':passcode';
  var PASS_UNLOCKED = DB_NAME + ':unlocked';

  function hostAllowed() {
    var hosts = cfg.official_hosts || [];
    if (!hosts.length) return true;
    var host = (location.hostname || '').toLowerCase();
    return hosts.some(function (allowed) {
      allowed = String(allowed).toLowerCase();
      return host === allowed || host.endsWith('.' + allowed);
    });
  }

  function openDb() {
    return new Promise(function (resolve, reject) {
      if (!window.indexedDB) return reject(new Error('no idb'));
      var req = indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = function () {
        var db = req.result;
        if (!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE);
      };
      req.onsuccess = function () { resolve(req.result); };
      req.onerror = function () { reject(req.error); };
    });
  }

  function idbPut(key, value) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, 'readwrite');
        tx.objectStore(STORE).put(value, key);
        tx.oncomplete = function () { resolve(); };
        tx.onerror = function () { reject(tx.error); };
      });
    });
  }

  function idbGet(key) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, 'readonly');
        var req = tx.objectStore(STORE).get(key);
        req.onsuccess = function () { resolve(req.result || null); };
        req.onerror = function () { reject(req.error); };
      });
    });
  }

  function pageKey() {
    return (cfg.is_dashboard ? 'dash:' : 'bio:') + location.pathname + location.search;
  }

  function ensureBanner() {
    var el = document.getElementById('cloub-resilience-banner');
    if (el) return el;
    el = document.createElement('div');
    el.id = 'cloub-resilience-banner';
    el.setAttribute('role', 'status');
    el.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:2147483000;display:none;padding:.55rem 1rem;font:600 13px/1.35 system-ui,sans-serif;text-align:center;color:#fff;background:#1b4332;box-shadow:0 2px 10px rgba(0,0,0,.2)';
    document.documentElement.appendChild(el);
    return el;
  }

  function showBanner(text, bg) {
    if (!cfg.connection_banner) return;
    var el = ensureBanner();
    el.textContent = text || '';
    el.style.background = bg || '#1b4332';
    el.style.display = text ? 'block' : 'none';
    document.documentElement.style.setProperty('--cloub-resilience-banner-h', text ? el.offsetHeight + 'px' : '0px');
  }

  function setOnlineUi() {
    if (!navigator.onLine) {
      showBanner(cfg.offline_message || 'Offline — showing saved content', '#6c757d');
    } else {
      showBanner('');
    }
  }

  function snapshotPage() {
    try {
      var html = document.documentElement.outerHTML;
      /* Keep snapshots reasonably small */
      if (html.length > 1500000) html = html.slice(0, 1500000);
      var payload = {
        html: html,
        title: document.title,
        saved_at: Date.now(),
        url: location.href
      };
      idbPut(pageKey(), payload).catch(function () {});
    } catch (e) {}
  }

  function restoreIfNeeded() {
    if (navigator.onLine) return;
    idbGet(pageKey()).then(function (payload) {
      if (!payload || !payload.html) return;
      /* Only restore if body looks empty/broken */
      if (document.body && document.body.children.length > 2) return;
      document.open();
      document.write(payload.html);
      document.close();
      showBanner(cfg.offline_message || 'Offline — showing saved content', '#6c757d');
    }).catch(function () {});
  }

  function registerSw() {
    if (!('serviceWorker' in navigator)) return;
    if (!hostAllowed()) return;
    var swUrl = cfg.sw_url || (cfg.site_url + 'sw.js');
    navigator.serviceWorker.register(swUrl, { scope: '/' }).catch(function () {});
  }

  function mirrorGuard() {
    if (hostAllowed()) return;
    var bar = ensureBanner();
    bar.style.background = '#9b2226';
    bar.innerHTML = (cfg.mirror_warning || 'Unofficial host detected.') +
      ' <a href="' + (cfg.authenticity_url || '#') + '" style="color:#fff;text-decoration:underline">' +
      (cfg.verify_label || 'Verify') + '</a>';
    bar.style.display = 'block';
  }

  function iframeGuard() {
    if (!cfg.iframe_guard) return;
    try {
      if (window.top !== window.self) {
        var parentHost = '';
        try { parentHost = window.top.location.hostname; } catch (e) { parentHost = 'unknown'; }
        var allowed = hostAllowed() && (parentHost === 'unknown' || hostAllowed.hostAllowed === undefined);
        /* If framed by a different origin, warn */
        if (parentHost === 'unknown' || (parentHost && parentHost !== location.hostname)) {
          showBanner((cfg.mirror_warning || 'Embedded on another site.') + ' ' + (cfg.verify_label || ''), '#9b2226');
        }
      }
    } catch (e) {}
  }

  function sha256(text) {
    var data = new TextEncoder().encode(text);
    return crypto.subtle.digest('SHA-256', data).then(function (buf) {
      return Array.from(new Uint8Array(buf)).map(function (b) { return b.toString(16).padStart(2, '0'); }).join('');
    });
  }

  function passcodeLock() {
    if (!cfg.passcode_enabled || !cfg.is_dashboard) return;
    var hash = localStorage.getItem(PASS_KEY);
    if (!hash) return;
    if (sessionStorage.getItem(PASS_UNLOCKED) === '1') return;

    var overlay = document.createElement('div');
    overlay.id = 'cloub-passcode-lock';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:2147483646;background:rgba(10,20,15,.92);display:flex;align-items:center;justify-content:center;padding:1rem';
    overlay.innerHTML = '<form id="cloub-passcode-form" style="background:#fff;border-radius:12px;padding:1.25rem;max-width:320px;width:100%;font-family:system-ui,sans-serif">' +
      '<h2 style="margin:0 0 .5rem;font-size:1.1rem">Unlock</h2>' +
      '<p style="margin:0 0 .75rem;color:#555;font-size:.9rem">Enter your local device passcode.</p>' +
      '<input type="password" name="pass" autocomplete="current-password" style="width:100%;padding:.55rem .7rem;margin-bottom:.75rem;border:1px solid #ccc;border-radius:8px" required />' +
      '<button type="submit" style="width:100%;padding:.6rem;border:0;border-radius:8px;background:#1b4332;color:#fff;font-weight:600">Unlock</button>' +
      '<p id="cloub-passcode-err" style="display:none;color:#9b2226;font-size:.85rem;margin:.5rem 0 0"></p>' +
      '</form>';
    document.documentElement.appendChild(overlay);
    overlay.querySelector('form').addEventListener('submit', function (ev) {
      ev.preventDefault();
      var pass = overlay.querySelector('[name=pass]').value || '';
      sha256(pass).then(function (digest) {
        if (digest === hash) {
          sessionStorage.setItem(PASS_UNLOCKED, '1');
          overlay.remove();
        } else {
          var err = overlay.querySelector('#cloub-passcode-err');
          err.style.display = 'block';
          err.textContent = 'Wrong passcode';
        }
      });
    });
  }

  function softRefresh() {
    if (!cfg.soft_refresh) {
      if (navigator.onLine) location.reload();
      return;
    }
    showBanner(cfg.updating_message || 'Updating…', '#2d6a4f');
    /* Silent sync: re-cache shell/assets via SW; avoid hard reload unless dashboard asked */
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
      navigator.serviceWorker.controller.postMessage({ type: 'cloub-soft-refresh' });
    }
    setTimeout(function () {
      snapshotPage();
      setOnlineUi();
    }, 800);
  }

  function refreshOfficialHosts() {
    if (!cfg.hosts_json_url || !navigator.onLine) return;
    fetch(cfg.hosts_json_url, { credentials: 'omit', cache: 'no-cache' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) {
        if (!data || !Array.isArray(data.hosts) || !data.hosts.length) return;
        cfg.official_hosts = data.hosts;
        try { localStorage.setItem(DB_NAME + ':hosts', JSON.stringify(data.hosts)); } catch (e) {}
        mirrorGuard();
      })
      .catch(function () {});
  }

  try {
    var cachedHosts = JSON.parse(localStorage.getItem(DB_NAME + ':hosts') || 'null');
    if (Array.isArray(cachedHosts) && cachedHosts.length) cfg.official_hosts = cachedHosts;
  } catch (e) {}

  window.CloubResilience = {
    setPasscode: function (pass) {
      if (!pass) {
        localStorage.removeItem(PASS_KEY);
        return Promise.resolve();
      }
      return sha256(String(pass)).then(function (digest) {
        localStorage.setItem(PASS_KEY, digest);
      });
    },
    clearPasscode: function () {
      localStorage.removeItem(PASS_KEY);
      sessionStorage.removeItem(PASS_UNLOCKED);
    },
    snapshotNow: snapshotPage,
    softRefresh: softRefresh
  };

  document.addEventListener('DOMContentLoaded', function () {
    mirrorGuard();
    iframeGuard();
    passcodeLock();
    setOnlineUi();
    registerSw();
    restoreIfNeeded();
    refreshOfficialHosts();
    /* Save after paint / soft idle */
    setTimeout(snapshotPage, 2500);
  });

  window.addEventListener('online', softRefresh);
  window.addEventListener('offline', setOnlineUi);
  window.addEventListener('pagehide', snapshotPage);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible' && navigator.onLine) {
      refreshOfficialHosts();
      snapshotPage();
    }
  });
})(window, document);
