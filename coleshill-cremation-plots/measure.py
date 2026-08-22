import cv2, numpy as np, json

im = cv2.imread('scan-1.png', 0)
bw = cv2.adaptiveThreshold(im, 255, cv2.ADAPTIVE_THRESH_MEAN_C,
                           cv2.THRESH_BINARY_INV, 41, 12)
bw = cv2.morphologyEx(bw, cv2.MORPH_CLOSE, np.ones((3, 3), np.uint8))
horiz = cv2.morphologyEx(bw, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (45, 1)))
vert  = cv2.morphologyEx(bw, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (1, 45)))
# hand-drawn rules wobble; widen them so a straight probe still hits
vert_f  = cv2.dilate(vert,  np.ones((1, 13), np.uint8))
horiz_f = cv2.dilate(horiz, np.ones((13, 1), np.uint8))

boxes = json.load(open('boxes_raw.json'))
cx = [x + w / 2 for x, y, w, h, a in boxes]
idx = sorted(range(len(boxes)), key=lambda i: cx[i])
lanes, cur = [], [idx[0]]
for i in idx[1:]:
    if cx[i] - cx[cur[-1]] > 60:
        lanes.append(cur); cur = []
    cur.append(i)
lanes.append(cur)

def runs(mask_1d, thresh, min_sep=14):
    hits = np.where(mask_1d > thresh)[0]
    if not len(hits): return []
    out, run = [], [hits[0]]
    for p in hits[1:]:
        if p - run[-1] > min_sep:
            out.append(int(np.mean(run))); run = []
        run.append(p)
    out.append(int(np.mean(run)))
    return out

result = []
for L in lanes:
    bs = [boxes[i] for i in L]
    xa = min(b[0] for b in bs) - 15; xb = max(b[0] + b[2] for b in bs) + 15
    ya = min(b[1] for b in bs) - 15; yb = max(b[1] + b[3] for b in bs) + 15

    # the two lane walls, located from the ink itself
    vprof = vert_f[ya:yb, xa:xb].sum(0) / 255
    walls = [xa + w for w in runs(vprof, (yb - ya) * 0.25, 30)]
    wl, wr = walls[0], walls[-1]

    rules = [ya + r for r in runs(horiz_f[ya:yb, wl:wr].sum(1) / 255, (wr - wl) * 0.5)]

    cells = []
    for a, b in zip(rules, rules[1:]):
        if b - a < 16: continue
        seg = slice(a + 10, b - 10)
        left  = vert_f[seg, wl - 10:wl + 10].max(1).sum() / 255
        right = vert_f[seg, wr - 10:wr + 10].max(1).sum() / 255
        need = (b - a - 20) * 0.55
        cells.append({"y0": int(a), "y1": int(b),
                      "cell": bool(left > need and right > need)})
    result.append({"wl": int(wl), "wr": int(wr), "cells": cells})

for r in result:
    real = [c for c in r["cells"] if c["cell"]]
    gaps = [c for c in r["cells"] if not c["cell"]]
    hs = sorted(c["y1"] - c["y0"] for c in real)
    print(f"lane x{r['wl']:5}-{r['wr']:5} w={r['wr']-r['wl']:3}  cells={len(real):3} "
          f"gaps={len(gaps):2}  pitch med={hs[len(hs)//2] if hs else 0} "
          f"range={hs[0] if hs else 0}-{hs[-1] if hs else 0}")
json.dump(result, open('lanes.json', 'w'))

# --- reclassify by ink: a real cell contains a handwritten number, a gap is bare
print()
final = []
for r in result:
    wl, wr = r["wl"], r["wr"]
    out = []
    for c in r["cells"]:
        a, b = c["y0"], c["y1"]
        reg = bw[a + 14:b - 14, wl + 10:wr - 10]
        ink = float(reg.mean() / 255) if reg.size else 0.0
        out.append({**c, "ink": round(ink, 4)})
    final.append({"wl": wl, "wr": wr, "cells": out})

allink = sorted(c["ink"] for r in final for c in r["cells"])
print("ink deciles:", [round(allink[int(len(allink)*p)], 3) for p in
                       (0, .1, .2, .3, .4, .5, .7, .9)])
