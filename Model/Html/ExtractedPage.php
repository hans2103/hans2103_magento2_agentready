<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model\Html;

/**
 * What PageExtractor takes from an HTML page: metadata, the cleaned main content and JSON-LD.
 *
 * @since  26.48.0
 */
final readonly class ExtractedPage
{
    /**
     * @param   array<string, string>  $meta         title, description and image; only keys with a value
     * @param   string                 $contentHtml  Inner HTML of the main content, cleaned
     * @param   list<string>           $jsonLd       Trimmed contents of each application/ld+json script
     *
     * @since  26.48.0
     */
    public function __construct(
        public array $meta,
        public string $contentHtml,
        public array $jsonLd,
    ) {
    }
}
