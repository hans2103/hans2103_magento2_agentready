<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Plugin;

use Hans2103\AgentReady\Model\Response\HeadBody;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * Before Magento clears the body of a HEAD response, keep it, so the observer can still tell an
 * HTML page and add the same headers as for GET.
 *
 * @since  26.48.4
 */
class KeepHeadBody
{
    /**
     * @param   HttpRequest  $request   The current request
     * @param   HeadBody     $headBody  Where the body is kept
     *
     * @since   26.48.4
     */
    public function __construct(
        private readonly HttpRequest $request,
        private readonly HeadBody $headBody,
    ) {
    }

    /**
     * @param   HttpResponse  $subject  The response
     *
     * @return  null
     *
     * @since   26.48.4
     */
    public function beforeClearBody(HttpResponse $subject)
    {
        if ($this->request->isHead()) {
            $body = (string) $subject->getBody();

            if ($body !== '') {
                $this->headBody->keep($body);
            }
        }

        return null;
    }
}
