# Lourdes AutoResearch — Program Brief

> **This file is yours to edit.** Lourdes reads it at the start of every `/autoresearch` session to understand what to work on. Think of it as your research org's standing orders. Change the goal, change the scope, change the budget — Lourdes will adapt.
>
> Inspired by [@karpathy/autoresearch](https://github.com/karpathy/autoresearch) — same idea, applied to prompt engineering instead of neural networks.

---

## 🎯 Current Research Goal

```
Improve trigger phrase precision and output format consistency
across all active skills.
```

*Edit this line to redirect the research focus.*

---

## 📋 Session Configuration

```yaml
session_budget:      3          # Max experiments per /autoresearch invocation
autonomous_mode:     false      # If true, Lourdes applies changes without per-experiment approval
score_threshold:     3.5        # Skills below this composite score are prioritized
delta_keep:          0.25       # Minimum composite score improvement to auto-KEEP
```

---

## 🔬 Skills In Scope

All registered skills are fair game unless listed under `constraints.exclude` below.

```yaml
in_scope: all
```

*To target specific skills only, replace `all` with a list:*
```yaml
# in_scope:
#   - brand_kit
#   - orchestrator
#   - instagram_carousel
```

---

## 🚫 Constraints

```yaml
constraints:
  exclude:
    - hindsight_memory      # Live memory system — do not experiment on
    - autoresearch          # Do not self-modify the research loop itself
  read_only:
    - program.md            # This file — human control only
    - memory/hindsight_store.jsonl
```

---

## 📌 Research Notes

*Use this section to leave notes for Lourdes across sessions — observations, patterns noticed, hypotheses to test next.*

```
[jun24] — Initial session. No prior experiments. Establish baselines for all skills first.
```

---

## 📊 How to Read Experiment Results

After each session, check `memory/autoresearch_log.jsonl` for the full log. Lourdes will also deliver a session report in-chat.

The composite score formula:
```
composite = (clarity × 0.3) + (conciseness × 0.2) + (trigger_accuracy × 0.25) + (output_quality × 0.25)
```
**Max possible score: 5.0 | Threshold for attention: below 3.5**
