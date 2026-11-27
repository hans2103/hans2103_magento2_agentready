<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Response\HeadBody;
use Hans2103\AgentReady\Model\Response\ResponseChanges;
use Hans2103\AgentReady\Model\Http\MarkdownHeaders;
use PHPUnit\Framework\TestCase;

/**
 * HEAD requests: the kept body, and changes without a body.
 */
final class HeadRequestTest extends TestCase
{
    public function testHeadBodyIsTakenOnce(): void
    {
        $kept = new HeadBody();
        self::assertNull($kept->take());

        $kept->keep('<html></html>');
        self::assertSame('<html></html>', $kept->take());
        self::assertNull($kept->take());
    }

    public function testWithoutBody(): void
    {
        $html = new ResponseChanges(null, ['Vary' => 'Accept'], []);
        self::assertSame($html, $html->withoutBody(), 'HTML changes have no body to drop');

        $markdown = new ResponseChanges("# Café\n", ['Content-Type' => 'text/markdown; charset=utf-8'], MarkdownHeaders::STRIP, 'e');
        $head     = $markdown->withoutBody();

        self::assertNull($head->body);
        self::assertSame(['Content-Type' => 'text/markdown; charset=utf-8', 'Content-Length' => '8'], $head->set);
        self::assertNotContains('Content-Length', $head->remove);
        self::assertContains('ETag', $head->remove);
        self::assertSame('e', $head->error);
    }
}
