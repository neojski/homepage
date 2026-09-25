# Coleshill cremation plot map

Cremation plot map for All Saints' Church, Coleshill: 140 plots. `index.html`
is the whole thing — no build step, no dependencies. Someone pastes the
parish register (or two columns, plot and status) and it draws the plan. The
parish's spreadsheet is the only source of truth; the page is a viewer for it.

## Rules

- **Don't touch the cell geometry.** The `<svg>` coordinates were measured off
  the hand-drawn survey, and the irregularity is real. Never hand-estimate
  positions or tidy them onto a grid. Nothing checks this any more.
- **No statuses in the page.** Plot cells carry no `data-status`; a status only
  ever comes from the paste. An empty page is hatched, so it can't be mistaken
  for a churchyard of vacant plots.
- **Four words, no synonyms**: `occupied`, `occupied-reserved`, `reserved`,
  `vacant`, defined in `CONFIG.statuses`. Any bad line, unknown word or missing
  plot refuses the whole paste and draws nothing. A refusal beats a guessed
  status on a burial record.
- **Nothing is stored.** No network, no `localStorage`, no cache; reload and the
  page is blank. That is what makes it safe to host publicly. Attaching names
  to plots would change that.
