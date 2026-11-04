---
name: frontend-design-accent
description: Guidance for distinctive, intentional visual design when building new UI or reshaping an existing one, with a disciplined single-accent color system. Build the design on a two-color base that already works, then add exactly one accent color chosen for the one emotion the base lacks. Helps with aesthetic direction, typography, accent color selection, and making choices that don't read as templated defaults. Use when designing or redesigning a website, landing page, admin UI, or product interface, when choosing a color palette, or when a design "looks fine but is not impressive yet". Dùng khi thiết kế giao diện mới, chọn bảng màu, hoặc khi giao diện đã đẹp nhưng chưa ấn tượng và cần một màu nhấn.
license: Derivative work. The frontend-design foundation is from Anthropic's skills repository (Apache License 2.0, see LICENSE.txt in the source repo). The single-accent system is original material distilled from a design video by Hoàng Anh | Website WP (@hoanganh_thrive on TikTok). See the "Sources and credit" section at the end.
---

# Frontend Design — Single Accent Edition

Approach this as the design lead at a design studio known for giving every client a distinct visual identity that is not mistaken for anyone else's. This client has already rejected proposals that felt cliché or templated, and is paying for a distinctive point of view: make deliberate, opinionated choices about palette, typography, and layout that are specific to this brief, and take aesthetic risk if justified.

This edition keeps that foundation and adds one discipline on top of it: **color is decided as a system of "2 + 1" — a two-color base that already works on its own, plus exactly one accent color whose only job is to supply the one emotion the base is missing.** A beautiful base is not the finish line. One accent, placed with restraint, is what makes a design memorable.

## Ground your designs in the subject matter

If the brief does not identify what the product or subject matter is, identify it yourself before designing, and confirm with the client. You can come up with one concrete subject, the design's audience, and the design's primary job, as a proposal. If there's any information in your memory about the client's preferences or context about what they're building, use that as a hint. The subject's industry, subject matter, materials, and vernacular are where distinctive visual choices come from — a design for a toy for girls aged 8–11 will be very aesthetically different from a dashboard for financial analysts. Build with the brief's real content and subject matter throughout.

The accent color is grounded the same way. It is not picked from a trending palette; it is derived from the subject's world and from the feeling the base palette cannot express by itself (see "The single-accent system").

## Design principles

For web designs, the hero is the first thing viewers will see. Open with the most characteristic thing in the subject's world, in the form that is most appropriate: a headline, an image, an animation, a live demo, an interactive moment, or other treatments. Be deliberate with your choice: a big number with a small label, supporting stats, and a gradient accent is the default treatment, so only use it if that's truly the best option.

Typography carries the personality of the page. You don't need a different typeface for display or headline text and body content: use one family or two, and if two, make them clearly distinct.

Choose your typefaces deliberately, not the default families you would reach for on any other project, and set a clear type scale following the default guidance of The Elements of Typographic Style with intentional weights, widths, and spacing. When type is used as a headline or visual element, use the type treatment itself as an active part of the design, not a neutral delivery vehicle for the content.

Default to line lengths of less than 80 characters. Serif typefaces can have slightly longer line lengths; give serif body text slightly more line-height than a sans-serif.

Avoid these default typographic treatments; they are the commonest tells of a generated page:

- Accenting just a single word or phrase in a headline, like putting one word in italic/bold or a different color — with one deliberate exception, governed by the accent rules below.
- Using all caps for labels.
- Adding unnecessary typographic labels above content.

Visual structure is information. Structural devices like outlines, borders, numbering, eyebrows, dividers, labels, etc., encode useful information about the content rather than decorate it. Many generic designs use numbered markers (01 / 02 / 03), but that's only appropriate if the content actually is a sequence — like a stepped process or a timeline. Before adding numbered markers, check the content really is a sequence.

Use non-user-triggered motion sparingly and deliberately, only to draw attention. A single orchestrated moment — one page-load sequence or one reveal — lands better than scattered effects; fade-and-slide-up entrances on each section and hover transitions on every card are the generic default and read as AI-generated. Motion that answers a person's action (opening, expanding, confirming) is welcome when it shows what changed.

Consider written content carefully. Often a design brief may not contain real content, and it's up to you to come up with copy and placeholder content. Copy can make a design feel as templated as the design itself. See the section on writing below for more guidance.

## The single-accent system

This is the core addition of this edition, distilled from a short design video that demonstrated the same layout three times: each time the base pair already looked good, and adding one third color — without touching layout, type, or imagery — changed the entire emotional register of the page. The lesson generalizes into the following procedure. Follow it in order; do not start by choosing the accent.

### Step 1 — Build a two-color base that already works

Choose exactly two base colors first: typically a ground (background or dominant surface) and a primary (text, headline, main visual tone). The pair must already harmonize — by tone, by temperature, or by material (for example, coffee brown with silver grey reads as one material world).

Test the base before moving on: render the hero with only these two colors. If the design already looks coherent and intentional, the base passes. If it only looks "not broken", fix the base — no accent rescues a weak base, it only distracts from it.

### Step 2 — Name the one missing emotion, in one word

Ask: what should the viewer feel that the base does not produce? Write it as a single word before looking at any color: *energy*, *warmth*, *impression*, *calm*, *luxury*, *futurity*, *trust*. If you cannot name it, you are decorating, not designing — go back to the brief and the subject matter.

One design gets one missing emotion. If the brief seems to need two, decide which one the page's primary job depends on, and let typography or imagery carry the other.

### Step 3 — Choose the accent by strategy, not by taste

Pick the accent using one of the three strategies below. Each strategy is a different mechanical relationship between accent and base, and each produces a different class of result. The three reference palettes in `references/accent-palettes.md` are worked examples of the three strategies — use them as starting points and as calibration, not as defaults to copy onto every brief.

1. **Neon on dark (energy).** On a dark, already-modern base, add one fully saturated neon accent. It works as light, not as surface: glow, headline segment, the lit edge of the hero visual. Example: black `#0A0A0A` + deep blue `#007BA7`, accent hot pink `#FF2D95`. Fits technology, AI, gaming, music, nightlife — anywhere "more energy" is the missing word.
2. **Complementary pop (impression).** On a quiet, editorial base, add the accent from the opposite side of the color wheel from the primary base color. The contrast does the work, so the accent can cover more of the hero visual itself (recolor the image or illustration, not only small UI details). Example: platinum `#E5E4E2` + deep plum `#3A0F34`, accent mustard yellow `#EAAA00` — yellow is purple's complement, and the muted floral hero turns fiery and modern. Fits editorial, fashion, culture, food, personal brands.
3. **Iridescent tint (futurity, refinement).** On a warm or metallic neutral base, add one pale tinted off-white as the accent. The effect is a shift of material rather than a pop of color: silver chrome becomes lavender glass, flat surfaces gain a colored sheen. Example: coffee brown `#6F4E37` + silver grey `#DDE3E8`, accent pale lavender `#E6E0F0`. Fits beauty, dance and arts, premium services, anything where the missing word is "refined" or "futuristic" but saturation would cheapen it.

If none of the three strategies fits the named emotion, derive the accent from the same logic anyway: state its mechanical relationship to the base (complement, neon-on-dark, tint-of-material, direct contrast) in the design plan. An accent whose relationship to the base cannot be stated is a guess.

### Step 4 — Place the accent under a strict budget

The accent earns its power from scarcity. Hold it to roughly **10% or less of the visible area** of any screen, and to a short, written list of allowed placements, typically:

- The primary call to action (one per screen).
- One segment of the hero headline, or the hero visual's focal element — see the exception rule below.
- The active or selected state in navigation and controls.
- One highlight system in data or pricing (the recommended plan, the key figure), never every highlight.

Forbidden placements: body text, large background fills, borders and dividers everywhere, every icon, every link. If a screen seems to need the accent in a fifth place, one of the existing four is not doing its job.

In product UI (dashboards, admin panels, apps), the same budget applies with different placements: primary action, active state, and the one status or figure the screen exists to show. Semantic colors (error red, success green) are a separate system and do not count as the accent — but never let the accent sit close enough to a semantic color to be confused with it.

### The headline exception — when accenting one word is allowed

The foundation above warns that coloring a single word in a headline is a common tell of a generated page. The video this system is drawn from does exactly that — and it works there. The difference is systematic, not stylistic:

- Accenting one word is a **tell** when it is the only place the accent exists, chosen because the headline "needed something".
- Accenting one word is a **system** when the same accent simultaneously appears in the hero visual and the primary CTA, the recolored segment is the semantically operative part of the headline (the claim, not a random adjective), and removing the accent everywhere would return the page to a base that still works.

If all three conditions hold, accent the headline segment. If any fails, set the whole headline in the primary base color.

### Specify hex, never color names alone

Color names drift; hex values do not. The source video itself calls `#FF2D95` "neon red" while the swatch on screen is plainly hot pink — anyone who reproduced the palette from the spoken name would ship the wrong color. Therefore:

- Every palette decision in the plan, the tokens, and the code is written as a named hex value. A color name may accompany the hex, never replace it.
- When a brief, a client, or a reference gives you a color by name, resolve it to a hex and show the client the swatch before building.
- When extracting a palette from a screenshot, video, or reference image, read the hex from the source's own labels or sample the pixels — do not transcribe the narrator's or caption's color names.

### Check contrast before the accent ships

An accent that cannot be read is decoration at the user's expense. Check the pairs the design actually uses against WCAG contrast (4.5:1 for normal text, 3:1 for large text and for essential non-text elements), and record the ratios in the design plan. Known traps from the reference palettes, measured:

- `#EAAA00` on the light base `#E5E4E2` is only **1.61:1** — unusable for text or controls on that ground. The same yellow on its dark pair `#3A0F34` is **7.90:1**. Rule: a complementary accent is placed on the *dark* member of the base pair, or used as pure decoration and recoloring of imagery, never as text on the light member.
- `#FF2D95` on black `#0A0A0A` is **5.71:1** — fine for headline and CTA backgrounds with dark text (black on the pink is also 5.71:1); white text on the pink is only **3.46:1**, large text only.
- A pale accent like `#E6E0F0` works as text only on the dark member of its pair (on `#6F4E37`: **5.77:1**); on light surfaces it is a tint and a sheen, never information.

Full measured ratios for all three reference palettes are in `references/accent-palettes.md`.

### Step 5 — Run the before/after test

Before calling the color work done, capture the hero (and the primary screen, for product UI) twice: base only, and base plus accent, with layout, type, and content identical.

- If the accent version does not change the perceived emotion in the one word named in Step 2, the accent is wrong or misplaced — change the accent, not the layout.
- If the base-only version looks broken rather than merely plainer, the base failed Step 1 — go back.
- If the accent version looks louder but not more specific to this subject, the accent is generic — re-derive it from the subject matter.

Keep the base-only screenshot. It is the control that proves the accent is doing design work rather than hiding weak structure.

## Process: plan, review against the brief, build, critique

For calibration, AI-generated design right now clusters around some traits:

1. a warm cream background (near #F4F1EA) with a high-contrast serif display and a terracotta or warm-clay accent (often near #D97757 — Anthropic's own Claude-interaction accent, so on a user's brief it reads as a tell);
2. a near-black background with a single bright acid-green or vermilion accent;
3. a broadsheet-style layout with hairline rules, zero border-radius, and dense newspaper-like columns;
4. the SaaS-card kit: content chopped into identical rounded cards, one border-radius on everything regardless of hierarchy, the same soft grey shadow (rgba(0,0,0,.1)) under each, and gradient washes as decoration;
5. template chrome that appears whatever the subject: a tracked-out ALL-CAPS eyebrow label above every heading; meta strings joined with middle dots ('A · B · C'); labels built as 'WORD — fragment' with a spaced em dash; tinted near-black (#0B0B0B, #111) standing in for black; a monospace face for small data labels; a '→' appended to link and button text.

All traits are legitimate for some briefs, but they are defaults rather than choices, and they appear regardless of subject. Where the brief pins down a visual direction, follow it exactly — the brief's own words always win, including when it asks for one of these looks. Where it leaves an axis free, don't spend that freedom on one of these defaults.

Note the interaction with the accent system: trait 2 above is strategy 1 (neon on dark) used as a reflex. The strategy is strong; using it without having named the missing emotion in Step 2 is what makes it a default. The procedure, not the palette, is the difference.

Work in two passes. First, brainstorm a short design plan based on the client's design brief: create a compact token system with color, type, layout, and principles.

- Color: describe the core base palette as 4–6 named hex values, **then add the accent block: the two base colors this design actually builds on, the missing emotion in one word, the accent strategy (Step 3), the accent hex, its allowed placements, its forbidden placements, and the measured contrast ratios for the pairs it will appear in.**
- Type: the typefaces and their roles.
- Layout: a layout concept, using one-sentence prose descriptions and ASCII wireframes to ideate and compare. Include alignment guidance; should the content be left aligned, center aligned, justified?
- Principles: the high-level guidance for what makes this page unique.

Then review that plan against the brief before building: if any part of it reads like the generic default you would produce for any similar page (work through a similar prompt to see if you arrive somewhere similar) rather than a choice made for this specific brief — revise that part, say what you changed and why. Only after you've confirmed the relative uniqueness of your design plan should you start to write the code, following the revised plan.

When writing the code, define the palette as tokens (`--base-ground`, `--base-primary`, `--accent`, plus the ordinary text/surface tokens) and reference the tokens everywhere. A raw hex value appearing in component CSS outside the token block is a defect: it is how second accents sneak in. Be careful of structuring your CSS selector specificities. It's easy to generate CSS classes that cancel each other out (especially with a type-based selector like .section and an element-based selector like .cta). This can happen often with padding/margin between sections.

## Restraint and self-critique

Spend your boldness in one place. Let one element be the memorable thing, keep everything around it quiet and disciplined, and cut any decoration that does not serve the brief. In this edition, that one place is usually where the accent lives — which is another reason the accent gets a budget: if the accent is everywhere, the memorable place is nowhere.

Build to a quality floor without announcing it: responsive down to mobile, visible keyboard focus, reduced motion respected, visually accessible, harmonious color palettes. Critique your own work as you build, taking screenshots to review if your environment supports it — a picture is worth 1000 tokens. Run this edition's specific checks before finishing:

- Count the accents in the built design. If there are two accent colors, remove one — merge its jobs into the surviving accent or demote it to a base tone.
- Estimate accent coverage on the primary screen. If it is clearly over about 10% of the visible area, cut placements until it is not.
- Compare against the base-only screenshot from Step 5 and say, in one word, what the accent changed. If you cannot, the color work is not done.
- Check every text-on-accent and accent-on-ground pair in the built design against the ratios recorded in the plan.

Consider Chanel's advice: before leaving the house, take a look in the mirror and remove one accessory. Human creatives have memory and always try to do something new, so if you have a space to quickly jot down notes about what you've tried — including which accent strategies and palettes you have already used for this client — it can help you in future passes, and it stops you handing the same accent to every brief.

## More on writing in design

Words appear in a design for one reason: to make it easier to understand and use. They are design content, not decoration. Bring the same intentionality and minimalism to copywriting that you would bring to spacing and color. Before writing anything, ask what the design needs to say, and how it can best be said to help the person navigate the experience.

Write from the end user's perspective. Name things by what users will understand in simple language, not by how the system is built. A user manages notifications, not webhook config. Describe what something is or does in plain terms rather than selling it. Being specific and legible to new users is always better than being clever.

Use active voice as default. A CTA says exactly what happens when it is used: "Save changes," not "Submit." An action keeps the same name through the whole flow, so the button that says "Publish" produces a toast that says "Published." The vocabulary of an interface is the signposting for someone navigating a product. Cohesion and consistency are how people learn their way around.

Treat failure and emptiness as moments for direction, not mood. Explain what went wrong and how to fix it, in the interface's voice rather than a person's. Errors don't apologize, and they are never vague about what happened. An empty screen is an invitation to act.

Keep the tone conversational: plain verbs, sentence case, no filler, with tone matched to the brand and the audience. Let each written element do exactly one job.

## Sources and credit

- Foundation: the `frontend-design` skill from Anthropic's skills repository, https://github.com/anthropics/skills/tree/main/skills/frontend-design (Apache License 2.0). Sections "Ground your designs in the subject matter", "Design principles", "Process", "Restraint and self-critique", and "More on writing in design" are adapted from it, with additions where the accent system interacts with the original rules.
- Single-accent system: distilled from the TikTok video "POV: Giao diện Website của bạn trông đẹp rồi, nhưng chỉ cần một màu nhấn cũng đủ khiến nó trở nên ấn tượng" by Hoàng Anh | Website WP (@hoanganh_thrive), https://vt.tiktok.com/ZSpNJfmbA/ (video ID 7703814924346068231, 18 seconds). The three palettes, the three hero examples, and the "already beautiful, one accent makes it impressive" premise are the video's; the five-step procedure, the three named strategies, the placement budget, the headline exception rule, the hex-over-names rule, and all contrast measurements are this skill's elaboration, added so the video's intuition can be applied and checked on real work.
- The palettes, CSS tokens, and measured contrast ratios for the video's three examples live in `references/accent-palettes.md`.
