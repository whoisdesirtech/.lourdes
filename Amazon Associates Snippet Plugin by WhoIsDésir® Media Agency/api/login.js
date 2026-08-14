'use strict';

const auth = require('../private/auth');

module.exports = function (req, res) {
  if (req.method !== 'POST') {
    return auth.json(res, 405, { ok: false, error: 'Method not allowed' });
  }

  let body = '';
  req.on('data', (chunk) => {
    body += chunk;
    if (body.length > 1024 * 10) {
      req.destroy();
    }
  });

  req.on('end', () => {
    let password = '';
    try {
      const parsed = JSON.parse(body || '{}');
      password = typeof parsed.password === 'string' ? parsed.password : '';
    } catch (e) {
      return auth.json(res, 400, { ok: false, error: 'Invalid request body' });
    }

    if (!password) {
      return auth.json(res, 400, { ok: false, error: 'Password required' });
    }

    const hash = auth.sha256Hex(password);
    if (hash !== auth.ADMIN_PASSWORD_HASH) {
      return auth.json(res, 401, { ok: false, error: 'Incorrect password' });
    }

    const token = auth.createSessionToken();
    const [cookieName, cookieValue] = auth.buildSessionCookie(
      token,
      auth.SESSION_TTL_SECONDS
    );
    res.setHeader(cookieName, cookieValue);
    return auth.json(res, 200, { ok: true });
  });

  req.on('error', () => {
    if (!res.headersSent) {
      return auth.json(res, 400, { ok: false, error: 'Request error' });
    }
  });
};
