<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Observer;

use Hans2103\AgentReady\Model\Config;
use Hans2103\AgentReady\Model\DiscoveryFiles;
use Hans2103\AgentReady\Model\DiscoveryLinks;
use Hans2103\AgentReady\Model\Response\PageResponse;
use Hans2103\AgentReady\Model\Response\ResponseProcessor;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

/**
 * controller_front_send_response_before (front end): turns the HTML page into Markdown for a
 * request with Accept: text/markdown, and adds Vary: Accept and the agent discovery Link headers
 * to HTML pages. This event also runs for a page the built-in page cache serves, so the cache
 * keeps storing HTML and the Markdown is made from it on the way out.
 *
 * @since  26.48.0
 */
class ProcessResponse implements ObserverInterface
{
    /**
     * HTTP context key Magento_Customer sets for a logged-in customer.
     *
     * @since  26.48.0
     */
    private const CONTEXT_AUTH = 'customer_logged_in';

    /**
     * @param   Config           $config       The module settings
     * @param   DiscoveryFiles   $files        The discovery files in the web root
     * @param   HttpContext      $httpContext  The HTTP context (logged-in state)
     * @param   UrlInterface     $url          The URL builder (current URL)
     * @param   LoggerInterface  $logger       The logger
     *
     * @since   26.48.0
     */
    public function __construct(
        private readonly Config $config,
        private readonly DiscoveryFiles $files,
        private readonly HttpContext $httpContext,
        private readonly UrlInterface $url,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param   Observer  $observer  The event observer
     *
     * @return  void
     *
     * @since   26.48.0
     */
    public function execute(Observer $observer): void
    {
        $request  = $observer->getEvent()->getData('request');
        $response = $observer->getEvent()->getData('response');

        if (!$request instanceof HttpRequest || !$response instanceof HttpResponse) {
            return;
        }

        $settings = $this->config->settings();

        if (!$settings->markdown && !$settings->linkHeaders) {
            return;
        }

        $pageUrl = $this->url->getCurrentUrl();
        $page    = new PageResponse(
            (string) $request->getMethod(),
            (int) $response->getStatusCode(),
            (string) $request->getServer('HTTP_ACCEPT', ''),
            (string) $response->getBody(),
            $this->headers($response),
            $pageUrl,
            (bool) $this->httpContext->getValue(self::CONTEXT_AUTH),
        );

        if (!ResponseProcessor::isHtmlPage($page)) {
            return;
        }

        $links   = DiscoveryLinks::build($settings, $this->files->baseUrl(), $pageUrl, $this->files->exists(...));
        $changes = ResponseProcessor::process($settings, $page, $links);

        if ($changes === null) {
            return;
        }

        if ($changes->error !== null) {
            $this->logger->warning('[Agent Ready] ' . $changes->error);
        }

        foreach ($changes->remove as $name) {
            $response->clearHeader($name);
        }

        foreach ($changes->set as $name => $value) {
            $response->setHeader($name, $value, true);
        }

        if ($changes->body !== null) {
            $response->setBody($changes->body);
        }
    }

    /**
     * The response headers as lower-case name => values.
     *
     * @param   HttpResponse  $response  The response
     *
     * @return  array<string, list<string>>
     *
     * @since   26.48.0
     */
    private function headers(HttpResponse $response): array
    {
        $headers = [];

        foreach ($response->getHeaders() as $header) {
            $headers[strtolower($header->getFieldName())][] = (string) $header->getFieldValue();
        }

        return $headers;
    }
}
