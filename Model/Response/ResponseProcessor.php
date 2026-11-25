<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model\Response;

use Hans2103\AgentReady\Model\Html\PageExtractor;
use Hans2103\AgentReady\Model\Http\LinkHeader;
use Hans2103\AgentReady\Model\Http\MarkdownHeaders;
use Hans2103\AgentReady\Model\Http\VaryHeader;
use Hans2103\AgentReady\Model\Markdown\MarkdownRenderer;
use Hans2103\AgentReady\Model\Negotiation\AcceptHeader;
use Hans2103\AgentReady\Model\Settings;

/**
 * Decides what happens to a front-end response, without touching Magento:
 *
 * - a request that prefers text/markdown gets the page as Markdown (when allowed and possible);
 * - other HTML pages get Vary: Accept and the agent discovery Link headers.
 *
 * Only GET and HEAD requests for an HTML page with status 200 are touched.
 *
 * @since  26.48.0
 */
final class ResponseProcessor
{
    /**
     * The changes for this response, or null when it is no HTML page (leave it alone).
     *
     * @param   Settings      $settings  The store view settings
     * @param   PageResponse  $page      The response
     * @param   list<string>  $links     Link values from DiscoveryLinks::build()
     *
     * @return  ResponseChanges|null
     *
     * @since   26.48.0
     */
    public static function process(Settings $settings, PageResponse $page, array $links): ?ResponseChanges
    {
        if (!self::isHtmlPage($page)) {
            return null;
        }

        $set   = [];
        $error = null;

        if ($settings->markdown) {
            if (AcceptHeader::prefersMarkdown($page->accept) && self::mayConvert($settings, $page)) {
                try {
                    $markdown = self::convert($settings, $page);
                } catch (\Throwable $e) {
                    $markdown = '';
                    $error    = 'Markdown conversion failed for ' . $page->pageUrl . ': ' . $e->getMessage();
                }

                if ($markdown !== '') {
                    return self::markdownChanges($settings, $page, $markdown);
                }
            }

            if ($settings->varyHtml) {
                $set['Vary'] = VaryHeader::merge($page->header('Vary'));
            }
        }

        if ($settings->linkHeaders && $links !== []) {
            $set['Link'] = LinkHeader::merge($page->header('Link'), $links);
        }

        return new ResponseChanges(null, $set, [], $error);
    }

    /**
     * A GET or HEAD request, status 200 and an HTML body (Content-Type text/html or none).
     *
     * @param   PageResponse  $page  The response
     *
     * @return  boolean
     *
     * @since   26.48.0
     */
    public static function isHtmlPage(PageResponse $page): bool
    {
        if (!\in_array(strtoupper($page->method), ['GET', 'HEAD'], true) || $page->status !== 200) {
            return false;
        }

        foreach ($page->header('Content-Type') as $type) {
            if (stripos(trim($type), 'text/html') !== 0) {
                return false;
            }
        }

        return stripos($page->body, '<html') !== false;
    }

    /**
     * Whether a page has noindex (or none) in its robots meta tag or X-Robots-Tag header.
     *
     * @param   PageResponse  $page  The response
     *
     * @return  boolean
     *
     * @since   26.48.0
     */
    public static function isNoindex(PageResponse $page): bool
    {
        $robots = $page->header('X-Robots-Tag');

        if (preg_match_all('/<meta\b[^>]*>/i', $page->body, $tags) > 0) {
            foreach ($tags[0] as $tag) {
                if (preg_match('/\bname\s*=\s*["\']?robots["\'\s\/>]/i', $tag) === 1
                    && preg_match('/\bcontent\s*=\s*(["\'])(.*?)\1/i', $tag, $content) === 1) {
                    $robots[] = $content[2];
                }
            }
        }

        return preg_match('/\b(noindex|none)\b/i', implode(',', $robots)) === 1;
    }

    /**
     * The settings that keep a page out of the Markdown version: logged-in customers, noindex
     * and a body that is too large.
     *
     * @param   Settings      $settings  The store view settings
     * @param   PageResponse  $page      The response
     *
     * @return  boolean
     *
     * @since   26.48.0
     */
    private static function mayConvert(Settings $settings, PageResponse $page): bool
    {
        if ($settings->skipLoggedIn && $page->loggedIn) {
            return false;
        }

        if ($settings->skipNoindex && self::isNoindex($page)) {
            return false;
        }

        return MarkdownHeaders::fits($page->body);
    }

    /**
     * The page as Markdown, '' when there is no content to convert.
     *
     * @param   Settings      $settings  The store view settings
     * @param   PageResponse  $page      The response
     *
     * @return  string
     *
     * @since   26.48.0
     */
    private static function convert(Settings $settings, PageResponse $page): string
    {
        $extracted = PageExtractor::extract($page->body, $settings->contentSelector(), $settings->removeSelectors, $page->pageUrl);

        return $extracted === null ? '' : MarkdownRenderer::render($extracted, $settings->frontmatter, $settings->jsonLd);
    }

    /**
     * The changes for a Markdown answer: new body, Content-Type, token estimate, Vary, an optional
     * Content-Signal, and without the headers that describe the HTML body.
     *
     * @param   Settings      $settings  The store view settings
     * @param   PageResponse  $page      The response
     * @param   string        $markdown  The Markdown document
     *
     * @return  ResponseChanges
     *
     * @since   26.48.0
     */
    private static function markdownChanges(Settings $settings, PageResponse $page, string $markdown): ResponseChanges
    {
        $set = [
            'Content-Type'      => 'text/markdown; charset=utf-8',
            'x-markdown-tokens' => (string) MarkdownRenderer::estimateTokens($markdown),
            'Vary'              => VaryHeader::merge($page->header('Vary')),
        ];

        $signal = MarkdownHeaders::contentSignal($page->header('Content-Signal'), $settings->contentSignal);

        if ($signal !== null) {
            $set['Content-Signal'] = $signal;
        }

        return new ResponseChanges($markdown, $set, MarkdownHeaders::STRIP);
    }
}
