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
use Hans2103\AgentReady\Model\Markdown\MarkdownRenderer;
use Hans2103\AgentReady\Model\Settings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end HTML to Markdown: PageExtractor followed by MarkdownRenderer, with the default
 * remove selectors.
 */
final class ConversionCasesTest extends TestCase
{
    /**
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function cases(): array
    {
        return [
            'headings and paragraphs' => [
                '<h1>Titel</h1><p>Een alinea met <strong>vet</strong> en <em>schuin</em>.</p><h2>Kop twee</h2><p>Tweede   alinea.</p>',
                "# Titel\n\nEen alinea met **vet** en *schuin*.\n\n## Kop twee\n\nTweede alinea.",
            ],
            'links absolute' => [
                '<p>Lees <a href="/over-ons">over ons</a> of <a href="https://example.org/a">extern</a>.</p>',
                'Lees [over ons](https://shop.example.com/over-ons) of [extern](https://example.org/a).',
            ],
            'mailto kept, anchor and javascript links become text' => [
                '<p><a href="mailto:info@example.com">Mail</a> en <a href="#top">naar boven</a> of <a href="javascript:void(0)">menu</a></p>',
                '[Mail](mailto:info@example.com) en naar boven of menu',
            ],
            'noise removed' => [
                '<script>alert(1)</script><style>p{}</style><p>Zichtbaar</p><p hidden>Weg</p><span aria-hidden="true">icoon</span><svg><title>x</title></svg><form><input name="q"></form><div class="toolbar">Sorteer</div>',
                'Zichtbaar',
            ],
            'image with alt kept, decorative image dropped' => [
                '<p><img src="/media/a.jpg" alt="Zwarte stoel"></p><p><img src="/media/deco.png" alt="">Tekst</p>',
                "![Zwarte stoel](https://shop.example.com/media/a.jpg)\n\nTekst",
            ],
            'br is a hard line break' => [
                '<blockquote><p>Citaat<br>regel twee</p></blockquote><p>Adres<br>Postcode</p>',
                "> Citaat  \n> regel twee\n\nAdres  \nPostcode",
            ],
            'table' => [
                '<table><thead><tr><th>Maat</th><th>Prijs</th></tr></thead><tbody><tr><td>M</td><td>€ 10,00</td></tr></tbody></table>',
                "| Maat | Prijs |\n|---|---|\n| M | € 10,00 |",
            ],
            'clickable product card becomes one link' => [
                '<a href="/stoel.html"><h3>Stoel</h3><p>Van eikenhout.</p></a>',
                '[Stoel Van eikenhout.](https://shop.example.com/stoel.html)',
            ],
            'name and job title get a space, avatar gone' => [
                '<ul><li><span aria-hidden="true">JJ</span><span><strong>Jan Jansen</strong><span>Inkoper</span></span></li></ul>',
                '- **Jan Jansen** Inkoper',
            ],
            'text glued to an element stays glued' => ['<p>Een <strong>vet</strong>woord</p>', 'Een **vet**woord'],
            'markdown characters escaped'          => ['<p>Prijs *korting* en 1_2</p>', 'Prijs \\*korting\\* en 1\\_2'],
            'JSON-LD inside main is no text'       => ['<script type="application/ld+json">{"@type":"Product"}</script><p>Inhoud</p>', 'Inhoud'],
            'nested list' => [
                '<ul><li>een</li><li>twee<ul><li>genest</li></ul></li></ul><ol><li>eerst</li><li>dan</li></ol>',
                "- een\n- twee\n    - genest\n\n1. eerst\n2. dan",
            ],
        ];
    }

    #[DataProvider('cases')]
    public function testConversion(string $html, string $expected): void
    {
        $page = PageExtractor::extract('<html><body><main>' . $html . '</main></body></html>', 'main, body', Settings::DEFAULT_REMOVE_SELECTORS, 'https://shop.example.com/');

        self::assertNotNull($page);
        self::assertSame($expected, rtrim(MarkdownRenderer::render($page, false, false), "\n"));
    }
}
