<?php
/**
 * @brief      Applications Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Proxy\Generator;

use IPS\Application;
use IPS\Data\Store;
use function header;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Applications Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Applications
 */
class _Applications extends GeneratorAbstract
{
    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;

    /**
     * creates the jsonMeta for the json file and writes the provider to disk.
     */
    public function create()
    {
        $jsonMeta = [];
        if ( isset( Store::i()->dt_json ) ) {
            $jsonMeta = Store::i()->dt_json;
        }
        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                "IPS\\Application::load",
                "IPS\\Application::appIsEnabled",
                "IPS\\Email::buildFromTemplate:0",
                "IPS\\Application::appsWithExtension:0",
                'IPS\\Lang::saveCustom:0',
                'IPS\\Lang::copyCustom:0',
                'IPS\\Lang::copyCustom:3',
                'IPS\\Lang::deleteCustom:0',
                'IPS\\Theme::getTemplate:1',
                'IPS\\Application::extension:0',
                'IPS\\Application::allExtensions:0',
                'IPS\\Output::js:1',
                'IPS\\Output::css:1',
            ],
            'provider'  => 'appName',
            'language'  => 'php',
        ];
        $jsonMeta[ 'providers' ][] = [
            'name'   => 'appName',
            'source' => [
                'contributor' => 'return_array',
                'parameter'   => 'dtProxy\\AppNameProvider::get',
            ],
        ];

        Store::i()->dt_json = $jsonMeta;
        $apps = [];

        /**
         * @var Application $app
         */
        foreach ( Application::roots() as $app ) {
            $apps[] = $app->directory;
        }

        $this->writeClass( 'Applications', 'AppNameProvider', $apps );
    }
}

