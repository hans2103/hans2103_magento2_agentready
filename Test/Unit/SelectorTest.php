<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Hans2103\AgentReady\Model\Html\Selector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * CSS selector subset to XPath.
 */
final class SelectorTest extends TestCase
{
    private const HTML = <<<'HTML'
<html><body>
<nav id="menu"><a id="n1" href="/">Home</a></nav>
<main id="maincontent" class="page-main page" role="main">
  <div class="x a b" id="d1"><p id="p1">One</p></div>
  <div class="a" id="d2" hidden><span id="s1" aria-hidden="true">x</span></div>
  <ul id="u1"><li id="l1">A<ul><li id="l2">B</li></ul></li></ul>
</main>
</body></html>
HTML;

    /**
     * @return  array<string, array{0: string, 1: list<string>}>
     */
    public static function selectors(): array
    {
        return [
            'tag'                     => ['main', ['maincontent']],
            'id'                      => ['#maincontent', ['maincontent']],
            'two classes'             => ['.a.b', ['d1']],
            'tag.class'               => ['div.x', ['d1']],
            'class matches whole word' => ['.page-m', []],
            'attr bare value'         => ['[role=main]', ['maincontent']],
            'attr quoted value'       => ['[aria-hidden="true"]', ['s1']],
            'attr presence'           => ['[hidden]', ['d2']],
            'descendant'              => ['nav a', ['n1']],
            'child'                   => ['#u1 > li', ['l1']],
            'descendant reaches nested' => ['#u1 li', ['l1', 'l2']],
            'list'                    => ['nav, main', ['menu', 'maincontent']],
            'invalid part skipped'    => ['div::before, #p1', ['p1']],
            'quote in value'          => ["[title=\"it's\"]", []],
            'uppercase tag'           => ['MAIN', ['maincontent']],
        ];
    }

    /**
     * @param  list<string>  $expected
     */
    #[DataProvider('selectors')]
    public function testMatches(string $selector, array $expected): void
    {
        $doc = new DOMDocument();
        @$doc->loadHTML(self::HTML);
        $path = Selector::toXPath($selector);

        self::assertNotNull($path);

        $ids = [];

        foreach ((new DOMXPath($doc))->query($path) ?: [] as $node) {
            $ids[] = $node instanceof DOMElement ? ($node->getAttribute('id') ?: $node->nodeName) : '?';
        }

        self::assertSame($expected, $ids);
    }

    public function testInvalid(): void
    {
        self::assertNull(Selector::toXPath('a:hover, ~x'));
        self::assertNull(Selector::toXPath(' , '));
        self::assertCount(2, Selector::toXPaths('body, main, a:hover'));
    }
}
