const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  const errors = [];
  page.on('console', m => { if (m.type() === 'error') errors.push(m.text().slice(0, 200)); });

  await page.goto('https://marcel-market-intelligence.web.app/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(3000);

  console.log('TITLE:', await page.title());
  console.log('URL:', page.url());

  const info = await page.evaluate(() => {
    const q = s => [...document.querySelectorAll(s)];
    return {
      text: document.body.innerText.slice(0, 1200),
      buttons: q('button').map(b => b.innerText.trim().slice(0, 40)).slice(0, 20),
      inputs: q('input, textarea, select').map(i => (i.type || i.tagName) + ' placeholder=' + (i.placeholder || '')).slice(0, 10),
      links: q('a[href]').map(a => a.innerText.trim().slice(0, 30) + ' -> ' + a.href).slice(0, 20),
      h1: q('h1,h2,h3').map(h => h.innerText.trim().slice(0, 60)).slice(0, 15),
    };
  });
  console.log(JSON.stringify(info, null, 2));
  console.log('CONSOLE ERRORS:', errors.slice(0, 5));

  await browser.close();
})().catch(e => { console.error('FAIL', e.message); process.exit(1); });
