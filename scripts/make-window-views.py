# Window views from one daytime picture per season: day, dawn, dusk and night.
import sys, random
import numpy as np
from PIL import Image, ImageFilter, ImageDraw

SRC = '/tmp/claude-0/-home-user-bookworm/27acbdbf-707c-5528-ad6d-196c2c73da63/images/'
files = {'summer': '21.jpg', 'autumn': '22.jpg', 'spring': '23.jpg', 'winter': '24.jpg'}
OUT = 'public/images/shelves/window/'
SIZE = 1200

def sky_mask(a):
    h, w, _ = a.shape
    lum = a.mean(axis=2) / 255
    sat = (a.max(axis=2) - a.min(axis=2)) / 255
    blueish = (a[..., 2] >= a[..., 0] - 5)
    ys = np.linspace(0, 1, h)[:, None]
    m = ((lum > .62) & (sat < .35) & blueish & (ys < .62)).astype(float)
    m = np.array(Image.fromarray((m * 255).astype('uint8')).filter(ImageFilter.GaussianBlur(18))) / 255
    return m * np.clip((0.66 - ys) / 0.2, 0, 1)

def grade(a, mul, add=(0, 0, 0), gamma=1.0):
    out = (a / 255) ** gamma * np.array(mul) + np.array(add) / 255
    return np.clip(out, 0, 1) * 255

def gradient(h, w, top, bottom, upto=1.0):
    ys = np.clip(np.linspace(0, 1, h) / upto, 0, 1)[:, None, None]
    return np.array(top)[None, None] * (1 - ys) + np.array(bottom)[None, None] * ys * np.ones((h, w, 1))

def blend(base, color, alpha):
    return base * (1 - alpha) + color * alpha

def make(season, file):
    im = Image.open(SRC + file).convert('RGB').resize((SIZE, SIZE), Image.LANCZOS)
    a = np.array(im).astype(float)
    h, w, _ = a.shape
    sky = sky_mask(a)[..., None]
    ys = np.linspace(0, 1, h)[:, None, None]

    day = a
    # dawn: soft pink-gold sky, cool shadows, a warm haze low on the land
    dawn = grade(a, (1.0, .9, .88), (8, 0, 6), 1.12)
    dawn = blend(dawn, gradient(h, w, (250, 196, 190), (255, 214, 150), .6), sky * .75)
    dawn = blend(dawn, np.array([255, 205, 160]), np.clip(1 - np.abs(ys - .58) / .18, 0, 1) * .22)
    # dusk: deep orange sky, warm darker land
    dusk = grade(a, (1.02, .78, .62), (6, 0, 0), 1.3)
    dusk = blend(dusk, gradient(h, w, (110, 70, 120), (255, 150, 80), .62), sky * .85)
    dusk = blend(dusk, np.array([255, 150, 70]), np.clip(1 - np.abs(ys - .6) / .14, 0, 1) * .25)
    # night: from dusk, blue and dark, with stars and a moon
    night = grade(dusk, (.32, .38, .62), (2, 4, 14), 1.25)
    night = blend(night, gradient(h, w, (10, 14, 38), (38, 48, 90), .62), sky * .9)

    img = Image.fromarray(night.clip(0, 255).astype('uint8'))
    d = ImageDraw.Draw(img, 'RGBA')
    rnd = random.Random(season)
    smask = sky[..., 0]
    for _ in range(260):
        x, y = rnd.randrange(w), rnd.randrange(int(h * .6))
        if smask[y, x] < .5:
            continue
        r = rnd.choice([.6, .8, 1, 1.3])
        d.ellipse([x - r, y - r, x + r, y + r], fill=(255, 255, 240, rnd.randrange(150, 255)))
    # the moon: a small crescent with a glow, high in the sky
    mx, my = int(w * .74), int(h * .14)
    glow = Image.new('RGBA', img.size, (0, 0, 0, 0))
    ImageDraw.Draw(glow).ellipse([mx - 70, my - 70, mx + 70, my + 70], fill=(255, 240, 200, 70))
    img = Image.alpha_composite(img.convert('RGBA'), glow.filter(ImageFilter.GaussianBlur(28)))
    d = ImageDraw.Draw(img)
    d.ellipse([mx - 24, my - 24, mx + 24, my + 24], fill=(255, 246, 216, 255))
    sky_col = tuple(int(c) for c in np.array(img)[my, mx + 60][:3])
    d.ellipse([mx - 12, my - 30, mx + 34, my + 16], fill=sky_col + (255,))

    for name, arr in (('day', day), ('dawn', dawn), ('dusk', dusk)):
        Image.fromarray(arr.clip(0, 255).astype('uint8')).save(f'{OUT}{season}-{name}.webp', quality=84, method=6)
    img.convert('RGB').save(f'{OUT}{season}-night.webp', quality=84, method=6)

for season, file in files.items():
    make(season, file)
    print('done', season)
