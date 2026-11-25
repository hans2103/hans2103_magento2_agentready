<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Model\Config\Source;

use Hans2103\AgentReady\Model\DiscoveryLinks;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options for a discovery file link: Auto (when the file exists in pub/), Yes, No.
 *
 * @since  26.48.0
 */
class LinkMode implements OptionSourceInterface
{
    /**
     * @return  list<array{value: string, label: \Magento\Framework\Phrase}>
     *
     * @since   26.48.0
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => DiscoveryLinks::MODE_AUTO, 'label' => __('Auto (when the file exists in pub/)')],
            ['value' => DiscoveryLinks::MODE_YES, 'label' => __('Yes')],
            ['value' => DiscoveryLinks::MODE_NO, 'label' => __('No')],
        ];
    }
}
