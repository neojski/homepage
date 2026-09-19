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
- North is to the right of the sheet, along the rows — not up the page. The
  compass on the drawing was wrong until the parish corrected it on a printout.
- Plot 83 is drawn detached at the foot of the sheet, in the space between rows
  A and B, beyond the end of both. Its true position on the ground is unknown.

## Status categories

Four words, and only these four:

    occupied
    occupied-reserved     drawn as a diagonal half-fill
    reserved
    vacant

Case is ignored; nothing else is. There are deliberately no synonyms — an
unrecognised word refuses the whole paste rather than being guessed at, and a
guessed status on a burial record is worse than a refusal.

**There is no word for absent data.** There was a fifth, `unknown`, drawn
hatched, and a cell left empty in the sheet became it. It is gone: every plot
on the drawn map holds one of the four. A map that quietly carried "we do not
know" for a plot was too easy to read past, and the sheet is the place to fix
a gap, not the map. The hatching survives only as the look of a map with
nothing pasted into it yet — cells with no `data-status` at all — so an empty
page cannot be mistaken for a churchyard of vacant plots.

A blank status cell is therefore not a status but a *silence*, and silence is
only refused when nothing else speaks. Two names against one plot with a date
of death on only one of them is the ordinary shape of the register, not an
error: the dated line settles the plot and the other adds nothing. It is a
plot that no line speaks for that is refused, by line number.

The 2026 register settles the two the scan left blank: **88** is occupied and
**135** is reserved.

The vocabulary is not fixed in code. `CONFIG.statuses` at the top of the script
in `index.html` holds each status's key, label and fill; the legend, the
accepted-words panel and the colours are all generated from it. Changing a word
or a colour is a one-line edit there.

## What the page will read

Two shapes, and the reader decides between them from the first line.

- **Two bare columns**, plot and status, as before.
- **The parish register itself**, headings and all — `Cremation Plots update
  2026.pdf.xlsx`, sheet *Cremations - Corrected*, columns
  `Plot | | Surname | Christian name | Date of Death | …`. If the first line
  has a plot heading it is used to find the plot column and the status column
  (`Status`, `Date of Death`, `Died`); every other column is ignored, names
  included. Paste the whole block or just those two columns; either works.

The register is a line per interment, not per plot, so two rules follow:

- **A date of death is a status.** It says someone is buried there, so it reads
  as occupied. Dates arrive as bare years, as Excel serials, and as real dates,
  and all three count. This is the only inference the reader makes; an
  unrecognised *word* is still refused rather than guessed at.
- **An empty status column defers rather than refuses.** Row 99 of the 2026
  sheet is the case to keep working: plot 98, a second interment with no date
  of death recorded, settled by the dated line above it. Only a plot where
  every line is silent is refused.
- **Several lines may share a plot.** Their statuses are folded together, and
  occupied plus reserved is exactly the split state the map draws. A folding
  that makes no sense — vacant against occupied — is reported as a
  contradiction in the sheet and nothing is drawn.

A tab-separated paste of more than two columns with no heading row is refused
with an explanation rather than line by line; a comma-separated line may still
run long, because `occupied, reserved` is a status key with a comma in it.

## Where the design discussion got to

- **Colour**: the four states are a sequence (vacant -> reserved -> occupied and
  reserved -> occupied), so lightness should carry the order and hue should only
  distinguish the kind of claim. Must survive photocopying. Hatching is kept
  back from the vocabulary entirely, for the map that has no data in it yet.
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
2. Plot **115** is on the map but has no line at all in the 2026 register, so
   a paste of that sheet is one plot short and is refused. The printed survey
   has it reserved. The sheet needs the line; the page should not paper over
   it. (Plot 98's undated second interment, Michael Temple Darvill, is *not*
   a gap of this kind — the dated line settles the plot.)
3. Where does 83 actually sit?
4. Will names ever be attached to plots? That changes whether the map can be
   public, and would end the paste-anything-in model.
