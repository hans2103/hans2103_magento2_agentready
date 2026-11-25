<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Where the discovery files live: the web root (pub/, or the Magento root when that is the
 * document root) and the base URL they are served from.
 *
 * @since  26.48.0
 */
class DiscoveryFiles
{
    /**
     * @var    array<string, bool>
     * @since  26.48.0
     */
    private array $exists = [];

    /**
     * @param   Filesystem             $filesystem    The filesystem
     * @param   StoreManagerInterface  $storeManager  The store manager
     *
     * @since   26.48.0
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * Whether a file exists in pub/ or in the Magento root.
     *
     * @param   string  $path  Path relative to the web root
     *
     * @return  boolean
     *
     * @since   26.48.0
     */
    public function exists(string $path): bool
    {
        return $this->exists[$path] ??= $this->filesystem->getDirectoryRead(DirectoryList::PUB)->isFile($path)
            || $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->isFile($path);
    }

    /**
     * The base URL of the web root for the current store view, with trailing slash.
     *
     * @return  string
     *
     * @since   26.48.0
     */
    public function baseUrl(): string
    {
        return rtrim((string) $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB), '/') . '/';
    }
}
