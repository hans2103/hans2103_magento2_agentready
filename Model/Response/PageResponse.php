<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model\Response;

/**
 * A front-end response as plain values: what ResponseProcessor needs to decide.
 *
 * @since  26.48.0
 */
final readonly class PageResponse
{
    /**
     * @param   string                       $method    Request method
     * @param   integer                      $status    Response status code
     * @param   string                       $accept    Raw Accept header of the request
     * @param   string                       $body      Response body
     * @param   array<string, list<string>>  $headers   Response headers: lower-case name => values
     * @param   string                       $pageUrl   Absolute URL of the page
     * @param   boolean                      $loggedIn  Whether a customer is logged in
     * @param   string                       $actionName  Full action name of the request ('' = unknown)
     *
     * @since   26.48.0
     */
    public function __construct(
        public string $method,
        public int $status,
        public string $accept,
        public string $body,
        public array $headers,
        public string $pageUrl,
        public bool $loggedIn = false,
        public string $actionName = '',
    ) {
    }

    /**
     * The values of a response header (case-insensitive name).
     *
     * @param   string  $name  The header name
     *
     * @return  list<string>
     *
     * @since   26.48.0
     */
    public function header(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }
}
