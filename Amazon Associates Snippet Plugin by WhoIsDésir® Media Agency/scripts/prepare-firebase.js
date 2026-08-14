'use strict';

/**
 * Stage api/ and private/ into functions/ before a Firebase deploy.
 *
 * firebase deploy only uploads the functions/ directory, but the API handlers
 * and their auth module live outside it (and private/ is gitignored, so it
 * must ship from this machine's disk, not a git checkout). This performs a
 * clean copy so the deployed bundle is self-contained:
 *
 *   api/     -> functions/api/
 *   private/ -> functions/private/
 *
 * Reference from firebase.json:
 *   "functions": { "predeploy": ["node scripts/prepare-firebase.js"] }
 */

const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const FUNCTIONS_DIR = path.join(ROOT, 'functions');

function stage(srcName, destName) {
  const src = path.join(ROOT, srcName);
  const dest = path.join(FUNCTIONS_DIR, destName);

  fs.rmSync(dest, { recursive: true, force: true });

  if (!fs.existsSync(src)) {
    console.warn('  ! skipped: ' + srcName + ' does not exist');
    return;
  }

  fs.cpSync(src, dest, { recursive: true });
  console.log('  staged ' + srcName + ' -> functions/' + destName);
}

console.log('Preparing functions/ for deploy...');
stage('api', 'api');
stage('private', 'private');
console.log('Done.');
