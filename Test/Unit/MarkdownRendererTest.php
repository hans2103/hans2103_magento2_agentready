<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Html\ExtractedPage;
use Hans2103\AgentReady\Model\Markdown\MarkdownRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Markdown document layout (frontmatter, body, JSON-LD), tidy() and the token estimate.
 */
final class MarkdownRendererTest extends TestCase
{
    private static function page(): ExtractedPage
    {
        $html = '<h1>Café</h1><p>Text with <strong>bold</strong> and <a href="https://example.com/x">a link</a>.</p>'
            . '<ul><li>One</li><li>Two<ul><li>Nested</li></ul></li></ul>'
            . '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>'
            . '<section><div><span>Wrapped</span></div></section>';

        return new ExtractedPage(['title' => 'Say "hi" — ok', 'description' => 'D'], $html, ['{"@type":"A"}', '{"@type":"B"}']);
    }

    public function testDocument(): void
    {
        $md = MarkdownRenderer::render(self::page());

        self::assertStringStartsWith("---\ntitle: \"Say \\\"hi\\\" — ok\"\ndescription: \"D\"\n---\n\n# Café\n", $md);
        self::assertStringContainsString('**bold**', $md);
        self::assertStringContainsString('[a link](https://example.com/x)', $md);
        self::assertStringContainsString("- One\n- Two\n    - Nested", $md);
        self::assertStringContainsString('| A | B |', $md);
        self::assertStringContainsString('Wrapped', $md);
        self::assertStringNotContainsString('<section>', $md);
        self::assertStringNotContainsString('<span>', $md);
        self::assertStringEndsWith("```json\n{\"@type\":\"A\"}\n{\"@type\":\"B\"}\n```\n", $md);
        self::assertStringEndsNotWith("\n\n", $md);
    }

    public function testOptions(): void
    {
        self::assertSame("x\n", MarkdownRenderer::render(new ExtractedPage([], '<p>x</p>', [])));
        self::assertStringStartsWith('# Café', MarkdownRenderer::render(self::page(), false, true));
        self::assertStringNotContainsString('```json', MarkdownRenderer::render(self::page(), true, false));
        self::assertSame('', MarkdownRenderer::render(new ExtractedPage(['title' => 'T'], '', ['{}'])));
        self::assertSame('', MarkdownRenderer::render(new ExtractedPage(['title' => 'T'], "<div> \n </div>", [])));
    }

    public function testTokens(): void
    {
        self::assertSame(2, MarkdownRenderer::estimateTokens('abcde'));
        self::assertSame(1, MarkdownRenderer::estimateTokens('éééé'), 'characters, not bytes');
        self::assertSame(0, MarkdownRenderer::estimateTokens(''));
    }

    /**
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function tidyCases(): array
    {
        return [
            'whitespace-only lines and blank runs' => ["a\n \n  \n\n\nb", "a\n\nb"],
            'stray indentation and trailing space' => [" [x](y) \n   z", "[x](y)\nz"],
            'one space after heading marks'        => ["#  How it works\n##   Two", "# How it works\n## Two"],
            'four-space nesting kept'              => ["- a\n    - b", "- a\n    - b"],
            'deep indentation outside lists gone'  => ["Price\n\n     € 7,49\n\t\tIn stock", "Price\n\n€ 7,49\nIn stock"],
            'list nesting kept after blank line'   => ["1. a\n\n    - b\n        - c", "1. a\n\n    - b\n        - c"],
            'indentation after a list ends gone'   => ["- a\n\nText\n\n     more", "- a\n\nText\n\nmore"],
            'empty links dropped'                  => ["Share [ ](https://x.test/a)[](https://x.test/b)[\t](https://x.test/c) here", "Share here"],
            'image without alt kept'               => ['![](https://x.test/a.jpg)', '![](https://x.test/a.jpg)'],
            'hard break kept'                      => ["Street 1  \nCity", "Street 1  \nCity"],
            'entities decoded, lt/gt kept'         => ['Sophie &amp; Jamie &rsquo; &#169; &lt;b&gt;', 'Sophie & Jamie ’ © &lt;b&gt;'],
            'fenced code untouched'                => ["```\n  &amp;  \n```", "```\n  &amp;  \n```"],
        ];
    }

    #[DataProvider('tidyCases')]
    public function testTidy(string $markdown, string $expected): void
    {
        self::assertSame($expected, MarkdownRenderer::tidy($markdown));
    }
}
