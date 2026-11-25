<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Http\LinkHeader;
use PHPUnit\Framework\TestCase;

/**
 * Link header values (RFC 8288).
 */
final class LinkHeaderTest extends TestCase
{
    private const LLMS = '<https://example.com/llms.txt>; rel="describedby"; type="text/markdown"';

    public function testLink(): void
    {
        self::assertSame(self::LLMS, LinkHeader::link('https://example.com/llms.txt', 'describedby', 'text/markdown'));
        self::assertSame('<https://example.com/>; rel="canonical"', LinkHeader::link('https://example.com/', 'canonical'));
        self::assertSame('<https://example.com/a%20b%3E>; rel="x"', LinkHeader::link('https://example.com/a b>', 'x'));
    }

    public function testMerge(): void
    {
        self::assertSame(self::LLMS, LinkHeader::merge([], [self::LLMS]));
        self::assertSame(
            '</style.css>; rel=preload; as=style, ' . self::LLMS,
            LinkHeader::merge(['</style.css>; rel=preload; as=style'], [self::LLMS]),
            'existing value first, kept as is'
        );
        self::assertSame(
            '<https://example.com/llms.txt>; rel="describedby"',
            LinkHeader::merge(['<https://example.com/llms.txt>; rel="describedby"'], [self::LLMS]),
            'same uri and rel not repeated'
        );
        self::assertSame(
            '<https://example.com/llms.txt>; rel="alternate", ' . self::LLMS,
            LinkHeader::merge(['<https://example.com/llms.txt>; rel="alternate"'], [self::LLMS]),
            'same uri, other rel kept'
        );
        self::assertSame(
            '<https://e.com/a,b>; rel="x"; title="a, b"',
            LinkHeader::merge(['<https://e.com/a,b>; rel="x"; title="a, b"'], []),
            'commas inside uri and quotes do not split'
        );
        self::assertSame('<a>; rel="x", <b>; rel="y"', LinkHeader::merge(['<a>; rel="x", <b>; rel="y"', '<a>; rel=x'], []));
    }
}
