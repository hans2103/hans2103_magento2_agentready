# Changelog

All notable changes to this module. Versions follow calendar versioning `YY.WW.PATCH`.

## 26.48.4

- HEAD requests get the same Link, Vary and Markdown headers as GET, also without Varnish. Magento empties a HEAD body before `controller_front_send_response_before`, so the module saw no HTML and added nothing (#11). Run `bin/magento cache:flush` after updating: a new plugin is registered.

## 26.48.3

**Upgrade note:** Markdown and Link headers are now off after installation. A store that relied on the old default (no value saved in the admin) loses both after upgrading; switch them on under **Stores > Configuration > General > Agent Ready**, or with `bin/magento config:set agentready/markdown/enabled 1` and `bin/magento config:set agentready/links/enabled 1`, before or right after upgrading.

- Cart, checkout, customer account, order, wishlist and contact pages always get HTML (`checkout_*`, `multishipping_*`, `customer_*`, `sales_*`, `wishlist_*`, `contact_*`). A new setting, **Also leave out (full action names)**, adds more pages; `*` at the end matches a prefix.
- The frontmatter has the page's canonical URL (`url`, from `<link rel="canonical">` or `og:url`, made absolute) and language (`lang`, from `<html lang>`).
- Markdown and Link headers are off by default.
- The `rel="alternate"` Markdown link is left out on pages that never get Markdown (excluded pages, noindex, logged-in customers), as in plg_system_agentready.
- README: Magento answers a Magewire or Web API request with only `Accept: text/markdown` with HTTP 500; the module does not touch POST requests.

## 26.48.2

Release process only; the module code is unchanged from 26.48.1.

- Every tag `v{version}` now gets a GitHub Release, with the matching section of this changelog as its notes.
- CI uses `actions/checkout` v7.

## 26.48.1

Cleaner Markdown for shop pages.

- Indentation outside lists is removed, so an indented line (a price) no longer becomes a code block. List nesting stays.
- Links without text (an icon, or a decorative image) get their `aria-label` or `title` as text, and are dropped when they have neither. List items left empty are dropped, and lists left without items.
- Whitespace at the start and end of a link's text moves outside the link: `[text](url)` instead of `[ text ](url)`.
- Skip links (an in-page link with class `sr-only`, `visually-hidden`, `screen-reader-text` or `skip`, like "Skip carousel") are removed.
- A `<label>` whose form control is removed (the quantity field) is removed too.

## 26.48.0

First version, ported from the Joomla plugin plg_system_agentready 26.45.1.

- Markdown for Agents: `Accept: text/markdown` gets a Markdown version of the page (frontmatter, main content, JSON-LD), also for pages the built-in page cache serves.
- Link headers to llms.txt, the agent skills index, the ARD manifest (also as `<link rel="ai-catalog">` in the head), the MCP Server Card, the sitemap and the Markdown version.
- Link modes Auto / Yes / No per discovery file, so a file a module serves through a route can be linked too.
- All settings per store view, with en_US and nl_NL translations.
