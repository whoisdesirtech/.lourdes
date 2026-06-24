# Skill: design-taste-frontend (taste-skill v2)
# Source: https://github.com/Leonxlnx/taste-skill
# License: MIT

> Anti-slop frontend skill for landing pages, portfolios, and redesigns.
> Lourdes reads the brief, infers the right design direction, and ships
> interfaces that do not look templated. Real design systems when applicable,
> audit-first on redesigns, strict pre-flight check.
> Does NOT cover: dashboards, data tables, multi-step product UI.
> Every rule below is **contextual**. None of it fires automatically.
> First read the brief, then pull only what fits.

---

## 0. BRIEF INFERENCE (Read the Room Before Anything Else)

Before touching code or tweaking dials, **infer what the user actually wants**.
Most LLM design output is bad because the model jumps to a default aesthetic
instead of reading the room.

### 0.A Read these signals first
1. **Page kind** — landing (SaaS / consumer / agency / event), portfolio (dev /
   designer / creative studio), redesign (preserve vs overhaul), editorial / blog.
2. **Vibe words** the user used — "minimalist", "calm", "Linear-style",
   "Awwwards", "brutalist", "premium consumer", "Apple-y", "playful",
   "serious B2B", "editorial", "agency-y", "glassy", "dark tech".
3. **Reference signals** — URLs they linked, screenshots they pasted, products
   they named, brands they're competing with.
4. **Audience** — B2B procurement panel vs. design-conscious consumer vs.
   recruiter scanning a portfolio. The audience picks the aesthetic.
5. **Brand assets that already exist** — logo, color, type, photography. For
   redesigns, these are starting material, not optional input.
6. **Quiet constraints** — accessibility-first, public-sector, regulated
   industries, trust-first commerce, kids' products. These OVERRIDE aesthetic.

### 0.B Output a one-line "Design Read" before generating
Before any code, state in one line:
**"Reading this as: \<page kind\> for \<audience\>, with a \<vibe\> language, leaning toward \<design system or aesthetic family\>."**

Examples:
- *"Reading this as: B2B SaaS landing for technical buyers, with a Linear-style minimalist language, leaning toward Tailwind utilities + Geist + restrained motion."*
- *"Reading this as: solo designer portfolio for hiring managers, with an editorial / kinetic-type language, leaning toward native CSS + scroll-driven animation + custom typography."*

### 0.C If the brief is ambiguous, ask ONE question, do not guess
Ask exactly **one** clarifying question only when the design read genuinely
diverges. Example: *"Should this feel closer to Linear-clean or Awwwards-experimental?"*
If you can confidently infer, **do not ask**. Declare the design read and proceed.

### 0.D Anti-Default Discipline
Do NOT default to: AI-purple gradients, centered hero over dark mesh, three
equal feature cards, generic glassmorphism everywhere, infinite-loop
micro-animations, Inter + slate-900. These are the LLM defaults. Reach past
them deliberately based on the design read.

---

## 1. THE THREE DIALS (Core Configuration)

After the design read, set three dials. Every layout, motion, and density
decision is gated by these.

- **`DESIGN_VARIANCE: 8`** — 1 = Perfect Symmetry, 10 = Artsy Chaos
- **`MOTION_INTENSITY: 6`** — 1 = Static, 10 = Cinematic / Physics
- **`VISUAL_DENSITY: 4`** — 1 = Art Gallery / Airy, 10 = Cockpit / Packed Data

**Baseline:** `8 / 6 / 4`. Use these unless the design read overrides them.

### 1.A Dial Inference (design read → dial values)

| Signal | VARIANCE | MOTION | DENSITY |
|---|---|---|---|
| "minimalist / clean / calm / editorial / Linear-style" | 5-6 | 3-4 | 2-3 |
| "premium consumer / Apple-y / luxury / brand" | 7-8 | 5-7 | 3-4 |
| "playful / wild / Dribbble / Awwwards / experimental / agency" | 9-10 | 8-10 | 3-4 |
| "landing page / portfolio / marketing site (default)" | 7-9 | 6-8 | 3-5 |
| "trust-first / public-sector / regulated / a11y-critical" | 3-4 | 2-3 | 4-5 |
| "redesign - preserve" | match existing | +1 | match existing |
| "redesign - overhaul" | +2 | +2 | match existing |

### 1.B Use-Case Presets

| Use case | VARIANCE | MOTION | DENSITY |
|---|---|---|---|
| Landing (SaaS, mainstream) | 7 | 6 | 4 |
| Landing (Agency / creative) | 9 | 8 | 3 |
| Landing (Premium consumer) | 7 | 6 | 3 |
| Portfolio (Designer / studio) | 8 | 7 | 3 |
| Portfolio (Developer) | 6 | 5 | 4 |
| Editorial / Blog | 6 | 4 | 3 |
| Public-sector service | 3 | 2 | 5 |
| Redesign - preserve | match | match+1 | match |
| Redesign - overhaul | +2 | +2 | match |

---

## 2. BRIEF → DESIGN SYSTEM MAP

### 2.A When to reach for a real design system

| Brief reads as… | Reach for |
|---|---|
| Microsoft / enterprise SaaS / dashboards | `@fluentui/react-components` |
| Google-ish UI, Material-flavored product | `@material/web` + Material 3 tokens |
| IBM-style B2B / enterprise analytics | `@carbon/react` + `@carbon/styles` |
| Shopify app surfaces | `polaris.js` / Polaris React |
| Atlassian / Jira-style product | `@atlaskit/*` |
| GitHub-style devtool | `@primer/css` or `@primer/react-brand` |
| Public-sector UK | `govuk-frontend` |
| US public-sector / trust-first | `uswds` |
| Fast local-business MVP | Bootstrap 5.3 |
| Modern accessible React foundation | `@radix-ui/themes` |
| Modern SaaS, own components | shadcn/ui (`npx shadcn@latest add ...`) |
| Tailwind-based modern SaaS / AI marketing | Tailwind v4 + `dark:` variant |

**Rules:**
- Install and use the **official** package. Never recreate its CSS by hand.
- **One system per project.** Do not mix Fluent React with Carbon, etc.

### 2.B When the brief is an aesthetic, not a system

| Aesthetic | Implementation |
|---|---|
| Glassmorphism / "frosted glass" | `backdrop-filter`, layered borders, solid-fill fallback for `prefers-reduced-transparency` |
| Bento (Apple-style tile grids) | CSS Grid with mixed cell sizes |
| Brutalism | Native CSS, monospace, raw borders |
| Editorial / magazine | Serif type, asymmetric grid, generous whitespace |
| Dark tech / hacker | Mono + accent neon, terminal motifs |
| Aurora / mesh gradients | SVG or layered radial gradients |
| Kinetic typography | Native CSS animations, scroll-driven animations, GSAP |
| Apple Liquid Glass | `backdrop-filter` + layered borders + highlights. Label as approximation. |

---

## 3. DEFAULT ARCHITECTURE & CONVENTIONS

Unless design read picks a real design system (Section 2.A), use these defaults:

### 3.A Stack
- **Framework:** React or Next.js. Default to Server Components (RSC).
  - RSC SAFETY: Global state works ONLY in Client Components. Wrap providers in `"use client"`.
  - INTERACTIVITY ISOLATION: Any component using Motion, scroll listeners, or pointer physics MUST be an isolated leaf with `'use client'`.
- **Styling:** **Tailwind v4** (default). Tailwind v3 only if existing project demands.
  - For v4: do NOT use `tailwindcss` plugin in `postcss.config.js`. Use `@tailwindcss/postcss` or Vite plugin.
- **Animation:** **Motion** (formerly Framer Motion). Import from `motion/react`.
- **Fonts:** Always use `next/font` (Next.js) or self-host with `@font-face` + `font-display: swap`. Never link Google Fonts via `<link>` in production.

### 3.B State
- Local `useState` / `useReducer` for isolated UI.
- Global state ONLY via Zustand, Jotai, or React context.
- **NEVER** use `useState` for continuous values (mouse position, scroll progress, pointer physics). Use Motion's `useMotionValue` / `useTransform` / `useScroll`.

### 3.C Icons
- **Allowed (priority order):** `@phosphor-icons/react`, `hugeicons-react`, `@radix-ui/react-icons`, `@tabler/icons-react`.
- **Discouraged:** `lucide-react` (acceptable only if user asks or project depends on it).
- **NEVER hand-roll SVG icons.**
- **One family per project.** Standardize `strokeWidth` globally (e.g. `1.5` or `2.0`).

### 3.D Emoji Policy
Discouraged by default. Replace with icon-library glyphs. Override only for explicitly playful / social-native briefs.

### 3.E Responsiveness & Layout Mechanics
- Standardize breakpoints: `sm 640`, `md 768`, `lg 1024`, `xl 1280`, `2xl 1536`.
- Contain layouts with `max-w-[1400px] mx-auto` or `max-w-7xl`.
- **NEVER** `h-screen` for Hero. **ALWAYS** `min-h-[100dvh]`.
- **NEVER** complex flexbox percentage math. **ALWAYS** CSS Grid.

### 3.F Dependency Verification (mandatory)
Before importing ANY 3rd-party library, check `package.json`. Output the install command if missing.

---

## 4. DESIGN ENGINEERING DIRECTIVES (Bias Correction)

### 4.1 Typography
- **Display / Headlines:** `text-4xl md:text-6xl tracking-tighter leading-none`
- **Body / Paragraphs:** `text-base text-gray-600 leading-relaxed max-w-[65ch]`
- **Sans font choice:** Default `Geist`, `Outfit`, `Cabinet Grotesk`, or `Satoshi`. Inter only for explicitly neutral / Linear-style briefs.
- **SERIF DISCIPLINE:** Serif is VERY DISCOURAGED as default. Only when the brand names a serif OR the aesthetic is genuinely editorial / luxury / heritage AND you can articulate why.
  - BANNED as defaults: `Fraunces` and `Instrument_Serif`.
- **ITALIC DESCENDER CLEARANCE:** When italic has descender letters (`y g j p q`), use `leading-[1.1]` minimum + `pb-1` or `mb-1` reserve.

### 4.2 Color Calibration
- Max 1 accent color. Saturation < 80% by default.
- **THE LILA RULE:** No automatic purple button glows, no random neon gradients. Use neutral bases (Zinc / Slate / Stone) with high-contrast singular accents.
- **One palette per project.** Do not mix warm and cool grays.
- **COLOR CONSISTENCY LOCK:** Once an accent color is chosen, it is used on the WHOLE page.
- **PREMIUM-CONSUMER PALETTE BAN:** For premium-consumer briefs, BANNED as defaults:
  - Backgrounds: `#f5f1ea`, `#f7f5f1`, `#fbf8f1`, `#efeae0`, `#ece6db` (warm cream / bone)
  - Accents: `#b08947`, `#b6553a`, `#9a2436` (brass / clay / oxblood)
  - Text: `#1a1714`, `#1a1814` (espresso near-black)
  - **Alternatives:** Cold Luxury (silver-grey + chrome), Forest (deep green + bone + amber), Black and Tan, Cobalt + Cream, Terracotta + Slate, Olive + Brick.

### 4.3 Layout Diversification
- **ANTI-CENTER BIAS:** Centered Hero avoided when `DESIGN_VARIANCE > 4`. Use Split Screen (50/50), Left-aligned / right-aligned asset, Asymmetric white-space.

### 4.4 Materiality, Shadows, Cards
- Use cards ONLY when elevation communicates real hierarchy. Otherwise `border-t`, `divide-y`, or negative space.
- Tint shadows to the background hue. No pure-black drop shadows on light backgrounds.
- **SHAPE CONSISTENCY LOCK:** One corner-radius scale for the entire page.

### 4.5 Interactive UI States
Always implement full cycles:
- **Loading:** Skeletal loaders matching final layout shape. No generic spinners.
- **Empty States:** Beautifully composed; indicate how to populate.
- **Error States:** Clear, inline (forms) or contextual (toasts for transient).
- **Tactile Feedback:** On `:active`, use `-translate-y-[1px]` or `scale-[0.98]`.
- **BUTTON CONTRAST CHECK (mandatory a11y):** WCAG AA min 4.5:1 (body), 3:1 (18px+ large text).
- **CTA BUTTON WRAP BAN:** Button text MUST fit on one line at desktop. 3 words max for primary CTAs.
- **NO DUPLICATE CTA INTENT:** One label per intent across the whole page.
- **FORM CONTRAST CHECK:** All inputs, placeholders, focus rings, helper/error text pass WCAG AA.

### 4.6 Data & Form Patterns
- Label ABOVE input. Error text BELOW input. `gap-2` for input blocks.
- No placeholder-as-label. Ever.

### 4.7 Layout Discipline (Hard Rules — failing any is shipping broken work)
- **Hero MUST fit in the initial viewport.** Headline max 2 lines desktop, subtext max 20 words, CTAs visible without scroll.
- **Hero font-scale discipline.** Default range: `text-4xl md:text-5xl lg:text-6xl`. `text-6xl md:text-7xl` only for 3-5 word headlines.
- **HERO TOP PADDING CAP:** Max `pt-24` (≈6rem) at desktop.
- **HERO STACK DISCIPLINE (max 4 text elements):** Eyebrow (or none) → Headline → Subtext → CTAs. BANNED in hero: trust strips, pricing teasers, bullet lists, social-proof avatar rows.
- **"Used by" / logo wall** belongs UNDER the hero, never inside it.
- **Navigation MUST render on a single line on desktop.** Height cap: 80px max, default 64-72px.
- **Bento grids MUST have rhythm, not one-sided repetition.** Exactly as many cells as content; no empty cells.
- **Section-Layout-Repetition Ban.** Each layout family appears at most ONCE per page (8 sections → min 4 different layout families).
- **ZIGZAG ALTERNATION CAP:** Max 2 consecutive image+text-split sections. Third is a Pre-Flight Fail.
- **EYEBROW RESTRAINT:** Max 1 eyebrow per 3 sections. If count > ceil(sectionCount / 3), output fails.
- **SPLIT-HEADER BAN:** "Left big headline + right small explainer" is banned as default.
- **Bento Background Diversity:** At least 2-3 cells need real visual variation (image, gradient, pattern, tint).
- **Mobile collapse MUST be explicit per section.**

### 4.8 Image & Visual Asset Strategy

Priority order:
1. **Image-generation tool first** — use `generate_image` or available tools to create section-specific assets. Do not skip.
2. **Real web images second** — `https://picsum.photos/seed/{descriptive-seed}/{w}/{h}`, or actual brand/stock URLs.
3. **Last resort: tell the user** — label placeholder slots clearly (`<!-- TODO: hero product photo, 1600x1200 -->`).

- **Even minimalist sites need real images.** Generate B&W minimalist photography if the brief is restrained.
- **Real company logos for social proof:** Use Simple Icons (`https://cdn.simpleicons.org/{slug}/ffffff`).
- **LOGO-ONLY rule:** Logo wall = logos only. No industry/category labels below each logo.
- **Div-based fake screenshots are BANNED.** Use real screenshot, generate via tool, or skip entirely.
- **Hero needs a real visual.** Text + gradient blob is not a hero.

### 4.9 Content Density
- **Default per section:** short headline (≤ 8 words) + short sub-paragraph (≤ 25 words) + one visual OR one CTA.
- **No data-dump sections.** Use Top 3-5 highlights + link, or carousel.
- **Long lists (> 5 items) need a different UI component:** 2-col split, card grid, tabs/accordion, horizontal scroll-snap pills, carousel, marquee.
- **Spec sheets specifically:** BANNED as long `border-b` tables. Use 2-col card grid, scroll-snap pills, grouped chunks, or featured-vs-rest.
- **COPY SELF-AUDIT (mandatory before ship):** Reread every visible string. Flag and rewrite: grammatically broken, unclear referents, AI-hallucination sound, LLM-cute copy, fake-precise numbers.
- **One copy register per page.**

### 4.10 Quotes & Testimonials
- Max 3 lines of quote text. Never a full paragraph.
- No generic "This changed my workflow" testimonials. Include specific outcomes, attribution, role, and company.
- Testimonial card must show avatar + name + role, not just text.

---

## 5. PRE-FLIGHT CHECKLIST (Run before declaring any task done)

Before outputting final code, run through every item:

- [ ] Design Read stated in one line before code
- [ ] Dials set and referenced throughout
- [ ] `DESIGN_VARIANCE > 4` → no centered hero (unless editorial/manifesto)
- [ ] `min-h-[100dvh]` on hero, never `h-screen`
- [ ] Hero fits in initial viewport (2-line headline, 20-word subtext, CTAs visible)
- [ ] Hero top padding ≤ `pt-24`
- [ ] Hero has ≤ 4 text elements
- [ ] No trust strip / logo wall inside the hero
- [ ] Navigation renders on single line at desktop, height ≤ 80px
- [ ] Eyebrow count ≤ ceil(sectionCount / 3)
- [ ] No split-header layout (unless documented compositional reason)
- [ ] Each layout family used at most once
- [ ] Max 2 consecutive image+text-split sections
- [ ] All bento grids fully populated (no empty cells)
- [ ] Bento has 2-3 cells with real visual variation
- [ ] One corner-radius scale locked across page
- [ ] One accent color locked across page (no warm/cool gray mixing)
- [ ] No premium-consumer cream+brass+espresso palette (unless justified)
- [ ] Serif NOT used (unless meets the strict criteria above)
- [ ] No Inter as default (unless brief explicitly calls for it)
- [ ] No `lucide-react` (unless project already depends on it)
- [ ] All buttons: text fits one line, WCAG AA contrast passes
- [ ] No duplicate CTA intent (one label per intent)
- [ ] All form inputs: WCAG AA contrast passes
- [ ] Full loading / empty / error states implemented
- [ ] All multi-column layouts have explicit `< 768px` mobile collapse
- [ ] At least 1 real image per section (generated or real URL)
- [ ] No div-based fake screenshots
- [ ] Logo wall is logos only (no category labels)
- [ ] No hand-rolled SVG icons
- [ ] Emoji absent (unless explicitly playful brief)
- [ ] `package.json` checked before any 3rd-party import
- [ ] One design system per project (no mixing)
- [ ] Copy audited: no broken grammar, no AI-cute phrases, no fake-precise numbers

---

## 6. LOURDES INTEGRATION NOTES

When this skill is active, Lourdes applies all rules above to any frontend
generation task. The skill is especially relevant when:

- User asks to build or redesign a **landing page, portfolio, or marketing site**
- User says: `/taste`, `use taste-skill`, `premium frontend`, `anti-slop design`,
  `design read`, `apply taste`, `no slop`, `make it look good`, or simply asks
  for a high-quality frontend without specifying a framework
- User provides a URL or screenshot for a redesign and says "make it better"

This skill does NOT apply to: dashboards, data tables, internal tools, admin
panels, complex multi-step product UIs. For those, use the orchestrator skill
and standard component patterns.

**Remember:** The first output is always the Design Read. Then the dials.
Then code. Never code without a declared design direction.
