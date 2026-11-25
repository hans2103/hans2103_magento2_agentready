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
use PHPUnit\Framework\TestCase;

/**
 * Response header rules for a Markdown answer.
 */
final class MarkdownHeadersTest extends TestCase
{
    public function testStrippedHeaders(): void
    {
        foreach (['ETag', 'Last-Modified', 'Content-Length', 'Content-Encoding', 'Content-Range', 'Transfer-Encoding'] as $name) {
            self::assertContains($name, MarkdownHeaders::STRIP);
        }

        foreach (['Cache-Control', 'Expires', 'Set-Cookie', 'Content-Security-Policy', 'Strict-Transport-Security', 'Vary', 'X-Magento-Tags'] as $name) {
            self::assertNotContains($name, MarkdownHeaders::STRIP);
        }
    }

    public function testSizeLimit(): void
    {
        self::assertSame(6291456, MarkdownHeaders::MAX_HTML_BYTES);
        self::assertTrue(MarkdownHeaders::fits(str_repeat('a', 6291456)));
        self::assertFalse(MarkdownHeaders::fits(str_repeat('a', 6291457)));
    }

    public function testContentSignal(): void
    {
        self::assertSame('search=yes, ai-train=no', MarkdownHeaders::contentSignal([], ' search=yes, ai-train=no '));
        self::assertNull(MarkdownHeaders::contentSignal([], '  '));
        self::assertNull(MarkdownHeaders::contentSignal(['ai-train=no'], 'ai-train=yes'), 'the site value wins');
        self::assertSame('search=yes', MarkdownHeaders::contentSignal([' '], 'search=yes'));
    }
}
