<?php
/**
 * @brief      Templates Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */

namespace IPS\toolbox\Proxy\Generator;

use Exception;
use Generator\Builders\ClassGenerator;
use Generator\Tokenizers\StandardTokenizer;
use IPS\Data\Store;
use IPS\Log;
use IPS\Theme;
use IPS\toolbox\Application;
use IPS\toolbox\Content\Data;
use IPS\toolbox\Shared\Read;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function array_pop;
use function array_values;
use function count;
use function defined;
use function explode;
use function function_exists;
use function header;
use function ksort;
use function md5;
use function rand;
use function random_int;
use function str_replace;
use function time;
use const DIRECTORY_SEPARATOR;
use const IPS\ROOT_PATH;

Application::loadAutoLoader();

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Templates Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Templates
 */
class _Templates extends GeneratorAbstract
{

    use Read;

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

        $jsonMeta = Store::i()->dt_json ?? [];
        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                "IPS\\Theme::getTemplate:0",
            ],
            'provider'  => 'templateGroup',
            'language'  => 'php',
        ];
        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                "IPS\\Theme::getTemplate:2",
                'IPS\\Output::js:2',
                'IPS\\Output::css:2',
            ],
            'provider'  => 'templateLocation',
            'language'  => 'php',
        ];
        $jsonMeta[ 'providers' ][] = [
            'name'           => 'templateLocation',
            'lookup_strings' => [
                'admin',
                'front',
                'global',
            ],
        ];
        $jsonMeta[ 'registrar' ][] = [
            'signature'  => [
                'IPS\\Theme::getTemplate:0',
            ],
            'signatures' => [
                [
                    'class'  => Theme::class,
                    'method' => 'getTemplate',
                    'index'  => 0,
                    'type'   => 'type',
                ],

            ],
            'provider'   => 'templateClass',
            'language'   => 'php',
        ];

        $templates = Store::i()->dtproxy_templates ?? [];
        $tempStore = array_values( $templates );
        $jsonMeta[ 'providers' ][] = [
            'name'  => 'templateClass',
            'items' => $tempStore,
        ];
        Store::i()->dt_json = $jsonMeta;

    }

}

