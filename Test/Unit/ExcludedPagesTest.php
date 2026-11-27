<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Response\ExcludedPages;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pages that always stay HTML, and the names a store view adds.
 */
final class ExcludedPagesTest extends TestCase
{
    /**
     * @return  array<string, array{string, string, bool}>
     */
    public static function cases(): array
    {
        return [
            'cart'                  => ['checkout_cart_index', '', true],
            'checkout'              => ['checkout_index_index', '', true],
            'success'               => ['checkout_onepage_success', '', true],
            'login'                 => ['customer_account_login', '', true],
            'account'               => ['customer_account_index', '', true],
            'order view'            => ['sales_order_view', '', true],
            'wishlist'              => ['wishlist_index_index', '', true],
            'contact'               => ['contact_index_index', '', true],
            'multishipping'         => ['multishipping_checkout_index', '', true],
            'upper case'            => ['Checkout_Cart_Index', '', true],
            'product'               => ['catalog_product_view', '', false],
            'category'              => ['catalog_category_view', '', false],
            'cms'                   => ['cms_page_view', '', false],
            'unknown'               => ['', 'cms_*', false],
            'no prefix match'       => ['customerx_index_index', '', false],
            'extra exact'           => ['cms_page_view', "catalogsearch_result_index,\n cms_page_view", true],
            'extra exact only'      => ['cms_page_viewx', 'cms_page_view', false],
            'extra prefix'          => ['review_product_list', 'review_*', true],
            'lone star is ignored'  => ['catalog_product_view', '*', false],
        ];
    }

    #[DataProvider('cases')]
    public function testMatches(string $action, string $extra, bool $expected): void
    {
        self::assertSame($expected, ExcludedPages::matches($action, $extra));
    }
}
