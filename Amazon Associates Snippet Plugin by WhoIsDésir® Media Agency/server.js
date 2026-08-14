'use strict';

/**
 * Local dev server for the admin-gated audit area.
 *
 * Zero-dependency Node server that:
 *   - serves the static marketing pages from ./public
 *   - mounts the four API handlers (login/logout/session/audit) at /api/*
 *
 * Usage:  node server.js   (PORT env overrides, default 8080)
 */

const http = require('http');
const fs = require('fs');
const path = require('path');

const ROOT = __dirname;
const PUBLIC_DIR = path.join(ROOT, 'public');
const PORT = parseInt(process.env.PORT || '8080', 10);

const login = require('./api/login');
const logout = require('./api/logout');
const session = require('./api/session');
const audit = require('./api/audit');

const MIME_TYPES = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.gif': 'image/gif',
  '.webp': 'image/webp',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.pdf': 'application/pdf',
  '.txt': 'text/plain; charset=utf-8',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
};

const API_ROUTES = {
  '/api/login': login,
  '/api/logout': logout,
  '/api/session': session,
  '/api/audit': audit,
};

function sendJson(res, status, body) {
  res.writeHead(status, {
    'Content-Type': 'application/json; charset=utf-8',
    'Cache-Control': 'no-store',
  });
  res.end(JSON.stringify(body));
}

function serveStatic(req, res, pathname) {
  let rel = pathname;
  if ('/' === rel) {
    // The gated audit page is the intended entry point.
    res.writeHead(302, { Location: '/audit.html' });
    return res.end();
  }

  const file = path.normalize(path.join(PUBLIC_DIR, rel));
  if (file !== PUBLIC_DIR && !file.startsWith(PUBLIC_DIR + path.sep)) {
    return sendJson(res, 403, { ok: false, error: 'Forbidden' });
  }

  fs.stat(file, (err, stat) => {
    if (err || !stat.isFile()) {
      return sendJson(res, 404, { ok: false, error: 'Not found' });
    }
    res.writeHead(200, {
      'Content-Type': MIME_TYPES[path.extname(file).toLowerCase()] || 'application/octet-stream',
      'Content-Length': stat.size,
      'Cache-Control': 'no-cache',
    });
    fs.createReadStream(file).pipe(res);
  });
}

const server = http.createServer((req, res) => {
  try {
    const rawPath = (req.url || '/').split('?')[0];
    const pathname = decodeURIComponent(rawPath);
    const handler = API_ROUTES[pathname];

    if (handler) {
      return handler(req, res);
    }
    return serveStatic(req, res, pathname);
  } catch (err) {
    return sendJson(res, 500, { ok: false, error: 'Internal error' });
  }
});

server.listen(PORT, () => {
  console.log('Audit landing server listening on http://localhost:' + PORT);
  console.log('  - Audit page:    http://localhost:' + PORT + '/audit.html');
});