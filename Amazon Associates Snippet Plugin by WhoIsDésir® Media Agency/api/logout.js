'use strict';

const auth = require('../private/auth');

module.exports = function (req, res) {
  if (req.method !== 'POST') {
    return auth.json(res, 405, { ok: false, error: 'Method not allowed' });
  }
  auth.clearSessionCookie(res);
  return auth.json(res, 200, { ok: true });
};
