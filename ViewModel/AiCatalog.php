<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\ViewModel;

use Hans2103\AgentReady\Model\Config;
use Hans2103\AgentReady\Model\DiscoveryFiles;
use Hans2103\AgentReady\Model\DiscoveryLinks;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * The <link rel="ai-catalog"> in the HTML head: the ARD discovery reads the manifest from the
 * page, the Link header alone is not enough for it.
 *
 * @since  26.48.0
 */
class AiCatalog implements ArgumentInterface
{
    /**
     * @param   Config          $config  The module settings
     * @param   DiscoveryFiles  $files   The discovery files in the web root
     *
     * @since   26.48.0
     */
    public function __construct(
        private readonly Config $config,
        private readonly DiscoveryFiles $files,
    ) {
    }

    /**
     * The manifest URL, or null when it is not linked.
     *
     * @return  string|null
     *
     * @since   26.48.0
     */
    public function getHref(): ?string
    {
        return DiscoveryLinks::aiCatalogUrl($this->config->settings(), $this->files->baseUrl(), $this->files->exists(...));
    }
}
