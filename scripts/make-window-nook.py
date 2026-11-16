# Window nook picture (from the source photo): see-through glass, the two
# bottom sashes cut out into their own layer so the window can open.
#   python3 scripts/make-window-nook.py <source 2000x1116 photo>
import sys
import numpy as np
from PIL import Image, ImageDraw, ImageFilter

src = Image.open(sys.argv[1]).convert('RGBA')
W, H = src.size
upper_panes = [(681, 143, 958, 534), (1041, 139, 1319, 534)]
lower_panes = [(677, 560, 959, 840), (1041, 558, 1324, 840)]
lower_sashes = [(651, 537, 981, 867), (1018, 537, 1350, 867)]
LAYER = (645, 530, 1356, 872)          # the sash layer's box in the picture

def soft_mask(boxes, blur=1.2):
    m = Image.new('L', (W, H), 0)
    d = ImageDraw.Draw(m)
    for b in boxes:
        d.rectangle(b, fill=255)
    return np.array(m.filter(ImageFilter.GaussianBlur(blur))).astype(float) / 255

a = np.array(src).astype(float)
glass = soft_mask(upper_panes + lower_panes)
alpha = 255 - glass * (255 - 38)                     # glass: faint reflection only

# The room: no bottom sashes (the opening shows the view behind)
room = a.copy()
room[..., 3] = alpha * (1 - soft_mask(lower_sashes, .6))
Image.fromarray(room.clip(0, 255).astype('uint8')).save('public/images/shelves/window-nook.webp', quality=80, method=6)

# The sashes: wood frames and their glass, nothing else
sash = a.copy()
sash[..., 3] = alpha * soft_mask(lower_sashes, .6)
x1, y1, x2, y2 = LAYER
Image.fromarray(sash[y1:y2, x1:x2].clip(0, 255).astype('uint8')).save('public/images/shelves/window-sash.webp', quality=86, method=6)
print('room + sash layer', (x2 - x1, y2 - y1))
