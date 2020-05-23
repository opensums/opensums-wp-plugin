<?php
/**
 * Class SampleTest
 *
 * @package Opensums_Wp_Plugin
 */
namespace UnitTest\OpenSumsWpPlugin;

use PHPUnit\Framework\TestCase;

use OpenSumsWpPlugin\Config;

// require_once('vendor/autoload.php');

/**
 * Sample test case.
 */
class ConfigTest extends TestCase {

    /**
     * A single example test.
     */
    public function test_should_give_plugin_information() {
        $config = Config::instance();
        $this->assertEquals('opensums-wp-plugin', $config->pluginName);
        $this->assertTrue(version_compare($config->version, '1.0.0-dev', '>='));
    }
}
