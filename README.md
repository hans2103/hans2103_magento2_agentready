# Agent Ready for Magento 2

Makes a Magento 2 shop readable for AI agents. A port of the Joomla plugin [plg_system_agentready](https://github.com/hans2103/plg_system_agentready).

- **Markdown for Agents**: a request with `Accept: text/markdown` gets a Markdown version of the page (YAML frontmatter, the main content, the page's JSON-LD at the end), the way Cloudflare's Markdown for Agents does on paid plans. HTML stays the default for browsers and crawlers.
- **Link headers** (RFC 8288) on HTML pages to `llms.txt`, `.well-known/agent-skills/index.json`, `.well-known/ai-catalog.json` (also as `<link rel="ai-catalog">` in the head), `.well-known/mcp/server-card.json`, the sitemap and the Markdown version of the page.

All settings are per store view: **Stores > Configuration > General > Agent Ready**.

**Requirements:** Magento 2.4 (`magento/framework` 103), PHP 8.2+, `league/html-to-markdown` ^5.2 (installed by Composer).

## Installation

```bash
composer config repositories.hans2103-agentready vcs https://github.com/hans2103/hans2103_magento2_agentready
composer require hans2103/magento2-agentready
bin/magento module:enable Hans2103_AgentReady
bin/magento setup:upgrade
bin/magento cache:flush
```

Both features are off after installation. Switch on **Markdown version** and **Link headers** in the admin, or for a store view:

```bash
bin/magento config:set --scope=stores --scope-code=default agentready/markdown/enabled 1
bin/magento config:set --scope=stores --scope-code=default agentready/links/enabled 1
bin/magento cache:flush
```

Flush the full page cache (and Varnish, if you use it) after installing or changing settings: the Link headers are added on the way out, but cached pages hold the `<link rel="ai-catalog">` in their head.

## Try it

```bash
curl -s -H 'Accept: text/markdown' https://shop.example.com/some-product.html
curl -sI https://shop.example.com/ | grep -iE '^(link|vary):'
```

## Settings

| Group | Setting | Default |
|---|---|---|
| Markdown | Markdown version | No |
| | Main content (CSS selectors, first match wins) | `main, [role=main], #maincontent, article, body` |
| | Leave out (CSS selectors) | scripts, media, forms, `nav`, hidden elements, `.sidebar`, `.messages`, `.toolbar` |
| | Frontmatter / JSON-LD | Yes / Yes |
| Link headers | Link headers | No |
| | llms.txt, agent skills index, AI catalog, MCP Server Card | Auto |
| | Sitemap URL | empty (no link) |
| | Markdown version (rel=alternate) | Yes |
| Advanced | Not for logged-in customers / Not for noindex pages | Yes / Yes |
| | Also leave out (full action names) | empty |
| | Vary: Accept on HTML | Yes |
| | Content-Signal header | empty (none) |

The selectors support a small CSS subset: tag, `*`, `#id`, `.class`, `[attr]`, `[attr=value]`, the descendant (space) and child (`>`) combinators, and comma-separated lists. A part with other syntax is skipped.

**Link modes.** *Auto* links a file only when it exists in `pub/` (or the Magento root, when that is the document root). *Yes* always links, for a file another module serves through a route. *No* never links.

## How it works

The observer `Observer\ProcessResponse` listens to `controller_front_send_response_before` in the front-end area. That event also runs for a page the built-in page cache serves, so the cache keeps storing HTML and the Markdown is made from it on the way out.

Only GET and HEAD requests with status 200 and an HTML body are touched. Markdown is chosen only when `text/markdown` is listed explicitly with a q-value at least as high as HTML's; wildcards never ask for Markdown. Cart, checkout, customer account, order, wishlist and contact pages (`checkout_*`, `multishipping_*`, `customer_*`, `sales_*`, `wishlist_*`, `contact_*`) always get HTML, as do the full action names in **Also leave out**. So does a page larger than 6 MiB, a page without matching content, a logged-in customer or a noindex page.

The Markdown answer has `Content-Type: text/markdown; charset=utf-8`, an `x-markdown-tokens` estimate and `Vary: Accept`, and no longer has the headers that describe the HTML body (`ETag`, `Last-Modified`, `Content-Length`, `Content-Encoding`, `Content-Range`, `Transfer-Encoding`).

### Magewire and Web API requests

Only GET and HEAD pages are converted; POST requests are left alone. Magento itself answers a request whose `Accept` header names no media type its Web API renderers know, such as `Accept: text/markdown` alone, with HTTP 500 (`Server cannot match any of the given Accept HTTP header media type(s)`). Magewire update requests (Hyvä Checkout) go through that renderer, so an agent that sends them with only `text/markdown` gets a 500, with or without this module. Seen on Magento 2.4.9; a normal browser or agent that also accepts `application/json` or `*/*` is not affected.

### Varnish and CDNs

HTML pages get `Vary: Accept`, so a cache keeps the HTML and Markdown variants apart. Browsers send many different `Accept` values, so with Varnish that means one stored copy per distinct header. Better: give Markdown requests their own hash and set **Vary: Accept on HTML** to *No*. Add this inside the existing `sub vcl_hash` of your VCL, before any `return`:

```vcl
if (req.http.Accept ~ "text/markdown") {
    hash_data("agentready-markdown");
}
```

The Markdown answer itself always keeps `Vary: Accept`.

Cloudflare ignores `Vary: Accept` for HTML. Behind Cloudflare, bypass the cache for requests with `Accept: text/markdown` (a cache rule), or use Cloudflare's own Markdown for Agents.

## Development

The classes under `Model/Negotiation`, `Model/Html`, `Model/Markdown`, `Model/Http` and `Model/Response`, plus `Model/Settings` and `Model/DiscoveryLinks`, do not use Magento, so the unit tests run without a Magento install:

```bash
mkdir -p /tmp/agentready-ci && composer --working-dir=/tmp/agentready-ci require league/html-to-markdown:^5.2 phpunit/phpunit:^11.5
/tmp/agentready-ci/vendor/bin/phpunit
```

CI (`.github/workflows/ci.yml`) runs lint, XML validation, `composer validate` and PHPUnit on PHP 8.2 to 8.5.

## License

GPL-2.0-or-later, see `LICENSE.txt`.
