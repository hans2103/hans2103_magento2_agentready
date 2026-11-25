# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Agent Ready for Magento 2 (`Hans2103_AgentReady`, Composer package `hans2103/magento2-agentready`) makes a shop readable for AI agents: a Markdown version of each page for `Accept: text/markdown`, and agent discovery Link headers. It is a port of the Joomla plugin `plg_system_agentready`; keep the two in step when the shared logic changes.

Public repo: no client or shop names in code, tests, docs or commit messages.

Language convention: PHP docblocks, `README.md` and this file in English; commit messages in Dutch.

## Repository Layout

```
registration.php  composer.json  phpunit.xml.dist
etc/                      module.xml, config.xml (defaults), acl.xml, adminhtml/system.xml, frontend/events.xml
Model/Negotiation/        AcceptHeader (q-values)
Model/Html/               Selector (CSS → XPath), PageExtractor, ExtractedPage, UrlResolver
Model/Markdown/           MarkdownRenderer (league/html-to-markdown, tidy, tokens)
Model/Http/               VaryHeader, LinkHeader, MarkdownHeaders
Model/Response/           ResponseProcessor (the decision), PageResponse, ResponseChanges
Model/Settings.php        settings as plain values;  Model/DiscoveryLinks.php  the Link values
Model/Config.php          reads the store config into Settings;  Model/DiscoveryFiles.php  pub/ checks, base URL
Model/Config/Source/      LinkMode (Auto / Yes / No)
Observer/ProcessResponse  controller_front_send_response_before: applies ResponseChanges
ViewModel/AiCatalog       <link rel="ai-catalog"> in head.additional
view/frontend/  i18n/{en_US,nl_NL}.csv  Test/Unit/  .github/workflows/{ci,release}.yml
```

## Commands

```bash
mkdir -p /tmp/agentready-ci && composer --working-dir=/tmp/agentready-ci require league/html-to-markdown:^5.2 phpunit/phpunit:^11.5
/tmp/agentready-ci/vendor/bin/phpunit
find . -path ./.git -prune -o \( -name '*.php' -o -name '*.phtml' \) -print0 | xargs -0 -n1 php -l
composer validate --no-check-publish --no-check-lock
```

## Code Standards

- PSR-12, four spaces, `declare(strict_types=1);` in every PHP file; DocBlocks with `@since`.
- Everything under `Model/` except `Config.php`, `DiscoveryFiles.php` and `Config/Source/` stays free of Magento classes, so the unit tests run without Magento. Put new logic there and keep `Observer` and `ViewModel` thin.
- A new setting needs: a default in `etc/config.xml`, a field in `etc/adminhtml/system.xml`, a property in `Model/Settings.php`, a read in `Model/Config.php`, and the label and comment in both `i18n` files. `Test/Unit/ModuleContractTest.php` checks this.

## Committing

Commit messages in Dutch, conventional and with an issue number: `feat(markdown): #12 omschrijving` (`#000` when there is no issue). No `Co-Authored-By` line.

## PR's

Do **not** add a 'Generated with Claude Code' line in a PR.

## Versioning

Calendar versioning `YY.WW.PATCH` (e.g. `26.48.0`), tagged `v{version}`. Composer reads the version from the tag; `composer.json` has no `version` field. Update `CHANGELOG.md` with every release.

Release: add a `## {version}` section to `CHANGELOG.md`, merge to `main`, then tag and push (`git tag v{version} && git push origin v{version}`). The tag triggers `.github/workflows/release.yml`, which creates the GitHub Release with that section as its notes, and fails when the section is missing. No zip is attached: Composer installs from the tag.
