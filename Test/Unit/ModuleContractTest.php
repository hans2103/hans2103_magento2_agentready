<?php

/**
 * Agent Ready for Magento 2
 *
 * @copyright  (C) 2026 Hans Kuijpers <https://hkweb.nl>
 * @license    GPL-2.0-or-later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Hans2103\AgentReady\Test\Unit;

use Hans2103\AgentReady\Model\Settings;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * Every setting exists in all places: a default in etc/config.xml, a field in
 * etc/adminhtml/system.xml, a read in Model/Config.php, and a translation for each label and
 * comment in both i18n files. The defaults match the defaults of Settings.
 */
final class ModuleContractTest extends TestCase
{
    private static function root(): string
    {
        return \dirname(__DIR__, 2);
    }

    private static function xml(string $file): SimpleXMLElement
    {
        $xml = simplexml_load_file(self::root() . '/' . $file);
        self::assertInstanceOf(SimpleXMLElement::class, $xml, $file);

        return $xml;
    }

    /**
     * group/field => default value from etc/config.xml.
     *
     * @return  array<string, string>
     */
    private static function defaults(): array
    {
        $defaults = [];

        foreach (self::xml('etc/config.xml')->default->agentready->children() as $group) {
            foreach ($group->children() as $field) {
                $defaults[$group->getName() . '/' . $field->getName()] = (string) $field;
            }
        }

        return $defaults;
    }

    public function testSettingsAreDeclaredReadAndTranslated(): void
    {
        $defaults = self::defaults();
        $fields   = [];
        $strings  = [];

        foreach (self::xml('etc/adminhtml/system.xml')->xpath('//section[@id="agentready"]/group') as $group) {
            $strings[] = trim((string) $group->label);
            $strings[] = trim((string) $group->comment);

            foreach ($group->field as $field) {
                $fields[]  = $group['id'] . '/' . $field['id'];
                $strings[] = trim((string) $field->label);
                $strings[] = trim((string) $field->comment);
            }
        }

        sort($fields);
        $declared = array_keys($defaults);
        sort($declared);
        self::assertSame($declared, $fields, 'config.xml defaults and system.xml fields');

        $config = (string) file_get_contents(self::root() . '/Model/Config.php');

        foreach ($fields as $path) {
            self::assertStringContainsString("'" . $path . "'", $config, "Model/Config.php reads $path");
        }

        $strings = array_values(array_filter(array_unique($strings), static fn (string $s): bool => $s !== ''));

        foreach (['en_US', 'nl_NL'] as $locale) {
            $translations = [];
            $handle       = fopen(self::root() . "/i18n/$locale.csv", 'r');
            self::assertIsResource($handle);

            while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                self::assertCount(2, $row, "$locale: two columns");
                $translations[$row[0]] = $row[1];
            }

            fclose($handle);

            foreach ($strings as $string) {
                self::assertArrayHasKey($string, $translations, "$locale translates: $string");
            }
        }
    }

    public function testDefaultsMatchSettings(): void
    {
        $defaults = self::defaults();
        $settings = new Settings();

        self::assertSame(Settings::DEFAULT_CONTENT_SELECTOR, $defaults['markdown/content_selector']);
        self::assertSame(Settings::DEFAULT_REMOVE_SELECTORS, $defaults['markdown/remove_selectors']);

        $map = [
            'markdown/enabled'        => $settings->markdown,
            'markdown/frontmatter'    => $settings->frontmatter,
            'markdown/jsonld'         => $settings->jsonLd,
            'links/enabled'           => $settings->linkHeaders,
            'links/llms'              => $settings->linkLlms,
            'links/agent_skills'      => $settings->linkAgentSkills,
            'links/ai_catalog'        => $settings->linkAiCatalog,
            'links/mcp_server_card'   => $settings->linkMcpServerCard,
            'links/sitemap_url'       => $settings->sitemapUrl,
            'links/markdown'          => $settings->linkMarkdown,
            'advanced/skip_logged_in' => $settings->skipLoggedIn,
            'advanced/skip_noindex'   => $settings->skipNoindex,
            'advanced/skip_actions'   => $settings->skipActions,
            'advanced/vary_html'      => $settings->varyHtml,
            'advanced/content_signal' => $settings->contentSignal,
        ];

        foreach ($map as $path => $value) {
            self::assertSame(\is_bool($value) ? ($value ? '1' : '0') : $value, $defaults[$path], $path);
        }
    }
}
