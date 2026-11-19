# FlashPaper UI rework — "Dark Ops, neutral" (variant B "console")

## Expiry (fixed separately)

Before this work, `retrieve_secret()` ignored `prune_epoch`, and expired rows were only deleted when someone loaded the
create page. That is fixed in its own commit ("Enforce secret expiry on retrieval") underneath this change: an expired
secret is deleted and reported with the usual not-found message, and expired rows are also cleaned up whenever a secret
is opened. Both only apply while pruning is enabled. `prune_epoch` stays random within `[now + min_days, now + max_days]`,
so the create page states a range, not a fixed lifetime.

---

Status: direction approved by the owner (2026-11-19, variant B, light mode kept). The owner hands implementation to
a separate session. That session should write the implementation plan from this doc (superpowers:writing-plans) and get the
owner's OK before building. Nothing is to be pushed without the owner's explicit OK. Reference mockup: `mockups/b.html`
(uncommitted, served locally; states via `?s=create|toolong|link|confirm|reveal|gone|error`, plus
`?theme=light`, `?ann=1`, `?http=1`, `?slots=1`, `?legacy=1`, `?host=…`).

## Intent

The fork still looks like upstream (Bootstrap card, teal gradient button, blue paper-plane logo) and feels dated.
The maintainer's other web properties share one "Dark Ops" look. FlashPaper should join that family, but as the
**public fork's default for every instance** (anyone pulling `ghcr.io/bastii717/flashpaper`).
So: Dark Ops tokens and typography, **no personal motifs** (no personal user@host prompt).

The second goal is honest UX: the page must explain the one-time model clearly to recipients, who are often not
technical, without promising more security than the app delivers.

## Goals

- Restyle all states: create (+ "too long"), link, confirm, reveal, not found, error, sanity-check error page.
- Copy that is accurate about the model: encrypted at rest on the server, key inside the link, server decrypts on open,
  works once, unopened secrets expire.
- Confirm step stays (protects against link-preview bots) and explains *why* it exists.
- Copy buttons with visible "copied" feedback for the link and the secret.
- Dark default, light mode kept (system preference, stored choice, toggle, Shift+D).
- Works for any instance without code changes: host and branding come from existing settings.

## Non-goals

- No change to crypto, storage, URL format (`?k=`), or the JSON API (`POST json=1`).
- No client-side (browser) encryption. That would be a different product.
- No per-secret metadata on the confirm page ("1 view left", created/expiry). The confirm page does no lookup and
  must not start doing one; that would let anyone probe whether a link is still valid without consuming it.

## Anti-goals (failure even if it "works")

- Any wording like "end-to-end", "zero-knowledge", "nobody can read it", or "military-grade". The server sees the plaintext
  on submit and on reveal.
- Claiming a fixed lifetime ("stored for 7 days") that the app doesn't enforce.
- External requests (fonts, icons, CDNs). Everything stays self-hosted.
- Breaking existing deployments: every `MESSAGES_*`, `SITE_*`, `DISPLAY_*`, `ANNOUNCEMENT` env var keeps working.
- Red, amber, or blue status colours. The owner uses a strict green-only accent palette; "danger" is done by inversion.

## Constraints (from the code)

- PHP 8.4, server-rendered templates in `html/*.php`, `index.php` routes by `$_GET['k']`, `$_POST['k']`, `$_POST['submit']`.
- CSP (docker/nginx.conf): `default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:`.
  There is no `font-src`, so fonts fall back to `'self'`: the font must be a real file, not a `data:` URI.
- `retrieve_secret()` returns the same exception text for "already read", "pruned", "wrong key" and "malformed link".
  The UI therefore has **one** not-found state and says it can't tell which case happened.
- `confirm.php` renders for any `?k=` without touching the database.
- The prune window is per instance (`PRUNE_MIN_DAYS`/`PRUNE_MAX_DAYS`; short windows such as 7–14 are common, the default is 365–730),
  and expiry is random inside that window.
- Expiry is enforced on retrieval since the separate fix (see "Expiry" at the top); before it, "removed after N–M days" was not strictly true.
- Static files are served by nginx without cache headers; the Docker build passes `FLASHPAPER_COMMIT`.
- `COPY . /var/www/html` in the Dockerfile: anything in the working tree ships in local builds (see `mockups/` below).

## Approaches considered

All three use the same tokens; they differ in how much terminal styling reaches the recipient.

| | Look | Trade-off |
|---|---|---|
| A · shell | every page is a shell session (`flashpaper new`, `[y/N]`, keycap buttons) | most distinctive; confusing for non-technical recipients |
| **B · console (picked)** | the maintainer's site language: prompt line and `// labels` as accents, mono headings, normal buttons, plain sentences | closest to the owner's other sites; readable for anyone |
| C · lifecycle | calm single column with a write→share→open→gone strip | clearest for recipients; least "Dark Ops" |

The owner picked B because it fits the family and stays readable for anyone receiving a link.

## Design (as decided)

### Tokens and type

- Dark (default): bg `#0b0f14`, surface `#0f141c`, border `#1d2733`, border-soft `#161e28`, text `#e8edf2`,
  muted `#8fa0b3`, dim `#717f93`, accent `#56d364`, accent-dim `#2ea043`, on-accent `#06250e`.
- Light: bg `#f6f8fa`, surface `#ffffff`, border `#d0d7de`, border-soft `#e3e8ed`, text `#0f1720`, muted `#4a5768`,
  dim `#5f6c7b`, accent and accent-dim both `#1a7f37` (`#2da44e` rejected at 3.0:1), on-accent `#ffffff`.
  All text tokens are ≥4.5:1 on bg and surface.
- Helper tokens: `--nav-bg`, `--accent-tint` (announcement band), `--accent-glow` (focus ring), each with a light value.
- Fonts: Fira Code variable woff2, self-hosted (`fonts/fira-code-var.woff2`, plus the OFL licence file), for mono.
  System sans for body text.

### Layout (mockup B)

- **Nav:** `flashpaper▍` wordmark (accent mono, blinking cursor; static under `prefers-reduced-motion`), right side
  "how it works" (create page anchor, hidden on phones), "new secret", theme pill `☾ dark` / `☀ light`.
- **Announcement** (if `ANNOUNCEMENT` is set): full-width band under the nav on every page, green tint, accent
  border top and bottom, solid green `NOTICE` chip, 15px sans. The value stays raw HTML as today, so operators can include links.
- **HTTP warning** (JS, `location.protocol != 'https:'`): full-width inverted band (text colour as background), bold mono.
- **Sender pages** (create, link) start with a prompt line `<host>:~$ flashpaper new`. The host is the host part of
  `BASE_URL`, falling back to `HTTP_HOST`, escaped. No new setting.
- **Create:** mono h1, sub line, a card (`// secret` header with live `n/max` counter, mono textarea, footer with
  primary button, the "too long" message slot, and the template select). Below it: three fact tiles
  (`// works once`, `// key in the link`, `// expires`) and a one-line trust note ("This server decrypts the secret
  when it's opened. Use an instance you trust.").
- **Link:** h1 plus sub, then the URL in a read-only mono field joined to a "Copy link" button, then a `// before you send it`
  list (don't open it yourself; send context through another channel; "not found" means someone else may have opened it),
  then a "New secret" button.
- **Recipient pages** (confirm, reveal, not found, error) use a narrow centred column with a `// label` or status pill,
  plain sentences, and no prompt line.
  - Confirm: "Open secret?" / "If this link is still valid, it shows a secret exactly once…", primary "Reveal secret",
    then `// why the extra click?` (link previews don't click buttons; you can close the page, nothing happens until you click).
  - Reveal: pill `● deleted from server`, "Your secret", the secret in a mono block, "Copy secret" + "Clear screen",
    and a hint to store it in a password manager.
  - Not found: pill `○ not found`, "Nothing here (anymore)", the explanation that it can't tell which case happened, and
    `// didn't open it yourself?` (ask for a new link, warn the sender, change the password; check the link wasn't cut off,
    40 characters after `?k=`).
  - Error: `// error`, "Something broke", the exception message in mono, and an honest line on both cases
    (create: nothing stored; open: it may already be deleted).
- **Footer:** centred mono: `flashpaper · source on GitHub · <short commit>` (commit link as today).
- **Focus:** text fields have no own outline. Their frame (`.card`, `.field`) shows an accent border plus a 3px
  `--accent-glow` ring while the field is focused (`:has(textarea:focus)`). Buttons and links keep the 2px accent
  `:focus-visible` outline.
- Responsive: no horizontal scroll at 375px. Fact tiles stack, the URL field stacks above its button, and the announcement chip moves above its text.

### Copy: what is configurable

The existing `MESSAGES_*` slots keep their keys; only the **defaults** change (in `settings.example.php` and
`docker/settings.php`):

| key | new default |
|---|---|
| submit_secret_header | New one-time secret |
| submit_secret_subheader | Paste it, get a link. The link works once, then the secret is deleted. |
| submit_secret_button | Create link |
| view_code_header | Link ready |
| view_code_subheader | Send it to one person. The first time it's opened, the secret is deleted from the server. |
| confirm_view_secret_header | Open secret? |
| confirm_view_secret_button | Reveal secret |
| view_secret_header | Your secret |
| view_secret_subheader | Deleted from the server. This page is the only copy left. |
| error_secret_too_long | Too long: max {max} characters. *(`{max}` is replaced with `max_secret_length` when present; env values without it work unchanged)* |

Everything else (fact tiles, before-you-send list, why-the-extra-click, not-found help, trust note) is new fixed copy
in the templates. The expiry tile uses the instance's `prune.min_days`–`prune.max_days`, and is hidden when pruning is disabled.
Instances that set the old strings via env keep them; the mockup's "your current strings"
toggle shows that the layout holds up with them.

### Theme behaviour

Replaces `js/color-toggle.js` (and drops Bootstrap's `data-bs-theme`):
stored choice (`localStorage`) wins, otherwise `prefers-color-scheme`. The pill toggles and stores. Shift+D toggles too,
**except while focus is in a textarea, input, or select**. Today, typing a capital "D" into the secret flips the theme.
The theme is applied before first paint to avoid a flash, with an inline `<script>` in `<head>` (CSP allows inline).

### Logo

- `DISPLAY_LOGO=true` with the default `site_logo` (`img/logo.svg`): render the text wordmark instead of the upstream
  blue logo.
- A custom `SITE_LOGO` (any other value) renders as an image in the nav, about 28–30px high, replacing the wordmark.
- `DISPLAY_TITLE=true` keeps appending `site_title` next to it.
- Note: `SITE_LOGO=img/logo.png` (an old upstream value) doesn't exist in this repo (upstream replaced it with
  `logo.svg`), so it renders as a broken image. Removing the env var gives the wordmark.

## Calls made while writing this up (owner can overrule)

- **"Too long" renders inline.** When the server rejects the length, `index.php` re-renders the create page with the message
  in the card footer, and the textarea is refilled with the submitted text. This is the sender's own POST echoed back in
  their own response; nothing is stored. Today the user lands on the error page and loses the text.
- **The counter counts bytes, not characters.** The server compares `strlen()` (bytes, no mbstring in the image) against
  `max_secret_length`. The JS counter therefore counts UTF-8 bytes (`TextEncoder`), so it matches the server.
  `maxlength` (which counts UTF-16 units) is dropped, and the JS blocks submit when over the limit. Server logic and the JSON API stay unchanged.
  The counter shows `n/max` without a unit. For ASCII, bytes and characters are the same.
- **`DISPLAY_LOGO=false` hides the wordmark too.** With `DISPLAY_TITLE=true`, only `site_title` shows. With both false,
  the nav keeps only its links. This matches what both settings do today.

## Left to the builder

- Whether to keep Bootstrap at all. The mockup needs none of it, and dropping `css/bootstrap.min.css` is preferred if
  nothing else uses it (the sanity-check page uses `alert` classes and needs restyling anyway).
- Stylesheet location (`css/flashpaper.css` suggested) and cache-busting via `?v=<short commit>` (falling back to a constant).
- Moving the inline `onclick` and `onUnload` handlers and `copyText()` into a small `js/flashpaper.js` (copy feedback,
  counter, clear screen, theme). The page still works without JS for create/confirm/reveal; copy needs JS, as today.
- Exact markup and CSS class names; the mockup's CSS is a reference, not a drop-in.

## Related changes (separate, not part of this UI work)

1. **Expiry bug:** done first, in its own commit (see "Expiry" at the top).
2. **README wording:** replace "zero-knowledge" in the intro with an accurate description.
3. **`mockups/`** must not be committed, or must be added to `.dockerignore` before any local build, because `COPY .` would ship it.
4. **README screenshot** (`readme-screenshot-dark.png`) needs replacing after the rework.

---

## Handover: everything observed (for the implementing session)

Collected while exploring the code, building the mockups and running a builder review. Each point is checked against the
code at `a56723a` unless it says otherwise.

### Process and preferences

- All UI copy is **English only**. Simple and clean but geeky; humour is fine if it stays honest. **No external requests**
  (fonts and assets self-hosted). Green-only accent; danger = inversion.
- No horizontal scroll at 375px.
- Nothing is pushed without the maintainer's explicit OK; work happens on feature branches off `master`.
- The repo is a **public fork** of AndrewPaglusch/FlashPaper. Keep upstream credit (header comment, README
  acknowledgements). Visible branding stays neutral: no personal prompt, no personal domains in templates.
- The mockups (`mockups/`, not committed) were the visual reference; `b.html` is the chosen variant.

### Deployment facts

- Env values override the new defaults. Instances that set `MESSAGES_*` to the old upstream strings keep them until those
  env lines are removed. An old `SITE_LOGO=img/logo.png` renders as a broken image; removing it gives the wordmark.
- CI (`.github/workflows/docker_publish.yml`) builds multi-arch on master pushes, semver tags and a weekly cron, signs
  with cosign, and passes `FLASHPAPER_COMMIT=${{ github.sha }}`. That commit is usable as a cache-busting `?v=` for CSS/JS.
- `Dockerfile` does `COPY . /var/www/html`, so `.dockerignore` must exclude `mockups/`, `docs/` and `tmp/` (done in this change).

### Code facts the templates must respect

- **Routing** (`index.php`): `GET ?k` → confirm; `POST k` → reveal; `POST submit+secret` → link; `POST json+secret` →
  JSON `{"url": …}` (README documents `curl -d "secret=…&json=true"`; it must keep working). Otherwise → create (+ prune).
  Each branch catches exceptions into `display_error()` → `html/error.php`.
- **Telling not-found from real errors:** `retrieve_secret()` throws exactly `This secret can not be found!` for
  not-found, already-read, wrong-key and malformed-length cases. Map that message (or add a dedicated exception class)
  to the "Nothing here (anymore)" page, and everything else to the generic error page. Don't leak which case happened.
- **Delete before decrypt:** `retrieve_secret()` deletes the row *before* decrypting. If decryption fails, the secret
  is gone anyway. That's why the error page says "if you were opening one, it may already be deleted". Keep that line.
- **Concurrency:** `deleteSecret()` returns true only for the request that deleted the row. Two simultaneous reveals
  → one gets the secret, the other gets not-found. The UI copy ("someone else may have opened it first") relies on this.
- **Confirm page** echoes `htmlspecialchars($_GET['k'])` into a hidden field and does no DB lookup. Keep it that way;
  a lookup would turn GET into a validity oracle.
- **Reveal page:** `$message = htmlentities($secret)` is printed raw into the template. If you move the secret from a
  `<textarea>` to `<pre>`, keep it escaped exactly once, and keep `white-space: pre-wrap` and `word-break: break-all`.
- **Length check:** `strlen(str_replace("\r\n", "\n", $_POST['secret'])) > max_secret_length` counts **bytes**, and no
  mbstring is installed. The browser's `maxlength` counts UTF-16 units, so an umlaut or emoji secret can pass the
  browser limit and fail on the server. Decision (see "Calls made"): JS counts UTF-8 bytes; server and API stay unchanged.
  Also normalise `\r\n` → `\n` in the JS count.
- **"Too long" today** throws into `error.php` and the typed text is lost. Decision: re-render the create page inline with
  the text refilled (escape with `htmlspecialchars(…, ENT_QUOTES)` as the template path already does).
- **Templates feature:** `?t=<name>` loads `templates/<name>.txt` (`basename()`-guarded) into the textarea; the select
  navigates via `onChange`. Keep both the feature and the guard. The select can move into the card footer as in the mockup.
- **`<body onUnload>`** clears `#secret`, so the browser's back/forward cache doesn't refill the form. Keep the behaviour
  (move it to the JS file, `pagehide` is the modern event).
- **HTTPS warning** is injected by an inline script in `header.php` when `location.protocol != 'https:'`. Keep it
  (it's restyled as the inverted band).
- **Announcement** is echoed **raw** (operators can put HTML in it). Keep it raw (it's operator-controlled), but place it
  in the new band.
- **Sanity-check page** (`includes/sanitycheck.php`) includes `html/header.php`/`footer.php` **before `settings.php` is loaded**,
  so `$settings` is undefined there. The current header then throws PHP warnings. The new header/footer must work with
  `$settings` unset (null-coalesce everything), and the sanity page (Bootstrap `alert` classes today) needs restyling into the error look.
- **Footer + entrypoint coupling:** `docker/entrypoint.sh` treats the container as ready when the page contains
  `Bastii717/FlashPaper`, i.e. the GitHub link in the footer. Keep that URL in the footer, or update the entrypoint in the same change.
- **Footer commit link** (`$settings['commit']`, `/^[0-9a-f]{7,40}$/`) stays. The new footer is centred mono:
  `flashpaper · source on GitHub · <short sha>`.
- **Settings types:** `docker/settings.php` turns `DISPLAY_LOGO`/`DISPLAY_TITLE` into **booleans** (`filter_var`), while
  `settings.example.php` uses the **strings** `'true'`/`'false'`. The templates compare with `== 'true'`, which works for both in
  PHP 8 by accident. Use one robust check (`filter_var($v, FILTER_VALIDATE_BOOLEAN)`) in the new header.
- **Host for the prompt line:** `parse_url($settings['base_url'], PHP_URL_HOST)`, falling back to `$_SERVER['HTTP_HOST']`,
  and always `htmlspecialchars`. `HTTP_HOST` is client-controlled; escaping is enough because it's only displayed.
- **CSP** allows inline script and style (`'unsafe-inline'`) and has no `font-src` (falls back to `'self'`). A real woff2
  under `fonts/` works; `data:` fonts would be blocked. No CSP change is needed. Tightening it (moving inline handlers out)
  is welcome but optional.
- **Theme storage:** upstream `js/color-toggle.js` stores `localStorage.themePreference = 'true'|'false'` (true = dark).
  Read the old key once and migrate it, so returning users keep their choice. Also note its bug: Shift+D toggles
  while typing a capital D into the secret. The new JS must ignore Shift+D when focus is in textarea/input/select.
  Apply the theme in an inline `<head>` script to avoid a flash.
- **Bootstrap** (`css/bootstrap.min.css`) is used only for layout helpers, `.form-*`, `.btn`, `.alert` and the switch.
  The mockup needs none of it. Dropping it removes ~230 KB; check `grep -rn "class=" html includes` for leftovers.
- **Font file:** Fira Code variable woff2 in `fonts/`, with Fira Code's OFL licence text from the upstream Fira Code
  repository next to it. `.dockerignore` must not exclude `fonts/`.

### UX and copy rules (why the copy reads the way it does)

- No "end-to-end", "zero-knowledge" or "unreadable even by us". The server encrypts (AES-256-CBC twice: per-secret key
  from the URL plus a static key in `data/`) and decrypts on reveal, and the key travels in the URL query (`?k=`), which
  reverse proxies may log. The README intro still says "zero-knowledge"; fixing it is a separate small change.
- The confirm page says "**if this link is still valid**", because it really doesn't know.
- There is one not-found page because the app can't distinguish the cases. It tells the recipient what to do:
  ask for a new link, warn the sender, change the password, and check for a truncated link (40 chars after `?k=`).
- The "why the extra click?" text explains the bot protection. Previews do GET; revealing needs POST.
- "Clear screen" only hides the secret in the DOM. The copy doesn't claim more ("Reloading won't bring it back" is true
  because the row is already deleted).
- The fact tile says "Stored encrypted (AES-256)", which is true at rest. It doesn't say "encrypted end-to-end".
- Status colours: none. The pills use accent (done) vs. muted outline (not found). The http band and the too-long chip use inverted text/bg.

### Builder-review answers (so they're not re-asked)

- `{max}` in `error_secret_too_long` → substitute `max_secret_length`. Env strings without `{max}` work unchanged.
- `DISPLAY_LOGO=false` hides the wordmark too. With both display flags false, the nav shows only its links.
- "Too long" → inline re-render with refilled text; the counter counts bytes (see above).
- Expiry → fixed first, in its own commit.
