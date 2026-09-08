const ALLOWED = new Set(['/v1/register', '/v1/login', '/v1/account']);

export default {
  async fetch(request, env) {
    const url = new URL(request.url);

    if (request.method === 'OPTIONS') {
      return cors(new Response(null, { status: 204 }));
    }

    if (url.pathname === '/' || url.pathname === '/health') {
      return cors(Response.json({ ok: true, app: env.APP_NAME || 'cloub' }));
    }

    if (!ALLOWED.has(url.pathname)) {
      return cors(Response.json({ ok: false, error: 'not_found' }, { status: 404 }));
    }

    const origin = (env.CLOUB_ORIGIN || 'https://cloub.io').replace(/\/$/, '');
    const action = url.pathname.replace('/v1/', '');
    const clientIp = request.headers.get('CF-Connecting-IP') || '';

    const headers = {
      'content-type': 'application/json',
      'x-boybios-secret': env.BOYBIOS_WORKER_SECRET || '',
      'x-boybios-client-ip': clientIp,
      'user-agent': request.headers.get('user-agent') || 'cloub/1.0',
      'accept-language': request.headers.get('accept-language') || 'en',
    };

    const authorization = request.headers.get('authorization');
    if (authorization) {
      headers.authorization = authorization;
    }

    const body = request.method === 'GET' || request.method === 'HEAD' ? undefined : await request.text();

    const upstream = await fetch(`${origin}/boybios-api/${action}`, {
      method: request.method,
      headers,
      body,
    });

    const text = await upstream.text();
    return cors(new Response(text, {
      status: upstream.status,
      headers: { 'content-type': upstream.headers.get('content-type') || 'application/json' },
    }));
  },
};

function cors(response) {
  response.headers.set('access-control-allow-origin', '*');
  response.headers.set('access-control-allow-headers', 'content-type, authorization');
  response.headers.set('access-control-allow-methods', 'GET, POST, PATCH, PUT, OPTIONS');
  return response;
}
