# Lourdes AI OS — 5-Minute Demo Script

> **Audience**: Capstone Presentation Day (peers, instructors, judges)
> **Tone**: Conversational, confident, not reading — speak these bullets naturally
> **Total time**: ~5 minutes

---

## Part 1 — Hook (30 seconds)

**Problem first. No intro.**

"Every AI agent I see is a single-purpose chatbot. You talk to it, it answers, you leave, and it forgets everything. If you want it to do something different, you build a new one from scratch.

That works for a demo. It does not work for a business.

I run a consulting practice — tax prep, brand design, web development, luxury concierge. I need an AI that knows my clients across every service line, improves its own skills without me rewriting prompts every week, and connects to the tools I actually use — Google Drive, Gmail, my calendar.

So I built one. It's called Lourdes. And it is not a chatbot. It is an operating system for an AI-powered consulting business."

---

## Part 2 — Demo Path (3 minutes)

### Step 1 — Show the OS (30 seconds)

**What to load**: VS Code or your file tree, open `.lourdes/`

**What to say**:

"Lourdes lives in a single folder on my machine. This is the entire OS.

`config/skills.json` — the kernel registry. Each skill is a separate module. The orchestrator reads this file and routes every request to the right skill agent. If I want to add a new capability, I drop a new `.md` file into `skills/` and register it here. No code changes. No redeploy.

`skills/` — twelve skill modules right now. Tax intake. Brand kit generation. Premium frontend design. Instagram carousel creation. Google Workspace automation.

`memory/` — long-term storage. Lourdes remembers facts, client preferences, decisions across sessions. Not chat history — structured, searchable durable memory.

`.agent/` — my business pipeline. A web form submission flows through three agents: intake parses it, scope defines the project, proposal drafts the email in my voice. All automated, all inside the OS."

**Click here**: Open `config/skills.json` briefly, then close it.

---

### Step 2 — Demo a live skill (90 seconds)

**Pick ONE of these tracks based on what will display best on your projector:**

#### Track A — Visual: Brand Kit Generator (recommended for impact)

**What to load**: Terminal + browser side by side

**What to type**:
```
/brand-kit create for Stripe --compare
```

**What to say**:

"I'll ask Lourdes to generate a brand kit for Stripe. The brand-kit-generator skill runs the BRANDEX framework — it researches the company, extracts design DNA, and compares it against a competitor.

Watch. It returns structured markdown, YAML for developers, and a standalone HTML page I can hand to a client. Color palette. Typography system. Logo usage. Voice and tone. Competitive positioning.

[point to the output]

This HTML file is a deliverable. A client-ready brand guide generated in under a minute inside my AI OS."

**Click here**: Scroll through the output HTML file, point out the color system and typography section.

#### Track B — Practical: Client Intake Agent (best for business value)

**What to load**: Terminal

**What to type**:
```
/intake start for Jane Smith, sole proprietor, 1099 + home office deduction
```

**What to say**:

"I'll start a tax intake for a new client. The client_intake_agent skill immediately knows what documents a 1040 sole proprietor needs — W-2, 1099-NEC, 1098 for mortgage interest, home office worksheet, prior-year return.

It generates a personalized checklist, resolves filing status, and gives me a pre-appointment summary in structured form. No back-and-forth emails. No forgotten documents.

[point to the checklist]

For my tax practice, this saves fifteen minutes per intake. Multiplied by fifty clients a season, that's twelve hours — reclaimed by building an OS skill once."

---

### Step 3 — The self-improvement loop (30 seconds)

**What to load**: Terminal

**What to type**:
```
/autoresearch
```

**What to say**:

"This is the part that makes it an OS, not a chatbot.

Autoresearch is a Karpathy-inspired autonomous improvement loop. Lourdes reads `program.md` — the research brief I wrote — then proposes hypotheses to improve its own skill files. It edits the file, runs an eval, scores the result on clarity, conciseness, trigger accuracy, and output quality. It keeps changes that score higher and discards the rest.

Every experiment is logged to `memory/autoresearch_log.jsonl`. I wake up to a report of what improved overnight. The OS debugs itself."

---

### Step 4 — Memory layer (30 seconds)

**What to load**: Terminal after a short break

**What to say**:

"Let me show you one more thing. Earlier in this conversation — before the demo started — I told Lourdes that Jane prefers email over phone, and that her deadline is April 1st."

**What to type**:
```
/recall what do you know about Jane Smith
```

**What to say**:

"It remembers. Not from chat history — from a dedicated memory layer using the Hindsight framework. Facts are extracted, reflected on, consolidated. Contradictions are flagged. Old memories decay over time.

This is what makes Lourdes a partner, not a tool. It learns who my clients are."

---

## Part 3 — AI OS Callout (30 seconds)

"The key moment in that demo was the self-improvement loop — autoresearch. That is the skill I want to call out.

I built it because prompts decay. A skill that works perfectly today will drift as language models update, as my business changes, as clients get more sophisticated. I cannot manually iterate twelve skill files every month.

So I built an agent that treats its own skill files as code — proposes hypotheses, tests them, and keeps the winners. The rubric is specific to my use case: clarity so the orchestrator routes correctly, conciseness to fit in the context window, trigger accuracy so it fires at the right moment, output quality so clients get premium work.

It is not generic prompt engineering advice. It is an automated quality assurance pipeline for my entire operating system. That is what makes it an OS, not a script."

---

## Part 4 — The Ask (30 seconds)

"Here is what I am still figuring out, and I would love your perspective.

Lourdes has twelve skills now. As I add more — email marketing, project estimation, client onboarding — the orchestrator's routing decisions get harder. The wrong skill firing wastes time and confuses the user.

**My question is: should the orchestrator itself be an AI-routed system that learns from past routing successes and failures, or should it stay a rule-based system that I explicitly configure?**

The AI route is more adaptive. The rule route is more predictable. I want to know which trade-off you would make as the skill count grows to twenty, fifty, a hundred.

Thank you."
