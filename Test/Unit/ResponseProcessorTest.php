<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Http\MarkdownHeaders;
use Hans2103\AgentReady\Model\Response\PageResponse;
use Hans2103\AgentReady\Model\Response\ResponseProcessor;
use Hans2103\AgentReady\Model\Settings;
use PHPUnit\Framework\TestCase;

/**
 * What happens to a front-end response: Markdown, or HTML with Vary and Link headers.
 */
final class ResponseProcessorTest extends TestCase
{
    private const LINK = '<https://shop.example.com/llms.txt>; rel="describedby"; type="text/markdown"';

    private const HTML = '<!DOCTYPE html><html><head><title>Stoel</title><meta name="robots" content="INDEX,FOLLOW"/>'
        . '<script type="application/ld+json">{"@type":"Product"}</script></head>'
        . '<body><header class="page-header">Logo</header><main id="maincontent"><h1>Stoel</h1><p>Van eikenhout.</p></main></body></html>';

    /**
     * @param  array<string, list<string>>  $headers
     */
    private static function page(string $accept = 'text/markdown', string $body = self::HTML, array $headers = [], string $method = 'GET', int $status = 200, bool $loggedIn = false): PageResponse
    {
        return new PageResponse($method, $status, $accept, $body, $headers, 'https://shop.example.com/stoel.html', $loggedIn);
    }

    public function testMarkdownAnswer(): void
    {
        $changes = ResponseProcessor::process(new Settings(contentSignal: 'search=yes'), self::page(headers: ['vary' => ['Accept-Encoding']]), [self::LINK]);

        self::assertNotNull($changes);
        self::assertSame("---\ntitle: \"Stoel\"\n---\n\n# Stoel\n\nVan eikenhout.\n\n```json\n{\"@type\":\"Product\"}\n```\n", $changes->body);
        self::assertSame('text/markdown; charset=utf-8', $changes->set['Content-Type']);
        self::assertSame('Accept-Encoding, Accept', $changes->set['Vary']);
        self::assertSame('search=yes', $changes->set['Content-Signal']);
        self::assertMatchesRegularExpression('/^\d+$/', $changes->set['x-markdown-tokens']);
        self::assertArrayNotHasKey('Link', $changes->set, 'no discovery links on the Markdown answer');
        self::assertSame(MarkdownHeaders::STRIP, $changes->remove);
    }

    public function testHtmlAnswer(): void
    {
        $changes = ResponseProcessor::process(new Settings(), self::page('text/html,*/*;q=0.8', headers: ['link' => ['</a.css>; rel=preload']]), [self::LINK]);

        self::assertNotNull($changes);
        self::assertNull($changes->body);
        self::assertSame(['Vary' => 'Accept', 'Link' => '</a.css>; rel=preload, ' . self::LINK], $changes->set);
        self::assertSame([], $changes->remove);

        $plain = ResponseProcessor::process(new Settings(markdown: false), self::page(), [self::LINK]);
        self::assertSame(['Link' => self::LINK], $plain?->set, 'Markdown off: no conversion, no Vary');

        $noVary = ResponseProcessor::process(new Settings(varyHtml: false, linkHeaders: false), self::page('text/html'), [self::LINK]);
        self::assertSame([], $noVary?->set);
    }

    public function testStaysHtml(): void
    {
        $settings = new Settings();

        self::assertNull(ResponseProcessor::process($settings, self::page(loggedIn: true), [])?->body, 'logged in');
        self::assertNotNull(ResponseProcessor::process(new Settings(skipLoggedIn: false), self::page(loggedIn: true), [])?->body, 'logged in allowed');

        $noindex = str_replace('INDEX,FOLLOW', 'NOINDEX,NOFOLLOW', self::HTML);
        self::assertNull(ResponseProcessor::process($settings, self::page(body: $noindex), [])?->body, 'noindex meta');
        self::assertNull(ResponseProcessor::process($settings, self::page(headers: ['x-robots-tag' => ['none']]), [])?->body, 'X-Robots-Tag');
        self::assertNotNull(ResponseProcessor::process(new Settings(skipNoindex: false), self::page(body: $noindex), [])?->body);

        $empty = '<html><body><main></main></body></html>';
        $changes = ResponseProcessor::process($settings, self::page(body: $empty), [self::LINK]);
        self::assertNull($changes?->body, 'nothing to convert');
        self::assertSame('Accept', $changes?->set['Vary']);
    }

    public function testNotAnHtmlPage(): void
    {
        $settings = new Settings();

        self::assertNull(ResponseProcessor::process($settings, self::page(method: 'POST'), []));
        self::assertNull(ResponseProcessor::process($settings, self::page(status: 404), []));
        self::assertNull(ResponseProcessor::process($settings, self::page(body: '{"a":1}', headers: ['content-type' => ['application/json']]), []));
        self::assertNull(ResponseProcessor::process($settings, self::page(body: 'plain text'), []));
        self::assertNotNull(ResponseProcessor::process($settings, self::page(headers: ['content-type' => ['text/html; charset=UTF-8']], method: 'HEAD'), []));
    }

    public function testNoindexDetection(): void
    {
        self::assertTrue(ResponseProcessor::isNoindex(self::page(body: '<html><head><meta content="noindex" name="robots"></head></html>')), 'attribute order');
        self::assertFalse(ResponseProcessor::isNoindex(self::page(body: '<html><head><meta name="description" content="noindex is a word"></head></html>')));
        self::assertFalse(ResponseProcessor::isNoindex(self::page()));
    }
}
