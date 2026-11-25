<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Negotiation\AcceptHeader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Markdown only when text/markdown is asked for explicitly and ranks at least as high as HTML.
 */
final class AcceptHeaderTest extends TestCase
{
    /**
     * @return  array<string, array{0: string|null, 1: bool}>
     */
    public static function headers(): array
    {
        return [
            'no header'                      => [null, false],
            'empty header'                   => ['', false],
            'only markdown'                  => ['text/markdown', true],
            'markdown and html, equal q'     => ['text/markdown, text/html', true],
            'html first, markdown lower q'   => ['text/html, text/markdown;q=0.9', false],
            'markdown lower than html'       => ['text/markdown;q=0.5, text/html', false],
            'markdown q=0'                   => ['text/markdown;q=0', false],
            'wildcard is not markdown'       => ['*/*', false],
            'text wildcard is not markdown'  => ['text/*', false],
            'markdown above */*'             => ['text/markdown;q=0.8, */*;q=0.1', true],
            'markdown below text/*'          => ['text/markdown;q=0.5, text/*;q=0.9', false],
            'specific html beats wildcard'   => ['text/markdown;q=0.5, text/html;q=0.4, */*', true],
            'case-insensitive'               => ['Text/Markdown', true],
            'spaces and charset param'       => [' text/markdown ; charset=utf-8 ; q=1.0 , text/html;q=0.9', true],
            'browser header'                 => ['text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', false],
            'garbage q'                      => ['text/markdown;q=abc', false],
        ];
    }

    #[DataProvider('headers')]
    public function testPrefersMarkdown(?string $accept, bool $expected): void
    {
        self::assertSame($expected, AcceptHeader::prefersMarkdown($accept));
    }
}
