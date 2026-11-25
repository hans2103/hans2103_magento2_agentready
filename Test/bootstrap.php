<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

/*
 * Bootstrap for the unit tests: they cover the classes that do not use Magento, so no Magento
 * install is needed. PHPUnit's own autoloader brings league/html-to-markdown when both are
 * installed in one Composer project (see README); a vendor/ folder in the module also works.
 */

$vendor = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($vendor)) {
    require $vendor;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Hans2103\\AgentReady\\';

    if (str_starts_with($class, $prefix)) {
        $file = dirname(__DIR__) . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
});
