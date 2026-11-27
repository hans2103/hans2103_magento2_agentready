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
 * The body of a HEAD response, kept from just before Magento empties it.
 *
 * Magento\Framework\App\Http::launch() clears the body of a HEAD request with status 200 before
 * controller_front_send_response_before runs, so the observer would never see the HTML. The
 * KeepHeadBody plugin stores it here, and the observer takes it back. One instance per request.
 *
 * @since  26.48.4
 */
final class HeadBody
{
    /**
     * @var    string|null
     * @since  26.48.4
     */
    private ?string $body = null;

    /**
     * Keeps the body.
     *
     * @param   string  $body  The body Magento is about to remove
     *
     * @return  void
     *
     * @since   26.48.4
     */
    public function keep(string $body): void
    {
        $this->body = $body;
    }

    /**
     * The kept body, once; null when there is none.
     *
     * @return  string|null
     *
     * @since   26.48.4
     */
    public function take(): ?string
    {
        $body       = $this->body;
        $this->body = null;

        return $body;
    }
}
