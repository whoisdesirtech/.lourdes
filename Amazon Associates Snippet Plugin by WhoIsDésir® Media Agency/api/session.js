'use strict';

const auth = require('../private/auth');

module.exports = function (req, res) {
  if (req.method !== 'GET') {
    return auth.json(res, 405, { ok: false, error: 'Method not allowed' });
  }
  const authenticated = auth.isAuthenticated(req);
  return auth.json(res, 200, { ok: true, authenticated });
};
