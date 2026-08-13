# Session Handoff — Amazon Associates Snippet Plugin

## Last Updated
2026-08-13

## Version
v1.5.2

## Git State
- Repo: `~/.lourdes` (this folder is tracked there; NOT its own repo)
- Branch: `main`
- Last commit: `9baddcf` — v1.5.2: Auto-recover from expired Creators API Bearer tokens

## In-Progress Work
- [ ] Modified `public/amazon-affiliate-program.html`, `amazon-plugin-landing.html`, `amazon-snippets.html` (uncommitted)
- [ ] New untracked `api/` service: `audit.js`, `login.js`, `logout.js`, `session.js`
- [ ] New untracked `private/auth.js` + `private/audit/amazon-associates-plugin-audit-2026.pdf`
- [ ] New untracked `public/audit.html`, `public/developer.html`
- [ ] New `AGENTS.md` and this `SESSION_HANDOFF.md` (untracked)

## Notes / Decisions
- Plugin lives in `amazon-associates-snippets/`; marketing pages in `public/`.
- OAuth tokens are transient-cached with auto-recovery on expiry (v1.5.2).
- Keep credentials/service accounts out of commits (`private/`, `.env` only).

## Next Steps
- (fill in)

## Open Questions
- (fill in)
