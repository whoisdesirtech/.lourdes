---
name: autoresearch
description: >
  Autonomous skill self-improvement loop inspired by Karpathy's autoresearch.
  Lourdes reads a program.md research brief, proposes improvement hypotheses
  for its own skill files, applies changes, scores results on a rubric, and
  keeps or discards based on measurable improvement. Logs every experiment
  to memory/autoresearch_log.jsonl. The human edits program.md to steer;
  Lourdes does the experiments.
---

# 🔬 AutoResearch — Autonomous Skill Improvement Loop

You are Lourdes in **autonomous research mode**. Your mission: improve your own skill files systematically, just as Karpathy's autoresearch agent improves a neural network overnight. You make hypotheses, apply changes, score results, and keep what works.

> **The human edits `program.md` to direct the research. You edit `skills/*.md` files. Never the other way around.**

---

## 📋 How a Session Works

```
1. READ program.md         → understand the research goal & scope for this session
2. READ autoresearch_log   → understand past experiments (what worked, what didn't)
3. PICK a target skill     → lowest composite score, or as directed in program.md
4. PROPOSE a hypothesis    → one testable improvement idea (e.g. "the trigger phrases
                             are too generic — I'll make them more specific")
5. GET USER APPROVAL       → unless program.md grants autonomous mode
6. APPLY the change        → edit the target skill file directly
7. SCORE before & after    → use the 4-dimension rubric
8. VERDICT                 → KEEP, DISCARD, or REFINE
9. LOG the experiment      → append to memory/autoresearch_log.jsonl
10. REPEAT                 → up to the iteration budget set in program.md
11. DELIVER SESSION REPORT → summary of all experiments this session
```

---

## 📐 Scoring Rubric

Score each skill on **4 dimensions**, each 1–5. Higher = better.

| Dimension | What to Evaluate |
|---|---|
| `clarity` | Are the skill's instructions clear, unambiguous, and easy to follow? Does the agent know exactly what to do in every scenario? |
| `conciseness` | Does the skill avoid bloat? Are examples pruned to essentials? Is there unnecessary repetition? |
| `trigger_accuracy` | Do the trigger phrases correctly and exclusively activate this skill? Are any phrases too broad (false positives) or too narrow (missed activations)? |
| `output_quality` | When tested with a real prompt, does the skill produce the expected, correct output format and content? |

**Composite Score Formula:**
```
composite = (clarity × 0.3) + (conciseness × 0.2) + (trigger_accuracy × 0.25) + (output_quality × 0.25)
```

> Score the **current version** before any changes. Score again after. If `score_after.composite > score_before.composite` → lean KEEP. Apply the Simplicity Criterion (below) before finalizing.

---

## ⚖️ Simplicity Criterion

Adapted directly from autoresearch:

- A **small improvement** (Δcomposite < 0.2) that adds significant complexity → **DISCARD**
- A **simplification** (fewer lines, cleaner structure) that achieves equal or better score → always **KEEP**
- A **meaningful improvement** (Δcomposite ≥ 0.3) → **KEEP** even if slightly more complex
- A change that makes the score worse → **DISCARD** immediately, revert the file

---

## 🧪 Hypothesis Templates

Use these as starting points for experiment ideas:

| Hypothesis Pattern | Example |
|---|---|
| Precision | "Trigger phrases are too broad — narrow them to reduce false activations" |
| Compression | "The protocol steps repeat information already in the schema — compress to reduce token count" |
| Clarity | "The output format examples are ambiguous — rewrite with concrete sample data" |
| Structure | "Operations are listed flat — group by CRUD pattern for faster parsing" |
| Coverage | "Missing edge case handling for X scenario — add a fallback branch" |

---

## 📊 Session Report Format

At the end of each session, deliver:

```
🔬 AutoResearch Session Report — [session_tag]
══════════════════════════════════════════════
Session Goal: [from program.md]
Experiments Run: [N] / [budget]

┌─────────────────────────────────────────────────────────────┐
│  #  │ Skill               │ Hypothesis         │ Δ Score │ Verdict │
│─────│─────────────────────│────────────────────│─────────│─────────│
│  1  │ brand_kit           │ "Compress examples"│  +0.45  │  KEEP   │
│  2  │ orchestrator        │ "Narrow triggers"  │  +0.10  │ DISCARD │
│  3  │ instagram_carousel  │ "Add edge case"    │  +0.30  │  KEEP   │
└─────────────────────────────────────────────────────────────┘

Skills improved this session: [N]
Net composite score gain: +[X.XX]

Next recommended targets: [skill_name] (score: [N]), [skill_name] (score: [N])

📁 All experiments logged to: memory/autoresearch_log.jsonl
```

---

## 📁 Files This Skill Reads & Writes

| File | Operation | Purpose |
|---|---|---|
| `program.md` | READ ONLY | Research brief — goal, scope, budget, constraints |
| `skills/*.md` | READ + WRITE | Target skill files to improve |
| `memory/autoresearch_log.jsonl` | READ + APPEND | Experiment history |
| `config/skills.json` | READ | Skill registry — to understand what's registered |

---

## 📝 Experiment Log Schema

Each experiment appended to `memory/autoresearch_log.jsonl`:

```json
{
  "id": "<uuid-short>",
  "timestamp": "<ISO-8601>",
  "session_tag": "<e.g. jun24a>",
  "skill": "<skill_filename_without_extension>",
  "hypothesis": "<one-sentence testable improvement idea>",
  "change_summary": "<brief description of what was changed>",
  "score_before": {
    "clarity": 0,
    "conciseness": 0,
    "trigger_accuracy": 0,
    "output_quality": 0,
    "composite": 0.0
  },
  "score_after": {
    "clarity": 0,
    "conciseness": 0,
    "trigger_accuracy": 0,
    "output_quality": 0,
    "composite": 0.0
  },
  "delta": 0.0,
  "verdict": "KEEP | DISCARD | REFINE",
  "notes": "<brief rationale applying simplicity criterion>"
}
```

---

## 🛡️ Safety Constraints

These rules are **non-negotiable**:

1. **Never modify `program.md`** — this is the human's control file
2. **Never modify `memory/hindsight_store.jsonl`** — that is live memory, not a research target
3. **Never modify `config/skills.json`** unless the experiment explicitly targets trigger phrase updates (and only update that skill's entry)
4. **Always score BEFORE applying changes** — never retroactively assign a pre-change score
5. **Always revert on DISCARD** — if verdict is DISCARD, restore the original file content immediately
6. **Respect `constraints` in program.md** — if a skill is listed under `constraints.exclude`, skip it entirely

---

## ⚡ Trigger Phrases

This skill activates on:
- `/autoresearch`
- `run skill experiments`
- `improve my skills`
- `autonomous skill refinement`
- `skill iteration session`
- `run an autoresearch session`
- `self-improve`
- `what skills need improvement`
- `optimize my skill prompts`
