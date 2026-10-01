require('dotenv').config();

const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');

const PORT = process.env.PORT || 3020;
const TOKEN = process.env.WHATSAPP_WEB_TOKEN || '';

const app = express();
app.use(express.json({ limit: '1mb' }));

let ready = false;

const client = new Client({
  authStrategy: new LocalAuth({ clientId: 'parent-alerts' }),
  puppeteer: {
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  }
});

client.on('qr', (qr) => {
  console.log('Scan the QR code below with WhatsApp:');
  qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
  ready = true;
  console.log('WhatsApp client ready.');
});

client.on('disconnected', (reason) => {
  ready = false;
  console.log('WhatsApp client disconnected:', reason);
});

client.initialize();

function normalizeNumber(raw) {
  if (!raw) return '';
  let digits = String(raw).replace(/\D+/g, '');
  if (!digits) return '';
  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }
  return digits;
}

app.get('/health', (req, res) => {
  res.json({ ok: true, ready });
});

app.post('/send', async (req, res) => {
  const incomingToken = req.header('X-WhatsApp-Token') || req.body.token || '';
  if (TOKEN && TOKEN !== incomingToken) {
    return res.status(401).json({ ok: false, error: 'unauthorized' });
  }

  if (!ready) {
    return res.status(503).json({ ok: false, error: 'client_not_ready' });
  }

  const to = normalizeNumber(req.body.to);
  const message = String(req.body.message || '').trim();
  if (!to || !message) {
    return res.status(400).json({ ok: false, error: 'invalid_payload' });
  }

  try {
    await client.sendMessage(`${to}@c.us`, message);
    return res.json({ ok: true });
  } catch (err) {
    return res.status(500).json({ ok: false, error: err.message || 'send_failed' });
  }
});

app.listen(PORT, () => {
  console.log(`WhatsApp gateway listening on ${PORT}`);
});
