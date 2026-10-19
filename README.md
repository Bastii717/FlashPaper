# FlashPaper
A one-time encrypted zero-knowledge password/secret sharing application focused on simplicity and security. No database or complicated set-up required.

[![Docker](https://github.com/Bastii717/FlashPaper/actions/workflows/docker_publish.yml/badge.svg)](https://github.com/Bastii717/FlashPaper/actions/workflows/docker_publish.yml)

> **About this fork:** This is an actively maintained fork of [AndrewPaglusch/FlashPaper](https://github.com/AndrewPaglusch/FlashPaper). All credit for the concept, design, and original implementation goes to [Andrew Paglusch](https://github.com/AndrewPaglusch) — this fork exists to keep the project patched and its base images current, not to replace it. See [Acknowledgments](#acknowledgments).

## Demo

- https://secret.bcdc.app
- https://secret.pwneytelecom.com

![Picture of Main Page](readme-screenshot-dark.png)

## Installation

### Docker *(Recommended)*
  Images for this fork are published at [`ghcr.io/bastii717/flashpaper`](https://ghcr.io/bastii717/flashpaper).
  1. Download [docker-compose.yml](https://raw.githubusercontent.com/Bastii717/FlashPaper/master/docker-compose.yml) from this repo
  2. Edit `docker-compose.yml` with your customizations
  3. Run `docker compose up -d` to start FlashPaper
  4. Set up a reverse proxy in front of FlashPaper that terminates SSL/TLS (see [Operating behind a reverse proxy](#operating-behind-a-reverse-proxy))

#### Building an Image
  You can build your own image using the provided Dockerfile in the `docker/` folder.

  To build FlashPaper, run `docker build . -t flashpaper -f docker/Dockerfile`.
  You can also build via docker compose by replacing the `image:` line in [docker-compose.yml](https://github.com/Bastii717/FlashPaper/blob/master/docker-compose.yml) with the following:

  ```
  build:
      context: .
      dockerfile: docker/Dockerfile
  ```

### Traditional
  **Requirements:** PHP 8.0+ and a web server
  1. Download and extract the [latest release](https://github.com/Bastii717/FlashPaper/releases/latest) of FlashPaper to the document root of your web server
  2. Copy `settings.example.php` to `settings.php` and make customizations to that file
  3. Make sure your web server never serves the `data/` directory — it holds the sqlite database and the static encryption key. An `.htaccess` file is included for Apache 2.4+; on nginx, deny the location explicitly
  4. Disable access logging in your web server's configuration so nothing sensitive (secret URLs, IP addresses, user agent strings, timestamps, etc) is logged to disk

## Operating behind a reverse proxy

FlashPaper is designed to run behind a reverse proxy that terminates TLS. A few things are worth configuring there:

- **TLS is mandatory.** Secret URLs contain the decryption key; never serve FlashPaper over plain HTTP.
- **Keep query strings out of your proxy's access logs.** Retrieval URLs carry the secret key in `?k=`. The bundled nginx inside the container does not log requests, but a default proxy log format (`$request`) will. Log `$uri` instead of `$request`, or disable access logging for this vhost.
- **Rate limiting** is best enforced at the proxy. Example for nginx:
  ```
  limit_req_zone $binary_remote_addr zone=flashpaper:10m rate=10r/m;

  location / {
      limit_req zone=flashpaper burst=5;
      proxy_pass http://127.0.0.1:8080;
  }
  ```
- **Security headers** (CSP, `X-Frame-Options`, `Referrer-Policy`, `X-Content-Type-Options`) are already sent by the container and can be overridden at the proxy if you need different values.
- **Set `BASE_URL`** so generated retrieval URLs don't depend on client-supplied `Host`/`X-Forwarded-Proto` headers.

## How It Works
### Submitting Secret
  1. `<random>--secrets.sqlite` sqlite database created (if it doesn't already exist)
  2. `<random>--aes-static.key` randomized 256-bit AES static key created (if one doesn't exist already)
  3. Random 256-bit AES key created
  4. Random 128-bit IV created
  5. Random 64-bit ID created
  6. ID + AES key hashed with bcrypt
  7. Submitted text encrypted with AES-256-CBC using AES key and random IV
  8. Ciphertext now encrypted with AES-256-CBC using static AES key and random IV
  9. ID and AES key joined (known as `k`)
  10. Random prune date/time generated using `prune`->`min_days`/`max_days`
  11. ID, IV, bcrypt hash, ciphertext, and prune epoch stored in DB
  12. `k` value returned to user in one-time URL

### Retrieving Secret
  1. `k` value removed from URL
  2. `k` value split into two parts: ID and AES key
  3. IV, bcrypt hash, ciphertext looked up in DB with ID from `k`
  4. `k` bcrypt hash compared against bcrypt hash from DB (prevents tampering of URL)
  5. Entry deleted from DB — only the request that deleted it may proceed, so a secret can never be read twice
  6. Ciphertext decrypted with static AES key and IV
  7. Ciphertext decrypted with AES key from `k` and IV
  8. Decrypted text sent to user

## Submitting Secrets via the API (with `curl`)

FlashPaper can accept secret submissions through a simple API. The retrieval URL will be returned in a JSON object.

Here's what it looks like to submit a secret with `curl`:
```
$ curl -s -X POST -d "secret=my secret&json=true" https://my.flashpaper.example
{"url":"https://my.flashpaper.example/?k=xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"}
```

## Settings

### `prune`:
 - `enabled`: Turn on/off auto-pruning of old secrets from the database upon page load
 - `min_days`/`max_days`: When a secret is submitted, a random date/time is generated between `min_days` and `max_days` in the future. After that date/time has elapsed, the secret will be pruned from the database if `enabled` is set to `true`. This is to prevent your database from being filled with secrets that are never retrieved. NOTE: Even if `enabled` is set to `false`, the prune value will still be generated and stored in the database, but secrets will not be pruned unless `enabled` is switched to `true`.

### `base_url`:
FlashPaper will try to generate the secret retrieval URL based on information provided by the upstream webserver. This process isn't always 100% accurate. If the secret retrieval URL that FlashPaper creates isn't correct for your setup (this usually happens when you're using a reverse proxy upstream), you can manually specify the URL that FlashPaper will use. For example: A `base_url` of "https://foo.com/flashpaper" will result in retrieval URLs like "https://foo.com/flashpaper/?k=xxxxxxxxxxxxx".

### `display_logo`:
 - `true`: The logo set in `site_logo` (either a file path or a URL) will be displayed at the top of the page. If `site_logo` isn't set, the default FlashPaper logo at `img/logo.svg` will be displayed
 - `false`: No logo will be displayed on your FlashPaper instance

### `display_title`:
 - `true`: The setting `site_title` will be displayed at the top of the page. If `display_logo` is enabled, the text will appear to the right of the logo.
 - `false`: The setting `site_title` will not be displayed.

## Acknowledgments

FlashPaper was created by [Andrew Paglusch](https://github.com/AndrewPaglusch). The original project, including its demo at [flashpaper.io](https://flashpaper.io), lives at [AndrewPaglusch/FlashPaper](https://github.com/AndrewPaglusch/FlashPaper). If you find this software useful, consider supporting him:

- PayPal: https://paypal.me/AndrewPaglusch
- BitCoin: 1EYDa33S14ejuQGMhSjtBUmBHTBB8mbTRs
