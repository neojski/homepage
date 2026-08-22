import cv2, numpy as np, json
exec(open('measure.py').read().split('# --- reclassify')[0])

LANE_ROW = ["A", "B", "C", "D", "E", "F"]   # left to right on the scan
EXPECT = {"A": 26, "B": 26, "C": 25, "D": 23, "E": 21, "F": 18}

out = {}
for name, r in zip(LANE_ROW, result):
    wl, wr = r["wl"], r["wr"]
    seq = []
    for c in r["cells"]:
        a, b = c["y0"], c["y1"]
        reg = bw[a + 14:b - 14, wl + 10:wr - 10]
        ink = float(reg.mean() / 255) if reg.size else 0.0
        seq.append({"y0": a, "y1": b, "len": b - a, "cell": ink > 0.04})
    out[name] = {"wl": wl, "wr": wr, "seq": seq}
    n = sum(1 for s in seq if s["cell"])
    g = len(seq) - n
    print(f"row {name}: cells={n:2} (expected {EXPECT[name]:2})  gaps={g}")

print()
for name in LANE_ROW:
    seq = out[name]["seq"]
    pat = "".join("#" if s["cell"] else "." for s in seq)
    # group into blocks
    blocks, run = [], 0
    for s in seq:
        if s["cell"]: run += 1
        elif run: blocks.append(run); run = 0
    if run: blocks.append(run)
    print(f"row {name}: {pat}   blocks {blocks}")
json.dump(out, open('lanes_final.json', 'w'))
