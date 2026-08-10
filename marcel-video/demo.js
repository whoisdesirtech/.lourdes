const { chromium } = require('playwright');
const fs = require('fs');

const EMAIL = 'demo@marcel.app';
const PASS = 'DemoDemo2026!';
const BASE = 'https://marcel-market-intelligence.web.app';

const CURSOR_SVG = `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
  <path d="M5 2l18 11.5-7.5 1.3L18.5 22l-3.2 1.4-3.6-7.2-4.6 4.3L5 15.8V2z" fill="#0d0d14" stroke="#00d4aa" stroke-width="1.2"/>
  <path d="M8 5.8l12.3 7.9-5.3.9 1.3 2.6 3.9 7.2-1.4.6-3.1-6.2-3 2.8V5.8z" fill="#ffffff"/>
</svg>`;

const sleep = ms => new Promise(r => setTimeout(r, ms));

async function injectCursor(page) {
  const dataUri = 'data:image/svg+xml;utf8,' + encodeURIComponent(CURSOR_SVG);
  await page.evaluate(src => {
    const style = document.createElement('style');
    style.textContent = `
      #pw-cursor { position: fixed; left: 0; top: 0; width: 28px; height: 28px;
        pointer-events: none; z-index: 2147483647; opacity: 0; will-change: transform;
        background: url('${src}') no-repeat center/contain;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.5)); }
      .pw-ripple { position: fixed; width: 84px; height: 84px; margin: -42px 0 0 -42px;
        border: 3px solid #00d4aa; border-radius: 50%; pointer-events: none;
        z-index: 2147483646; animation: pw-ripple 0.55s ease-out forwards; }
      .pw-flash { position: fixed; width: 14px; height: 14px; margin: -7px 0 0 -7px;
        background: #00d4aa; border-radius: 50%; pointer-events: none;
        z-index: 2147483646; animation: pw-flash 0.3s ease-out forwards; }
      @keyframes pw-ripple { 0% { transform: scale(0.25); opacity: 0.95; }
        100% { transform: scale(1.5); opacity: 0; } }
      @keyframes pw-flash { 0% { transform: scale(0.4); opacity: 1; }
        100% { transform: scale(1.3); opacity: 0; } }
    `;
    document.head.appendChild(style);
    const c = document.createElement('div');
    c.id = 'pw-cursor';
    document.body.appendChild(c);
  }, dataUri);
}

async function setCursor(page, x, y, visible = true) {
  await page.evaluate(([x, y, visible]) => {
    const c = document.getElementById('pw-cursor');
    if (!c) return;
    c.style.transform = `translate(${x}px, ${y}px)`;
    c.style.opacity = visible ? '1' : '0';
  }, [x, y, visible]).catch(() => {});
}

async function ripple(page, x, y) {
  await page.evaluate(([x, y]) => {
    const r = document.createElement('div');
    r.className = 'pw-ripple';
    r.style.left = x + 'px';
    r.style.top = y + 'px';
    document.body.appendChild(r);
    setTimeout(() => r.remove(), 600);
    const f = document.createElement('div');
    f.className = 'pw-flash';
    f.style.left = x + 'px';
    f.style.top = y + 'px';
    document.body.appendChild(f);
    setTimeout(() => f.remove(), 320);
  }, [x, y]).catch(() => {});
}

async function move(page, x, y, { dur = 600, steps = 24, visible = true } = {}) {
  const start = await page.evaluate(() => {
    const c = document.getElementById('pw-cursor');
    if (!c) return { x: 1400, y: 900 };
    const t = c.style.transform.match(/translate\(([-\d.]+)px, ([-\d.]+)px\)/);
    return t ? { x: +t[1], y: +t[2] } : { x: 1400, y: 900 };
  });
  for (let i = 1; i <= steps; i++) {
    const p = i / steps;
    const ease = p < 0.5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2;
    const px = start.x + (x - start.x) * ease;
    const py = start.y + (y - start.y) * ease;
    await Promise.all([page.mouse.move(px, py), setCursor(page, px, py, visible)]);
    await sleep(dur / steps);
  }
}

async function clickEl(page, locator, opts = {}) {
  const box = await locator.boundingBox();
  if (!box) throw new Error('no bounding box for ' + locator);
  const cx = box.x + box.width / 2;
  const cy = box.y + box.height / 2;
  await move(page, cx, cy, opts);
  await ripple(page, cx, cy);
  await locator.click();
  await sleep(400);
}

async function typeSlow(page, locator, text, { cps = 14 } = {}) {
  await clickEl(page, locator, { dur: 450 });
  for (const ch of text) {
    await locator.press(ch);
    await sleep(1000 / cps);
  }
}

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    recordVideo: { dir: 'video/', size: { width: 1920, height: 1080 } },
  });
  const page = await context.newPage();

  await page.goto(BASE, { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2500);
  await injectCursor(page);
  await setCursor(page, 1750, 950);
  await sleep(600);

  // 1. Hero: click "See how it works" -> scrolls to features
  const seeHow = page.getByRole('link', { name: /See how it works/i }).first();
  await clickEl(page, seeHow, { dur: 800 });
  await sleep(1500);

  // 2. Scroll features slowly
  for (let i = 0; i < 6; i++) { await page.mouse.wheel(0, 500); await sleep(450); }
  await sleep(600);

  // 3. Scroll back up
  for (let i = 0; i < 6; i++) { await page.mouse.wheel(0, -500); await sleep(350); }
  await sleep(800);

  // 4. Click "Get Started — Free" -> login
  const getStarted = page.getByRole('link', { name: /Get Started/i }).first();
  await clickEl(page, getStarted, { dur: 800 });
  await page.waitForURL(/\/login/, { timeout: 20000 });
  await sleep(1200);

  // 5. Sign in
  await typeSlow(page, page.locator('input[type="email"]'), EMAIL);
  await typeSlow(page, page.locator('input[type="password"]'), PASS);
  const signIn = page.getByRole('button', { name: 'Sign In' }).first();
  await clickEl(page, signIn, { dur: 600 });
  await page.waitForURL(/\/dashboard/, { timeout: 30000 });
  await page.waitForTimeout(3500);

  // 6. Dashboard: open TSLA details
  const viewDetails = page.getByText('View Details', { exact: false }).first();
  await clickEl(page, viewDetails, { dur: 800 });
  await page.waitForURL(/\/research/, { timeout: 30000 });
  await page.waitForTimeout(3500);

  // 7. Research page: focus search, type a ticker, then clear (no live analysis run)
  const search = page.locator('input[placeholder="Search"], input[type="search"], input').first();
  await clickEl(page, search, { dur: 600 });
  await typeSlow(page, search, 'NVDA', { cps: 12 });
  await sleep(900);
  await page.keyboard.press('Control+A');
  await page.keyboard.press('Backspace');
  await sleep(600);

  // 8. Scroll the research page
  for (let i = 0; i < 4; i++) { await page.mouse.wheel(0, 450); await sleep(400); }
  for (let i = 0; i < 4; i++) { await page.mouse.wheel(0, -450); await sleep(300); }

  await sleep(1500);
  await page.mouse.move(50, 1000);
  await setCursor(page, 50, 1000, false);
  await sleep(800);

  const vpath = await page.video().path();
  console.log('VIDEO_PATH:', vpath);
  await browser.close();
  console.log('DONE');
})().catch(e => { console.error('FATAL', e.message); process.exit(1); });
