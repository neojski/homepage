# Coleshill cremation plot map

Digitising the hand-drawn 2026 cremation plot survey for All Saints' Church,
Coleshill. 140 plots. The end goal is a colour-coded map hosted on a personal
website, with an authenticated editor for changing the palette and plot statuses.

## Pipeline

    Cremation_Plots_2026.pdf
      -> detect.py    rasterise at 300dpi, find candidate cells
      -> measure.py   locate lane walls and cross-rules in the ink
      -> extract.py   classify each interval as cell or gap -> lanes_final.json
      -> render.py    apply plot numbers and statuses -> SVG + JSON

Run in that order. `pdftoppm -r 300 -png -gray <pdf> scan` produces `scan-1.png`,
which the detection steps expect in the working directory.

Requires: opencv-python, numpy. `pdftoppm` from poppler-utils.

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

occupied; occupied but reserved for further ashes (diagonal half-fill);
reserved; free. Plots **88 and 135 have no status recorded** and render as
"unrecorded" — still to be resolved with the parish.

## Where the design discussion got to

- **Colour**: the four states are a sequence (free -> reserved -> occupied and
  reserved -> occupied), so lightness should carry the order and hue should only
  distinguish the kind of claim. Must survive photocopying. Unrecorded should be
  hatched rather than filled, so it reads as absent data rather than a status.
- **"Crib"** in the parish's correspondence means the legend/key panel.
- **Hosting**: static geometry file plus a small JSON holding palette and plot
  statuses. `GET /api/plots` public, `PUT /api/plots` authenticated. Editor on a
  separate path from the public map.
- **Writes**: temp file plus atomic rename, dated backup on every save, version
  number in the JSON rejected on `PUT` if stale. It is a burial record.
- **Auth**: `.htaccess` Basic auth is sufficient for one or two editors, given
  HTTPS, `htpasswd -B`, and the password file outside the web root. It guards
  the URL path, so static JSON is covered as well as any scripts.

## Open questions

1. Are all plots the same size on the ground, or are rows D-F genuinely larger?
2. Statuses for 88 and 135.
3. Where does 83 actually sit?
4. Should the editor also change plot statuses, or only the palette?
5. Will names ever be attached to plots? That changes whether the map can be
   public.
