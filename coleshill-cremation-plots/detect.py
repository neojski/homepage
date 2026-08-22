import cv2, numpy as np, json

im = cv2.imread('scan-1.png', 0)
# Ink is dark. Binarise so ink = 255.
bw = cv2.adaptiveThreshold(im, 255, cv2.ADAPTIVE_THRESH_MEAN_C,
                           cv2.THRESH_BINARY_INV, 41, 12)
# The rules are hand-drawn, so they wobble; close small breaks before
# isolating long runs in each direction.
bw = cv2.morphologyEx(bw, cv2.MORPH_CLOSE, np.ones((3, 3), np.uint8))

H, V = 45, 45
horiz = cv2.morphologyEx(bw, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (H, 1)))
vert  = cv2.morphologyEx(bw, cv2.MORPH_OPEN, cv2.getStructuringElement(cv2.MORPH_RECT, (1, V)))
grid = cv2.dilate(cv2.bitwise_or(horiz, vert), np.ones((5, 5), np.uint8), iterations=1)

print('ink px', int(bw.sum()//255), 'horiz', int(horiz.sum()//255), 'vert', int(vert.sum()//255))

# Cells are the enclosed white regions inside the grid.
n, lab, stats, cent = cv2.connectedComponentsWithStats(cv2.bitwise_not(grid), 8)
boxes = []
for i in range(1, n):
    x, y, w, h, a = stats[i]
    if 900 < a < 60000 and 12 < w < 400 and 12 < h < 400:
        boxes.append((int(x), int(y), int(w), int(h), int(a)))
print('candidate cells', len(boxes))
ws = sorted(b[2] for b in boxes); hs = sorted(b[3] for b in boxes)
if boxes:
    print('w median', ws[len(ws)//2], 'range', ws[0], ws[-1])
    print('h median', hs[len(hs)//2], 'range', hs[0], hs[-1])
json.dump(boxes, open('boxes_raw.json', 'w'))
cv2.imwrite('dbg_grid.png', grid)
