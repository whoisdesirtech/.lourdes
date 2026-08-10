const fs = require('fs');
const path = require('path');

const envPath = path.join(__dirname, '..', '.env');
const env = fs.readFileSync(envPath, 'utf8');
const key = env.match(/OPENAI_API_KEY=(.+)/)[1].trim();
const OpenAI = require('openai');
const o = new OpenAI({ apiKey: key });

const TEXT = `Every day, retail investors make decisions on noise and headlines. Marcel changes that. Marcel deploys specialized AI analysts, each one examining financial health, fraud red flags, SEC filings, and insider activity. Then it synthesizes everything into one transparent rating: Buy, Hold, or Sell, with a confidence score and a plain English explanation of exactly what is driving it. No black boxes. No hype. Every conclusion, backed by evidence you can see. Marcel doesn't pick winners. Marcel finds facts. Smart investing, explained.`;

(async () => {
  const mp3 = await o.audio.speech.create({
    model: 'gpt-4o-mini-tts',
    voice: 'nova',
    input: TEXT,
    instructions: 'Confident, warm, professional fintech promo narration. Steady pace, natural pauses between sentences, emphasis on key phrases like "evidence" and "facts". No robotic delivery.',
  });
  const buf = Buffer.from(await mp3.arrayBuffer());
  fs.writeFileSync(path.join(__dirname, 'audio', 'narration-openai.mp3'), buf);
  console.log('WROTE audio/narration-openai.mp3', buf.length, 'bytes');
})().catch(e => {
  console.error('FAILED:', e.status, e.error?.message || e.message);
  process.exit(1);
});
