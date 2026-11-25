<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\DiscoveryLinks;
use Hans2103\AgentReady\Model\Settings;
use PHPUnit\Framework\TestCase;

/**
 * Which discovery links an HTML page gets, per link mode.
 */
final class DiscoveryLinksTest extends TestCase
{
    private const BASE = 'https://shop.example.com/';
    private const PAGE = 'https://shop.example.com/stoel.html';

    public function testAutoLinksOnlyExistingFiles(): void
    {
        $links = DiscoveryLinks::build(new Settings(), self::BASE, self::PAGE, static fn (string $path): bool => $path === DiscoveryLinks::LLMS);

        self::assertSame([
            '<https://shop.example.com/llms.txt>; rel="describedby"; type="text/markdown"',
            '<https://shop.example.com/stoel.html>; rel="alternate"; type="text/markdown"',
        ], $links);
    }

    public function testAllLinksInOrder(): void
    {
        $settings = new Settings(
            linkLlms: DiscoveryLinks::MODE_YES,
            linkAgentSkills: DiscoveryLinks::MODE_YES,
            linkAiCatalog: DiscoveryLinks::MODE_YES,
            linkMcpServerCard: DiscoveryLinks::MODE_YES,
            sitemapUrl: '/sitemap.xml',
        );

        self::assertSame([
            '<https://shop.example.com/llms.txt>; rel="describedby"; type="text/markdown"',
            '<https://shop.example.com/.well-known/agent-skills/index.json>; rel="describedby"; type="application/json"',
            '<https://shop.example.com/.well-known/ai-catalog.json>; rel="ai-catalog"; type="application/json"',
            '<https://shop.example.com/.well-known/mcp/server-card.json>; rel="describedby"; type="application/json"',
            '<https://shop.example.com/sitemap.xml>; rel="sitemap"; type="application/xml"',
            '<https://shop.example.com/stoel.html>; rel="alternate"; type="text/markdown"',
        ], DiscoveryLinks::build($settings, 'https://shop.example.com', self::PAGE, static fn (): bool => false));
    }

    public function testModeNoAndSwitches(): void
    {
        $exists = static fn (): bool => true;

        $no = new Settings(linkLlms: '0', linkAgentSkills: '0', linkAiCatalog: '0', linkMcpServerCard: '0', linkMarkdown: false);
        self::assertSame([], DiscoveryLinks::build($no, self::BASE, self::PAGE, $exists));

        self::assertSame([], DiscoveryLinks::build(new Settings(linkHeaders: false), self::BASE, self::PAGE, $exists));

        $noMarkdown = DiscoveryLinks::build(new Settings(markdown: false, linkLlms: '0', linkAgentSkills: '0', linkAiCatalog: '0', linkMcpServerCard: '0'), self::BASE, self::PAGE, $exists);
        self::assertSame([], $noMarkdown, 'no Markdown alternate when the Markdown version is off');

        $sitemap = DiscoveryLinks::build(new Settings(linkLlms: '0', linkAgentSkills: '0', linkAiCatalog: '0', linkMcpServerCard: '0', sitemapUrl: 'https://cdn.example.com/s.xml', linkMarkdown: false), self::BASE, self::PAGE, $exists);
        self::assertSame(['<https://cdn.example.com/s.xml>; rel="sitemap"; type="application/xml"'], $sitemap);
    }

    public function testAiCatalogUrl(): void
    {
        self::assertSame('https://shop.example.com/.well-known/ai-catalog.json', DiscoveryLinks::aiCatalogUrl(new Settings(), self::BASE, static fn (): bool => true));
        self::assertNull(DiscoveryLinks::aiCatalogUrl(new Settings(), self::BASE, static fn (): bool => false));
        self::assertNull(DiscoveryLinks::aiCatalogUrl(new Settings(linkHeaders: false, linkAiCatalog: '1'), self::BASE, static fn (): bool => true));
    }
}
