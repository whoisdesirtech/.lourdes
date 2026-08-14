'use strict';

const fs = require('fs');
const path = require('path');
const auth = require('../private/auth');

const PDF_PATH = path.join(
  __dirname,
  '..',
  'private',
  'audit',
  'amazon-associates-plugin-audit-2026.pdf'
);

module.exports = function (req, res) {
  if (req.method !== 'GET') {
    return auth.json(res, 405, { ok: false, error: 'Method not allowed' });
  }

  if (!auth.isAuthenticated(req)) {
    res.writeHead(401, {
      'Content-Type': 'application/json; charset=utf-8',
      'Cache-Control': 'no-store',
    });
    return res.end(JSON.stringify({ ok: false, error: 'Unauthorized' }));
  }

  if (!fs.existsSync(PDF_PATH)) {
    res.writeHead(404, {
      'Content-Type': 'application/json; charset=utf-8',
      'Cache-Control': 'no-store',
    });
    return res.end(JSON.stringify({ ok: false, error: 'Not found' }));
  }

  const stat = fs.statSync(PDF_PATH);
  res.writeHead(200, {
    'Content-Type': 'application/pdf',
    'Content-Length': stat.size,
    'Content-Disposition': 'attachment; filename="amazon-associates-plugin-audit-2026.pdf"',
    'Cache-Control': 'private, no-store',
  });
  fs.createReadStream(PDF_PATH).pipe(res);
};
