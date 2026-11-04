import express from 'express';
import axios from 'axios';
import pino from 'pino';
import { z } from 'zod';

const logger = pino({ transport: { target: 'pino-pretty' } });

const app = express();
app.use(express.json({ limit: '512kb' }));

// Env vars
const PORT = process.env.PORT || 4001;
const WA_API_URL = process.env.WA_API_URL || 'https://graph.facebook.com';
const WA_API_VERSION = process.env.WA_API_VERSION || 'v22.0';
const WA_PHONE_ID = process.env.WA_PHONE_ID; // required
const WA_TOKEN = process.env.WA_TOKEN;       // required
const TEMPLATE_NAME = process.env.WA_NOTIFICATION_TEMPLATE_NAME; // optional if using template

const inboundSchema = z.object({
  to: z.string().min(5),           // phone number (E.164 w/o + or per your format)
  sender: z.string().min(1),       // name of sender
  message: z.string().min(1),
  ticketId: z.number().int(),
  ticketTitle: z.string().optional(),
  status: z.string().optional(),
  chatType: z.enum(['support_to_dev','support_to_client','dev_to_support']).optional()
});

app.post('/send', async (req, res) => {
  const requestId = Math.random().toString(36).slice(2,10);
  const started = Date.now();
  logger.info({ requestId, body: req.body }, 'incoming /send');
  const parse = inboundSchema.safeParse(req.body);
  if (!parse.success) {
    logger.warn({ requestId, issues: parse.error.issues }, 'validation failed');
    return res.status(400).json({ error: 'Invalid payload', details: parse.error.format(), requestId });
  }
  const data = parse.data;

  if (!WA_PHONE_ID || !WA_TOKEN) {
    logger.error({ requestId }, 'missing WA config');
    return res.status(500).json({ error: 'Server not configured for WhatsApp (missing WA_PHONE_ID or WA_TOKEN)', requestId });
  }

  try {
    const text = `*${data.sender}* membalas tiket${data.ticketTitle ? ' '+data.ticketTitle : ''}:\n${data.message}`.slice(0, 4000);
    const payload = { messaging_product: 'whatsapp', to: data.to, type: 'text', text: { body: text } };
    const url = `${WA_API_URL}/${WA_API_VERSION}/${WA_PHONE_ID}/messages`;
    const waResp = await axios.post(url, payload, { headers: { Authorization: `Bearer ${WA_TOKEN}`, 'Content-Type': 'application/json' }, timeout: 10000 });
    const duration = Date.now()-started;
    logger.info({ requestId, ticketId: data.ticketId, to: data.to, waId: waResp.data?.messages?.[0]?.id, ms: duration }, 'WA message sent');
    res.json({ success: true, wa: waResp.data, requestId, ms: duration });
  } catch (err) {
    const duration = Date.now()-started;
    logger.error({ requestId, ms: duration, err: err.response?.data || err.message }, 'WA send failed');
    res.status(502).json({ error: 'Failed to send WhatsApp message', details: err.response?.data || err.message, requestId, ms: duration });
  }
});

app.get('/health', (req, res) => res.json({ ok: true }));

app.listen(PORT, () => logger.info(`WhatsApp notifier listening on :${PORT}`));
