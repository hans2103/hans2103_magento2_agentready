# Changelog

All notable changes to this module. Versions follow calendar versioning `YY.WW.PATCH`.

## 26.48.0

First version, ported from the Joomla plugin plg_system_agentready 26.45.1.

- Markdown for Agents: `Accept: text/markdown` gets a Markdown version of the page (frontmatter, main content, JSON-LD), also for pages the built-in page cache serves.
- Link headers to llms.txt, the agent skills index, the ARD manifest (also as `<link rel="ai-catalog">` in the head), the MCP Server Card, the sitemap and the Markdown version.
- Link modes Auto / Yes / No per discovery file, so a file a module serves through a route can be linked too.
- All settings per store view, with en_US and nl_NL translations.
