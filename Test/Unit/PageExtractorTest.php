<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Html\PageExtractor;
use PHPUnit\Framework\TestCase;

/**
 * Metadata, JSON-LD and the cleaned main content.
 */
final class PageExtractorTest extends TestCase
{
    private const REMOVE = 'script, style, nav, [hidden], [aria-hidden=true], button, .sidebar';

    public function testFullPage(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en"><head>
<title>Doc title</title>
<meta property="og:title" content="OG title">
<meta property="og:description" content="OG description">
<meta name="description" content="  Real
 description ">
<meta property="og:image" content="https://example.com/i.png">
<meta property="og:url" content="https://example.com/og">
<link rel="canonical" href="/en/page">
<script type="application/ld+json">
 {"@type":"Organization"}
</script>
<style>body{}</style>
</head><body>
<header class="page-header"><nav><a href="/">Menu</a></nav></header>
<main id="maincontent">
  <!-- a comment -->
  <h1>Café — 🎄</h1>
  <p>Read <a href="/more">more</a> or <a href="#x">jump</a>. <img src="img/a.png" alt="A"></p>
  <nav><a href="/sub">Sub menu</a></nav>
  <div hidden>Hidden notice</div>
  <span aria-hidden="true">icon</span>
  <button type="button">Close</button>
  <div class="sidebar">Compare products</div>
  <script>alert(1)</script>
  <script type="application/ld+json">{"@type":"Product"}</script>
</main>
<div id="cookie">Cookies!</div>
</body></html>
HTML;

        $page = PageExtractor::extract($html, 'main, body', self::REMOVE, 'https://example.com/en/page');

        self::assertNotNull($page);
        self::assertSame(['title' => 'OG title', 'description' => 'Real description', 'url' => 'https://example.com/en/page', 'lang' => 'en', 'image' => 'https://example.com/i.png'], $page->meta);
        self::assertSame(['{"@type":"Organization"}', '{"@type":"Product"}'], $page->jsonLd);
        self::assertStringContainsString('<h1>Café — 🎄</h1>', $page->contentHtml);
        self::assertStringContainsString('href="https://example.com/more"', $page->contentHtml);
        self::assertStringContainsString('src="https://example.com/en/img/a.png"', $page->contentHtml);
        self::assertStringContainsString('jump', $page->contentHtml);
        self::assertStringNotContainsString('href="#x"', $page->contentHtml);

        foreach (['Sub menu', 'Hidden notice', 'icon', 'Close', 'Compare products', 'alert(1)', 'Product', 'a comment', 'Cookies!', 'Menu</a>'] as $gone) {
            self::assertStringNotContainsString($gone, $page->contentHtml);
        }
    }

    public function testCanonicalUrl(): void
    {
        $absolute = PageExtractor::extract('<html><head><link rel="canonical" href="https://shop.test/p.html"></head><body>B</body></html>', 'body', '', 'https://example.com/p.html?utm=1');
        self::assertSame(['url' => 'https://shop.test/p.html'], $absolute?->meta);

        $og = PageExtractor::extract('<html><head><meta property="og:url" content="/og.html"></head><body>B</body></html>', 'body', '', 'https://example.com/x/');
        self::assertSame(['url' => 'https://example.com/og.html'], $og?->meta);

        $none = PageExtractor::extract('<html lang=" nl-NL "><head></head><body>B</body></html>', 'body', '', 'https://example.com/');
        self::assertSame(['lang' => 'nl-NL'], $none?->meta);
    }

    public function testFallbacksAndOrder(): void
    {
        $noMain = PageExtractor::extract('<html><head><title>T</title></head><body><p>Only body</p></body></html>', 'main, body', self::REMOVE, 'https://example.com/');
        self::assertSame('<p>Only body</p>', $noMain?->contentHtml);
        self::assertSame(['title' => 'T'], $noMain?->meta);
        self::assertSame([], $noMain?->jsonLd);

        $order = PageExtractor::extract('<body><article>A</article><main>M</main></body>', 'main, article', '', 'https://example.com/');
        self::assertSame('M', $order?->contentHtml, 'list order wins, not document order');

        $base = PageExtractor::extract('<head><base href="/sub/"></head><body><a href="x">x</a></body>', 'body', '', 'https://example.com/a/b');
        self::assertSame('<a href="https://example.com/sub/x">x</a>', $base?->contentHtml);
    }

    public function testSiteLandmarks(): void
    {
        $html = <<<'HTML'
<body>
<header class="site">Site header</header>
<div role="banner">Banner div</div>
<article><header><h1>Article title</h1></header><p>Text</p><footer>Article footer</footer></article>
<section><header>Section header</header></section>
<div class="wrap"><footer>Wrapped site footer</footer></div>
<div role="contentinfo">Contentinfo div</div>
<footer>Site footer</footer>
</body>
HTML;
        $body = PageExtractor::extract($html, 'body', '', 'https://example.com/')?->contentHtml ?? '';

        foreach (['Site header', 'Banner div', 'Wrapped site footer', 'Contentinfo div', 'Site footer'] as $gone) {
            self::assertStringNotContainsString($gone, $body);
        }

        foreach (['Article title', 'Article footer', 'Section header', 'Text'] as $kept) {
            self::assertStringContainsString($kept, $body);
        }

        $inMain = PageExtractor::extract('<body><main><header><h1>Main title</h1></header><p>x</p></main></body>', 'main', '', 'https://example.com/');
        self::assertSame('<header><h1>Main title</h1></header> <p>x</p>', $inMain?->contentHtml);
    }

    public function testNothingToExtract(): void
    {
        self::assertNull(PageExtractor::extract('   ', 'body', '', 'https://example.com/'));
        self::assertNull(PageExtractor::extract('<body><p>x</p></body>', 'main', '', 'https://example.com/'));
        self::assertNull(PageExtractor::extract('<body><p>x</p></body>', 'a:hover', '', 'https://example.com/'));
    }
}
