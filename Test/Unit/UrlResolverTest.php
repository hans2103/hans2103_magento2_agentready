<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Html\UrlResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Relative links become absolute against the page URL.
 */
final class UrlResolverTest extends TestCase
{
    /**
     * @return  array<string, array{0: string, 1: string, 2: string}>
     */
    public static function references(): array
    {
        $base = 'https://example.com/a/b?x=1';

        return [
            'root-relative'            => [$base, '/x', 'https://example.com/x'],
            'path-relative'            => [$base, 'img.png', 'https://example.com/a/img.png'],
            'dot segments'             => [$base, '../c/./d', 'https://example.com/c/d'],
            'too many dot-dots'        => [$base, '../../../e', 'https://example.com/e'],
            'query only'               => [$base, '?y=2', 'https://example.com/a/b?y=2'],
            'query and fragment kept'  => [$base, 'f?q=1#top', 'https://example.com/a/f?q=1#top'],
            'fragment untouched'       => [$base, '#top', '#top'],
            'absolute untouched'       => [$base, 'https://other.test/x', 'https://other.test/x'],
            'mailto untouched'         => [$base, 'mailto:a@b.c', 'mailto:a@b.c'],
            'protocol-relative'        => [$base, '//cdn.test/y', 'https://cdn.test/y'],
            'port kept'                => ['https://shop.ddev.site:8443/', 'p', 'https://shop.ddev.site:8443/p'],
            'empty untouched'          => [$base, '', ''],
            'base without path'        => ['https://example.com', 'z', 'https://example.com/z'],
            'invalid base'             => ['not a url', '/x', '/x'],
        ];
    }

    #[DataProvider('references')]
    public function testResolve(string $base, string $reference, string $expected): void
    {
        self::assertSame($expected, UrlResolver::resolve($base, $reference));
    }
}
