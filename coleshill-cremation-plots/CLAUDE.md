# Coleshill cremation plot map

Digitising the hand-drawn 2026 cremation plot survey for All Saints' Church,
Coleshill. 140 plots. The map is one static page: someone pastes two columns —
plot number and status — and it draws the colour-coded plan.

## Pipeline

    Cremation_Plots_2026.pdf
      -> detect.py    rasterise at 300dpi, find candidate cells
      -> measure.py   locate lane walls and cross-rules in the ink
      -> extract.py   classify each interval as cell or gap -> lanes_final.json
      -> render.py    apply plot numbers and statuses -> SVG + JSON

Run in that order. `pdftoppm -r 300 -png -gray <pdf> scan` produces `scan-1.png`,
which the detection steps expect in the working directory.

Requires: opencv-python, numpy. `pdftoppm` from poppler-utils.

## The page

`index.html` is the whole thing, and now the only thing — CONFIG, styles, the
drawing and the script, in one file. No build step, no dependencies, no tooling.
Edit it directly; open it off the filesystem to see the result.

The `<svg>` in the middle is the measured geometry: 140 `<g class="cell">`
elements carrying the surveyed coordinates. It began as a separate `map.svg`
emitted by `render.py`, with a checked-in script that compared the two copies
and failed on any coordinate drift. Both were dropped as surplus once the survey
was final. That guard is gone, so the rule in the next section is now enforced
by nothing but care.

## Why it is measured rather than traced

Three earlier attempts placed cells by eye from the scan and each one silently
broke alignments elsewhere. Positions now come from measuring the ruled lines on
the raster. Do not reintroduce hand-estimated coordinates or "tidy" the geometry
onto a uniform grid — the survey's irregularity is load-bearing.

Two failure modes already hit, worth not repeating:

- Discarding short intervals during rule detection silently merges the blocks on
  either side, erasing narrow gaps (52|53 and 81|82 are ~11 units wide).
- Miscounting a row shifts every subsequent number in it. `render.py` asserts
  that each row's transcribed numbers exactly consume its measured cells, so a
  miscount fails loudly. Keep that assertion.

## Layout facts established from measurement

- Six rows, lettered A (furthest from Barrack Hill) to F (nearest).
- Rows A-C are drawn at ~97px per plot, rows D-F at ~126px. The 4:3 difference
  is real and is why 71-78 spans the same ground as 98-103.
- Plot 116 is the last cell of row E, not row D. Row E reads 142-128, then
  122 down to 116. Row D's final block is 110-115.
- Plot 83 is drawn detached at the foot of the sheet, in the space between rows
  A and B, beyond the end of both. Its true position on the ground is unknown.

## Status categories

Five words, and only these five:

    occupied
    occupied-reserved     drawn as a diagonal half-fill
    reserved
    vacant
    unknown               hatched, so it reads as absent data not a status

Case is ignored; nothing else is. There are deliberately no synonyms — an
unrecognised word refuses the whole paste rather than being guessed at, and a
guessed status on a burial record is worse than a refusal. A cell left empty
in the spreadsheet is the one exception: it counts as `unknown`, since that is
how a blank is meant in the sheet.

In the 2026 survey the unknowns are plots **88 and 135**, still to be resolved
with the parish — but the page reads that from the pasted data rather than
assuming any particular plots are the blank ones.

The vocabulary is not fixed in code. `CONFIG.statuses` at the top of the script
in `index.html` holds each status's key, label and fill; the legend, the
accepted-words panel and the colours are all generated from it. Changing a word
or a colour is a one-line edit there.

## Where the design discussion got to

- **Colour**: the four states are a sequence (vacant -> reserved -> occupied and
  reserved -> occupied), so lightness should carry the order and hue should only
  distinguish the kind of claim. Must survive photocopying. Unrecorded should be
  hatched rather than filled, so it reads as absent data rather than a status.
- **"Crib"** in the parish's correspondence means the legend/key panel.
- **Hosting**: one file, `index.html`. Works over `file://` or from any
  static host. No server, no credentials, no stored record — the parish's
  spreadsheet is the only source of truth and the page is a viewer for it.
  An earlier PHP version (writable JSON, atomic writes, dated backups, version
  conflicts, `.htaccess` Basic auth) was removed in favour of this; it is in
  the history if the reasoning is ever needed.
- **Bad input is refused whole.** Any unknown plot, unreadable status, duplicate
  row, or missing plot lists every problem and draws nothing. A paste must cover
  all 140 plots — with no stored state there is nothing to merge a partial paste
  into. It is a burial record; a refusal beats a half-applied one.
- **Nothing is stored at all.** The paste lives in the textarea and nowhere
  else — no network, no `localStorage`, no cache. Reload and the page is blank
  again. A burial record that looked current but was a stale cached paste would
  be worse than an obviously empty page. That is what makes it safe to host
  publicly today — and what open question 4 would change.

## Open questions

1. Are all plots the same size on the ground, or are rows D-F genuinely larger?
2. Statuses for 88 and 135.
3. Where does 83 actually sit?
4. Will names ever be attached to plots? That changes whether the map can be
   public, and would end the paste-anything-in model.
