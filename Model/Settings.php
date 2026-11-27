<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model;

/**
 * The module settings of one store view, as plain values (no Magento classes), so the response
 * logic can be tested without Magento. Model\Config builds it from the store configuration.
 *
 * @since  26.48.0
 */
final readonly class Settings
{
    /**
     * Default selectors for the main content, in order of preference.
     *
     * @since  26.48.0
     */
    public const DEFAULT_CONTENT_SELECTOR = 'main, [role=main], #maincontent, article, body';

    /**
     * Default selectors removed from the main content.
     *
     * @since  26.48.0
     */
    public const DEFAULT_REMOVE_SELECTORS = 'script, style, noscript, template, svg, canvas, iframe, object, embed, form, button, input, select, textarea, nav, dialog, [hidden], [aria-hidden=true], .sidebar, .messages, .toolbar';

    /**
     * @param   boolean  $markdown         Answer Accept: text/markdown with Markdown
     * @param   string   $contentSelector  Comma-separated selectors for the main content
     * @param   string   $removeSelectors  Comma-separated selectors left out of the Markdown
     * @param   boolean  $frontmatter      Start the Markdown with YAML frontmatter
     * @param   boolean  $jsonLd           End the Markdown with the page's JSON-LD
     * @param   boolean  $linkHeaders      Send the agent discovery Link headers
     * @param   string   $linkLlms         DiscoveryLinks::MODE_* for llms.txt
     * @param   string   $linkAgentSkills  DiscoveryLinks::MODE_* for the agent skills index
     * @param   string   $linkAiCatalog    DiscoveryLinks::MODE_* for the ARD manifest
     * @param   string   $linkMcpServerCard  DiscoveryLinks::MODE_* for the MCP Server Card
     * @param   string   $sitemapUrl       Sitemap URL, absolute or relative to the base URL ('' = none)
     * @param   boolean  $linkMarkdown     Link to the Markdown version of the page
     * @param   boolean  $skipLoggedIn     Logged-in customers always get HTML
     * @param   boolean  $skipNoindex      Pages with noindex stay HTML
     * @param   string   $skipActions      Extra full action names that stay HTML (see ExcludedPages)
     * @param   boolean  $varyHtml         Send Vary: Accept with HTML pages too
     * @param   string   $contentSignal    Content-Signal header for the Markdown answer ('' = none)
     *
     * @since   26.48.0
     */
    public function __construct(
        public bool $markdown = false,
        public string $contentSelector = self::DEFAULT_CONTENT_SELECTOR,
        public string $removeSelectors = self::DEFAULT_REMOVE_SELECTORS,
        public bool $frontmatter = true,
        public bool $jsonLd = true,
        public bool $linkHeaders = false,
        public string $linkLlms = DiscoveryLinks::MODE_AUTO,
        public string $linkAgentSkills = DiscoveryLinks::MODE_AUTO,
        public string $linkAiCatalog = DiscoveryLinks::MODE_AUTO,
        public string $linkMcpServerCard = DiscoveryLinks::MODE_AUTO,
        public string $sitemapUrl = '',
        public bool $linkMarkdown = true,
        public bool $skipLoggedIn = true,
        public bool $skipNoindex = true,
        public string $skipActions = '',
        public bool $varyHtml = true,
        public string $contentSignal = '',
    ) {
    }

    /**
     * The content selector, or the default when the setting is empty.
     *
     * @return  string
     *
     * @since   26.48.0
     */
    public function contentSelector(): string
    {
        return trim($this->contentSelector) === '' ? self::DEFAULT_CONTENT_SELECTOR : trim($this->contentSelector);
    }
}
