#!/usr/bin/env python3
"""Render the Coleshill cremation plot map from measured scan geometry.

Cell positions are not idealised: they come from `lanes_final.json`, produced by
measuring the ruled lines on a 300dpi raster of the 2026 survey. Gaps between
blocks therefore appear at their drawn widths, and rows align exactly as the
draughtsman drew them.

The survey is drawn in landscape with north to the right of the sheet, so scan
coordinates are rotated a quarter turn anticlockwise here: scan-y becomes map-x,
and scan-x becomes map-y measured upwards.
"""

import json

# Plot numbers in drawn order, per row, top-to-bottom on the scan.
NUMBERING = {
    "A": list(range(1, 12)) + list(range(13, 21)) + list(range(22, 26)) + [27, 28, 29],
    "B": list(range(30, 35)) + [35] + list(range(37, 41)) + list(range(42, 50))
         + [51, 52] + list(range(53, 59)),
    "C": [59, 60, 62, 63, 64] + list(range(65, 69)) + list(range(71, 79))
         + [80, 81, 82, 84, 85, 86, 87, 88],
    "D": list(range(89, 104)) + [109] + list(range(110, 116)),
    "E": list(range(142, 127, -1)) + [122, 121, 120, 119, 118, 117, 116],
    "F": list(range(143, 158)) + [159, 160, 161],
}
# Drawn detached at the foot of the sheet, in the space between rows A and B.
DETACHED = {83: {"x0": 732, "x1": 853, "y0": 3410, "y1": 3509}}


def expand(*parts):
    out = []
    for p in parts:
        out.extend(range(p[0], p[1] + 1) if isinstance(p, tuple) else [p])
    return out


STATUS = {}
for n in expand((1, 11), (13, 20), (22, 25), 27, 28, 29, (30, 35), (37, 40),
                43, 47, 49, (51, 64), 66, 67, 71, 72, (74, 78), (80, 87),
                90, 92, 93, 94, (96, 102), 109, (111, 114), 116, 117, 120,
                132, 133, 136):
    STATUS[n] = "occupied"
for n in [44, 91, 103, 128, 130, 134]:
    STATUS[n] = "occupied-reserved"
for n in [42, 48, 65, 68, 73, 95, 110, 115, 118, 119, 121, 122, 129, 131,
          137, 138, 139, 140, 141, 142]:
    STATUS[n] = "reserved"
for n in expand(45, 46, 89, (143, 157), 159, 160, 161):
    STATUS[n] = "free"

INK, HAIRLINE, GROUND = "#1f2933", "#5b6672", "#f7f6f3"
FILL = {"occupied": "#6e8ca8", "occupied-reserved": "#6e8ca8",
        "reserved": "#d9a441", "free": "#ffffff", "unrecorded": "#e2e0da"}
TEXT_ON = {"occupied": "#ffffff", "occupied-reserved": INK, "reserved": INK,
           "free": INK, "unrecorded": "#8a867d"}

lanes = json.load(open("lanes_final.json"))
S = 0.36                      # scan pixels -> svg units
PAD_X, PAD_Y = 96, 300        # room for the path rule, and for the title block

scan_y0 = min(s["y0"] for r in lanes.values() for s in r["seq"])
scan_x1 = max(r["wr"] for r in lanes.values())


def place(x0, x1, y0, y1):
    return (round((y0 - scan_y0) * S + PAD_X, 1),
            round((scan_x1 - x1) * S + PAD_Y, 1),
            round((y1 - y0) * S, 1), round((x1 - x0) * S, 1))


records = []
for row, lane in lanes.items():
    nums = iter(NUMBERING[row])
    for seg in lane["seq"]:
        if not seg["cell"]:
            continue
        plot = next(nums)
        x, y, w, h = place(lane["wl"], lane["wr"], seg["y0"], seg["y1"])
        records.append({"id": plot, "row": row, "status": STATUS.get(plot, "unrecorded"),
                        "x": x, "y": y, "w": w, "h": h})
    leftover = list(nums)
    assert not leftover, f"row {row}: {len(leftover)} numbers unplaced: {leftover}"

for plot, d in DETACHED.items():
    x, y, w, h = place(d["x0"], d["x1"], d["y0"], d["y1"])
    records.append({"id": plot, "row": "C", "detached": True,
                    "status": STATUS.get(plot, "unrecorded"),
                    "x": x, "y": y, "w": w, "h": h})

right = max(r["x"] + r["w"] for r in records)
bottom = max(r["y"] + r["h"] for r in records)
top = min(r["y"] for r in records)
W, H = round(right + 120), round(bottom + 96)

o = []
add = o.append
add(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" width="{W}" '
    f'height="{H}" font-family="Inter, Helvetica Neue, Arial, sans-serif">')
add(f'<rect width="{W}" height="{H}" fill="{GROUND}"/>')
add(f'<text x="{PAD_X}" y="62" font-size="27" font-weight="600" letter-spacing="0.5" '
    f'fill="{INK}">All Saints\u2019 Church, Coleshill</text>')
add(f'<text x="{PAD_X}" y="90" font-size="15" letter-spacing="2.6" fill="{HAIRLINE}">'
    f'CREMATION PLOTS \u00b7 2026</text>')
add(f'<line x1="{PAD_X}" y1="108" x2="{PAD_X+360}" y2="108" stroke="{HAIRLINE}"/>')

ly = 142
for key, label in [("occupied", "Occupied"),
                   ("occupied-reserved", "Occupied, reserved for further ashes"),
                   ("reserved", "Reserved"), ("free", "Free"),
                   ("unrecorded", "No status recorded")]:
    if key == "occupied-reserved":
        add(f'<rect x="{PAD_X}" y="{ly}" width="26" height="20" fill="#ffffff"/>')
        add(f'<polygon points="{PAD_X},{ly} {PAD_X+26},{ly} {PAD_X},{ly+20}" fill="{FILL[key]}"/>')
    else:
        add(f'<rect x="{PAD_X}" y="{ly}" width="26" height="20" fill="{FILL[key]}"/>')
    add(f'<rect x="{PAD_X}" y="{ly}" width="26" height="20" fill="none" stroke="{INK}"/>')
    add(f'<text x="{PAD_X+38}" y="{ly+15}" font-size="14" fill="{INK}">{label}</text>')
    ly += 30
add(f'<text x="{PAD_X}" y="{ly+14}" font-size="12.5" fill="{HAIRLINE}">'
    f'Cell positions measured from the 2026 survey. Plots 88 and 135 have no status recorded.</text>')

add(f'<line x1="{PAD_X-40}" y1="{top}" x2="{PAD_X-40}" y2="{bottom}" '
    f'stroke="{HAIRLINE}" stroke-width="2"/>')
mid = round((top + bottom) / 2)
add(f'<text x="{PAD_X-56}" y="{mid}" font-size="13.5" letter-spacing="3.2" fill="{HAIRLINE}" '
    f'text-anchor="middle" transform="rotate(-90 {PAD_X-56} {mid})">PATH</text>')
add(f'<line x1="{PAD_X}" y1="{bottom+34}" x2="{round(right)}" y2="{bottom+34}" '
    f'stroke="{HAIRLINE}" stroke-width="2"/>')
add(f'<text x="{PAD_X}" y="{bottom+56}" font-size="13.5" letter-spacing="3.2" '
    f'fill="{HAIRLINE}">PATH</text>')
add(f'<g transform="translate({W-52} {top+60})">')
add(f'<line x1="0" y1="46" x2="0" y2="-6" stroke="{INK}" stroke-width="1.6"/>')
add(f'<polygon points="0,-16 6,2 -6,2" fill="{INK}"/>')
add(f'<text x="0" y="66" font-size="14" font-weight="600" fill="{INK}" text-anchor="middle">N</text>')
add('</g>')

for r in sorted(records, key=lambda r: r["id"]):
    x, y, w, h, s = r["x"], r["y"], r["w"], r["h"], r["status"]
    if s == "occupied-reserved":
        add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="#ffffff"/>')
        add(f'<polygon points="{x},{y} {x+w},{y} {x},{y+h}" fill="{FILL[s]}"/>')
    else:
        add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="{FILL[s]}"/>')
    add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="none" stroke="{INK}"/>')
    add(f'<text x="{x+w/2:.1f}" y="{y+h/2+4.5:.1f}" font-size="12.5" fill="{TEXT_ON[s]}" '
        f'text-anchor="middle">{r["id"]}</text>')

for row, lane in lanes.items():
    _, y, _, h = place(lane["wl"], lane["wr"], 0, 0)
    add(f'<text x="{PAD_X-14}" y="{y+ (lane["wr"]-lane["wl"])*S/2 + 4.5:.1f}" font-size="12" '
        f'fill="{HAIRLINE}" text-anchor="middle">{row}</text>')
add('</svg>')

open("/mnt/user-data/outputs/coleshill-cremation-plots-v4.svg", "w").write("\n".join(o))
json.dump({"site": "All Saints' Church, Coleshill", "survey": "Cremation Plots 2026",
           "source": "measured from 300dpi raster of the original sheet",
           "plots": sorted(records, key=lambda r: r["id"])},
          open("/mnt/user-data/outputs/coleshill-plots.json", "w"), indent=2)
print(f"{len(records)} plots rendered, canvas {W}x{H}")
