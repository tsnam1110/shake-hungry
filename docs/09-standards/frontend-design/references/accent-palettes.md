# Accent palettes — the three worked examples from the source video

These are the exact palettes shown, with hex labels read from the video frames, in the TikTok video by Hoàng Anh | Website WP (@hoanganh_thrive), https://vt.tiktok.com/ZSpNJfmbA/. Each palette demonstrates one accent strategy from `SKILL.md`, Step 3. Contrast ratios below were measured from the hex values (WCAG relative luminance formula) when this reference was written — recheck if you adjust any value.

How to read each entry: the first two colors are the base pair that already works alone. The third is the accent. The hero example is the website mockup the video recolors live.

---

## Palette 1 — Neon on dark (strategy 1: energy)

| Role | Name used here | Hex |
| --- | --- | --- |
| Base ground | Near black | `#0A0A0A` |
| Base primary | Deep blue | `#007BA7` |
| Accent | Hot pink (the video's narration calls it "neon red" — the on-screen hex is pink; always trust the hex) | `#FF2D95` |

Video example: an AI-themed hero, headline "THẾ HỆ TIẾP THEO" ("The next generation"), a blue-lit robot portrait on black. With the accent, the second headline line and the robot's rim light turn hot pink. The narrator's verdict: the base pair already looks very good together; adding the neon makes the site "extremely energetic".

Measured contrast:

| Pair | Ratio | Verdict |
| --- | --- | --- |
| `#FF2D95` on `#0A0A0A` | 5.71:1 | Passes AA for normal text |
| `#0A0A0A` text on `#FF2D95` | 5.71:1 | Passes AA — preferred CTA text pairing |
| White text on `#FF2D95` | 3.46:1 | Large text only |
| `#007BA7` on `#0A0A0A` | 4.14:1 | Large text / non-text elements; not small body text |
| White text on `#007BA7` | 4.78:1 | Passes AA for normal text |

Ready-to-use tokens:

```css
:root {
  --base-ground: #0A0A0A;
  --base-primary: #007BA7;
  --accent: #FF2D95;
  --text-on-accent: #0A0A0A; /* 5.71:1 */
  --text-on-primary: #FFFFFF; /* 4.78:1 */
}
```

Fits: technology, AI, gaming, music, events, nightlife. Avoid for: trust-first subjects (finance, health, legal) unless the brand is deliberately disruptive — neon saturation reads as excitement, not safety.

Placement notes: accent as light, not surface — glow, headline segment, CTA. Do not fill large panels with `#FF2D95`; at large areas it vibrates against the black ground and the energy turns to noise.

---

## Palette 2 — Complementary pop (strategy 2: impression)

| Role | Name used here | Hex |
| --- | --- | --- |
| Base ground | Platinum (light warm grey) | `#E5E4E2` |
| Base primary | Deep plum | `#3A0F34` |
| Accent | Mustard yellow — the author's own name for it is "vàng cúc" (chrysanthemum yellow) | `#EAAA00` |

Video example: an editorial hero, headline "Chậm là một sức mạnh mới" ("Slow is a new strength"), a flower rendered in muted plum monochrome on the light ground. With the accent, the flower recolors into a fiery yellow-orange gradient and the headline picks up the same warmth. The narrator's verdict: silver/platinum and plum already look "very stable" together; adding yellow makes the site "modern, extremely impressive". Yellow is the complement of purple — that opposition is the mechanism.

Measured contrast:

| Pair | Ratio | Verdict |
| --- | --- | --- |
| `#3A0F34` on `#E5E4E2` | 12.73:1 | Passes AAA — the base text pairing |
| `#EAAA00` on `#3A0F34` | 7.90:1 | Passes AAA — put the accent on the dark member |
| `#3A0F34` text on `#EAAA00` | 7.90:1 | Passes AAA — preferred CTA pairing |
| `#EAAA00` on `#E5E4E2` | 1.61:1 | Fails everything — decoration only, never text or controls on the light ground |
| White text on `#EAAA00` | 2.05:1 | Fails — do not use |

Ready-to-use tokens:

```css
:root {
  --base-ground: #E5E4E2;
  --base-primary: #3A0F34;
  --accent: #EAAA00;
  --text-on-accent: #3A0F34; /* 7.90:1 */
  --text-on-ground: #3A0F34; /* 12.73:1 */
}
```

Fits: editorial, magazines, fashion, culture, food, personal brands, studios. Avoid for: interfaces dense with small controls on the light ground, where the accent cannot be used for state without breaking contrast — in product UI, restrict this accent to the hero and marketing surfaces, or darken it for control states and record the change.

Placement notes: unlike strategy 1, this accent may recolor the hero visual itself, because the image is where the impression is made. Keep UI chrome quiet in the two base colors so the recolored visual stays the one memorable place.

---

## Palette 3 — Iridescent tint (strategy 3: futurity, refinement)

| Role | Name used here | Hex |
| --- | --- | --- |
| Base ground | Coffee brown | `#6F4E37` |
| Base primary | Silver grey | `#DDE3E8` |
| Accent | Pale lavender — the author's own name for it is "tím oải hương" (lavender purple) | `#E6E0F0` |

Video example: a dance-class hero, headline "LỚP HỌC VŨ ĐẠO" ("Dance class"), a liquid chrome shape over a dancer photo on the brown ground. With the accent, the chrome and the headline take on a lavender iridescence. The narrator's verdict: brown and silver already look very good together; adding the lavender makes the site "extremely refined and strongly futuristic".

Measured contrast:

| Pair | Ratio | Verdict |
| --- | --- | --- |
| `#E6E0F0` on `#6F4E37` | 5.77:1 | Passes AA — accent may carry text on the dark ground |
| `#DDE3E8` on `#6F4E37` | 5.75:1 | Passes AA — base text pairing |
| White text on `#6F4E37` | 7.44:1 | Passes AAA |
| `#6F4E37` text on `#E6E0F0` | 5.77:1 | Passes AA if the accent is ever used as a light surface |
| `#E6E0F0` on `#DDE3E8` or other light surfaces | ~1.2:1 (near-identical lightness) | Tint and sheen only, never information |

Ready-to-use tokens:

```css
:root {
  --base-ground: #6F4E37;
  --base-primary: #DDE3E8;
  --accent: #E6E0F0;
  --text-on-ground: #FFFFFF; /* 7.44:1 */
  --text-accent-on-ground: #E6E0F0; /* 5.77:1 */
}
```

Fits: beauty, dance and performing arts, premium services, wellness, boutique hospitality. Avoid for: anything that must read as urgent, cheap, or high-energy — this accent whispers.

Placement notes: the accent works as a material shift (gradient sheen, iridescent highlight, tinted reflection in imagery) more than as flat fills. A flat `#E6E0F0` block on the light primary reads as a rendering error; the same color as a gradient stop over the chrome or photographic element reads as light.

---

## Choosing between the three, in one pass

1. Name the missing emotion (SKILL.md, Step 2).
2. Energy, excitement, night, technology → strategy 1, Palette 1 shape: dark base, saturated neon accent, accent as light.
3. Impression, warmth, a quiet design that needs one unforgettable image → strategy 2, Palette 2 shape: light editorial base, complementary accent, accent inside the visual.
4. Refinement, futurity, premium calm → strategy 3, Palette 3 shape: warm or metallic base, pale tint accent, accent as material.

Then replace every hex with values derived from the actual subject — brand colors, materials, photography — keeping the mechanical relationship (neon-on-dark, complement, tint) that made the example work. Copying these exact hexes onto an unrelated brief is the default-behavior this skill exists to prevent.
