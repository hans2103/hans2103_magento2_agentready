<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model;

use Hans2103\AgentReady\Model\Http\LinkHeader;

/**
 * The agent discovery links for an HTML page: llms.txt, the agent skills index, the ARD manifest,
 * the MCP Server Card, the sitemap and the Markdown version of the page.
 *
 * Each file link has a mode: "auto" links only when the file exists in the web root (pub/), "1"
 * always links (for a file a module serves through a route), "0" never.
 *
 * @since  26.48.0
 */
final class DiscoveryLinks
{
    /**
     * Link only when the file exists in the web root.
     *
     * @since  26.48.0
     */
    public const MODE_AUTO = 'auto';

    /**
     * Always link.
     *
     * @since  26.48.0
     */
    public const MODE_YES = '1';

    /**
     * Never link.
     *
     * @since  26.48.0
     */
    public const MODE_NO = '0';

    /**
     * @since  26.48.0
     */
    public const LLMS = 'llms.txt';

    /**
     * @since  26.48.0
     */
    public const AGENT_SKILLS = '.well-known/agent-skills/index.json';

    /**
     * The Agentic Resource Discovery manifest.
     *
     * @since  26.48.0
     */
    public const AI_CATALOG = '.well-known/ai-catalog.json';

    /**
     * The MCP Server Card (SEP-1649).
     *
     * @since  26.48.0
     */
    public const MCP_SERVER_CARD = '.well-known/mcp/server-card.json';

    /**
     * The Link header values for a page, in a fixed order.
     *
     * @param   Settings                  $settings    The store view settings
     * @param   string                    $baseUrl     Base URL of the web root, with trailing slash
     * @param   string                    $pageUrl     Absolute URL of the page
     * @param   callable(string): bool    $fileExists  Whether a path exists in the web root
     *
     * @return  list<string>
     *
     * @since   26.48.0
     */
    public static function build(Settings $settings, string $baseUrl, string $pageUrl, callable $fileExists): array
    {
        if (!$settings->linkHeaders) {
            return [];
        }

        $baseUrl = rtrim($baseUrl, '/') . '/';
        $links   = [];

        if (self::shows($settings->linkLlms, self::LLMS, $fileExists)) {
            $links[] = LinkHeader::link($baseUrl . self::LLMS, 'describedby', 'text/markdown');
        }

        if (self::shows($settings->linkAgentSkills, self::AGENT_SKILLS, $fileExists)) {
            $links[] = LinkHeader::link($baseUrl . self::AGENT_SKILLS, 'describedby', 'application/json');
        }

        $catalog = self::aiCatalogUrl($settings, $baseUrl, $fileExists);

        if ($catalog !== null) {
            $links[] = LinkHeader::link($catalog, 'ai-catalog', 'application/json');
        }

        if (self::shows($settings->linkMcpServerCard, self::MCP_SERVER_CARD, $fileExists)) {
            $links[] = LinkHeader::link($baseUrl . self::MCP_SERVER_CARD, 'describedby', 'application/json');
        }

        $sitemap = trim($settings->sitemapUrl);

        if ($sitemap !== '') {
            $links[] = LinkHeader::link(
                preg_match('#^https?://#i', $sitemap) === 1 ? $sitemap : $baseUrl . ltrim($sitemap, '/'),
                'sitemap',
                'application/xml'
            );
        }

        if ($settings->markdown && $settings->linkMarkdown && $pageUrl !== '') {
            $links[] = LinkHeader::link($pageUrl, 'alternate', 'text/markdown');
        }

        return $links;
    }

    /**
     * The URL of the ARD manifest when it is linked (Link header and <link rel="ai-catalog">), else null.
     *
     * @param   Settings                $settings    The store view settings
     * @param   string                  $baseUrl     Base URL of the web root
     * @param   callable(string): bool  $fileExists  Whether a path exists in the web root
     *
     * @return  string|null
     *
     * @since   26.48.0
     */
    public static function aiCatalogUrl(Settings $settings, string $baseUrl, callable $fileExists): ?string
    {
        if (!$settings->linkHeaders || !self::shows($settings->linkAiCatalog, self::AI_CATALOG, $fileExists)) {
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . self::AI_CATALOG;
    }

    /**
     * Whether a file link with this mode is sent.
     *
     * @param   string                  $mode        One of the MODE_* constants
     * @param   string                  $path        Path relative to the web root
     * @param   callable(string): bool  $fileExists  Whether a path exists in the web root
     *
     * @return  boolean
     *
     * @since   26.48.0
     */
    private static function shows(string $mode, string $path, callable $fileExists): bool
    {
        return match ($mode) {
            self::MODE_YES  => true,
            self::MODE_AUTO => (bool) $fileExists($path),
            default         => false,
        };
    }
}
