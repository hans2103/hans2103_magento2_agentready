<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Http\VaryHeader;
use PHPUnit\Framework\TestCase;

/**
 * Vary merge.
 */
final class VaryHeaderTest extends TestCase
{
    public function testMerge(): void
    {
        self::assertSame('Accept', VaryHeader::merge([]));
        self::assertSame('Accept-Encoding, Accept', VaryHeader::merge(['Accept-Encoding']));
        self::assertSame('accept', VaryHeader::merge(['accept']), 'no duplicate, case-insensitive');
        self::assertSame('Cookie, Accept', VaryHeader::merge(['Cookie, Accept']));
        self::assertSame('Cookie, Accept-Encoding, Accept', VaryHeader::merge(['Cookie', 'Accept-Encoding, cookie']));
        self::assertSame('*', VaryHeader::merge(['*']));
        self::assertSame('Accept', VaryHeader::merge([' , ']));
    }
}
