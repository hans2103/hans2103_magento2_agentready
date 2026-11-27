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
 * What ResponseProcessor wants changed on a response.
 *
 * @since  26.48.0
 */
final readonly class ResponseChanges
{
    /**
     * @param   string|null            $body     The new body (Markdown), null = keep the HTML
     * @param   array<string, string>  $set      Headers to replace: name => value
     * @param   list<string>           $remove   Headers to remove
     * @param   string|null            $error    Why a conversion failed, for the log
     *
     * @since   26.48.0
     */
    public function __construct(
        public ?string $body = null,
        public array $set = [],
        public array $remove = [],
        public ?string $error = null,
    ) {
    }

    /**
     * The changes for a HEAD request: no body, but the headers of the body a GET would get, with
     * its Content-Length.
     *
     * @return  self
     *
     * @since   26.48.4
     */
    public function withoutBody(): self
    {
        if ($this->body === null) {
            return $this;
        }

        return new self(
            null,
            [...$this->set, 'Content-Length' => (string) \strlen($this->body)],
            array_values(array_diff($this->remove, ['Content-Length'])),
            $this->error,
        );
    }
}
