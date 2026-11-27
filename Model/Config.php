<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Reads the Agent Ready settings of a store view (Stores > Configuration > General > Agent Ready).
 *
 * @since  26.48.0
 */
class Config
{
    /**
     * @since  26.48.0
     */
    public const XML_PATH = 'agentready/';

    /**
     * @param   ScopeConfigInterface  $scopeConfig  The store configuration
     *
     * @since   26.48.0
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * The settings of a store view (null = the current one).
     *
     * @param   integer|string|null  $store  Store id or code
     *
     * @return  Settings
     *
     * @since   26.48.0
     */
    public function settings(int|string|null $store = null): Settings
    {
        return new Settings(
            markdown: $this->flag('markdown/enabled', $store),
            contentSelector: $this->value('markdown/content_selector', $store),
            removeSelectors: $this->value('markdown/remove_selectors', $store),
            frontmatter: $this->flag('markdown/frontmatter', $store),
            jsonLd: $this->flag('markdown/jsonld', $store),
            linkHeaders: $this->flag('links/enabled', $store),
            linkLlms: $this->value('links/llms', $store),
            linkAgentSkills: $this->value('links/agent_skills', $store),
            linkAiCatalog: $this->value('links/ai_catalog', $store),
            linkMcpServerCard: $this->value('links/mcp_server_card', $store),
            sitemapUrl: $this->value('links/sitemap_url', $store),
            linkMarkdown: $this->flag('links/markdown', $store),
            skipLoggedIn: $this->flag('advanced/skip_logged_in', $store),
            skipNoindex: $this->flag('advanced/skip_noindex', $store),
            skipActions: $this->value('advanced/skip_actions', $store),
            varyHtml: $this->flag('advanced/vary_html', $store),
            contentSignal: $this->value('advanced/content_signal', $store),
        );
    }

    /**
     * @param   string               $path   Path below agentready/
     * @param   integer|string|null  $store  Store id or code
     *
     * @return  boolean
     *
     * @since   26.48.0
     */
    private function flag(string $path, int|string|null $store): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH . $path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param   string               $path   Path below agentready/
     * @param   integer|string|null  $store  Store id or code
     *
     * @return  string
     *
     * @since   26.48.0
     */
    private function value(string $path, int|string|null $store): string
    {
        return trim((string) $this->scopeConfig->getValue(self::XML_PATH . $path, ScopeInterface::SCOPE_STORE, $store));
    }
}
