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
 * Pages that never get a Markdown version, by full action name (route_controller_action):
 * cart, checkout, customer account, contact, wishlist and order pages always stay HTML, plus
 * the names a store view adds. A name ending in * matches every action that starts with it.
 *
 * @since  26.49.0
 */
final class ExcludedPages
{
    /**
     * Always excluded.
     *
     * @var    list<string>
     * @since  26.49.0
     */
    public const ALWAYS = [
        'checkout_*',
        'multishipping_*',
        'customer_*',
        'sales_*',
        'wishlist_*',
        'contact_*',
    ];

    /**
     * Whether a page is excluded.
     *
     * @param   string  $actionName  Full action name of the request ('' = unknown, not excluded)
     * @param   string  $extra       Names from the setting, separated by commas or new lines
     *
     * @return  boolean
     *
     * @since   26.49.0
     */
    public static function matches(string $actionName, string $extra = ''): bool
    {
        $actionName = strtolower(trim($actionName));

        if ($actionName === '') {
            return false;
        }

        foreach ([...self::ALWAYS, ...self::parse($extra)] as $pattern) {
            if (str_ends_with($pattern, '*')
                ? str_starts_with($actionName, substr($pattern, 0, -1))
                : $actionName === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * The names in a setting value, lower-case, without empty entries.
     *
     * @param   string  $value  Names separated by commas or new lines
     *
     * @return  list<string>
     *
     * @since   26.49.0
     */
    private static function parse(string $value): array
    {
        $names = preg_split('/[\s,]+/', strtolower($value)) ?: [];

        return array_values(array_filter($names, static fn (string $name): bool => $name !== '' && $name !== '*'));
    }
}
