// DOM + real HTTP regression test; does not claim to render a real browser.
const { JSDOM } = require(process.env.WH_JSDOM_MODULE || 'jsdom');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const base = process.env.WH_TEST_URL;
if (!base || !/^http:\/\/127\.0\.0\.1:\d+$/.test(base)) throw new Error('Local test URL required');
let cookie = '';
async function request(url, options = {}) {
  const headers = new Headers(options.headers || {});
  if (cookie) headers.set('Cookie', cookie);
  const response = await fetch(new URL(url, base), { ...options, headers, redirect: 'manual' });
  for (const value of response.headers.getSetCookie()) {
    if (value.startsWith('WHSESSID=')) cookie = value.split(';')[0];
  }
  return response;
}
(async () => {
  const login = await request('/login');
  const text = await login.text();
  const token = text.match(/name="csrf-token" content="([a-f0-9]+)"/)[1];
  const authenticated = await request('/login', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ username: 'admin_fixture', password: 'Fixture-password-2026', csrf_token: token }) });
  assert.equal(authenticated.status, 303);
  const page = await request('/transaksi?id=REQ-UI');
  const dom = new JSDOM(await page.text(), { url: base + '/transaksi?id=REQ-UI', runScripts: 'outside-only', pretendToBeVisual: true });
  const w = dom.window;
  try {
    w.fetch = (url, opts) => request(new URL(url, w.location.href), opts);
    w.eval(fs.readFileSync(path.join(__dirname, '../assets/vendor-ui/jquery/jquery-3.6.0.min.js'), 'utf8'));
    for (const script of w.document.querySelectorAll('script:not([src])')) w.eval(script.textContent);
    w.eval(fs.readFileSync(path.join(__dirname, '../assets/js/scripts.js'), 'utf8'));
    await new Promise(resolve => setTimeout(resolve, 30));
    const count = () => w.document.querySelectorAll('#dynamic-item-container .item-row').length;
    assert.equal(count(), 0, 'No initial item card');
    await w.processAutoScan('101019999');
    assert.equal(count(), 0, 'Unregistered barcode creates no card');
    await w.processAutoScan('101010009');
    assert.equal(count(), 1, 'Valid database scan creates a card');
    await w.processAutoScan('101010009');
    assert.equal(count(), 1, 'Repeated scan creates no duplicate');
    w.removeItemCard(1);
    assert.equal(count(), 0, 'Removing last item removes the card');
    await Promise.all([w.processAutoScan('101010009'), w.processAutoScan('101010010')]);
    assert.equal(count(), 2, 'Rapid scans are queued correctly');
    assert.equal(w.document.querySelector('#btnProses').disabled, true, 'Validation required before submit');
    w.document.querySelector('#id_sales').value = '1234';
    w.document.querySelector('#department').value = 'Mens Casual';
    await w.validateAllItems();
    assert.equal(w.document.querySelector('#btnProses').disabled, false, 'Full validation enables submit');
    const pairs = w.jQuery('#formTransaksi').serializeArray();
    assert.equal(pairs.filter(p => p.name === 'csrf_token').length, 1, 'Single CSRF field is present in form');
    const body = new URLSearchParams();
    for (const pair of pairs) body.append(pair.name, pair.value);
    const saved = await request('/controllers/proses_transaksi', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' }, body });
    assert.equal(saved.status, 200, await saved.text());
    console.log('PASS DOM + HTTP: empty card, invalid/valid/duplicate scan, removal, rapid scans, validation, real form submission');
  } finally { w.close(); }
})().catch(error => { console.error(error); process.exit(1); });
