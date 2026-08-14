'use strict';

/**
 * Firebase Cloud Functions v2 entry point.
 *
 * Exposes a single onRequest function named "api" that dispatches /api/*
 * paths to the shared handlers kept in ./api (staged at deploy time by
 * scripts/prepare-firebase.js so private/ can ship from disk).
 *
 * Hosting rewrite in firebase.json:
 *   { "source": "/api/**", "function": "api" }
 */

const { onRequest } = require('firebase-functions/v2/https');

const login = require('./api/login');
const logout = require('./api/logout');
const session = require('./api/session');
const audit = require('./api/audit');

const ROUTES = {
  '/api/login': login,
  '/api/logout': logout,
  '/api/session': session,
  '/api/audit': audit,
};

exports.api = onRequest((req, res) => {
  const pathname = (req.path || '/').split('?')[0];
  const handler = ROUTES[pathname];

  if (handler) {
    return handler(req, res);
  }

  res.writeHead(404, {
    'Content-Type': 'application/json; charset=utf-8',
    'Cache-Control': 'no-store',
  });
  return res.end(JSON.stringify({ ok: false, error: 'Not found' }));
});