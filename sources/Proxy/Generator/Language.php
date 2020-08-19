<?php
/**
 * @brief      Language Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Proxy\Generator;

use Exception;
use IPS\Data\Store;
use IPS\Lang;
use function header;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Language Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Language
 */
class _Language extends GeneratorAbstract
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
                'IPS\\Lang::addToStack:0',
                'IPS\\Lang::checkKeyExists',
                'IPS\\Lang::get',
                'IPS\\Lang::saveCustom:1',
                'IPS\\Lang::copyCustom:1',
                'IPS\\Lang::copyCustom:2',
                'IPS\\Lang::deleteCustom:1',
            ],
            'provider'  => 'langs',
            'language'  => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'   => 'langs',
            'source' => [
                'contributor' => 'return_array',
                'parameter'   => 'dtProxy\\LanguageProvider::get',
            ],
        ];

        Store::i()->dt_json = $jsonMeta;
        $toWrite = [];
        try {
            $lang = Lang::load( Lang::defaultLanguage() );

            foreach ( $lang->words as $key => $val ) {
                $toWrite[] = $key;
            }

            $this->writeClass( 'Langs', 'LanguageProvider', $toWrite );
        } catch ( Exception $e ) {
        }
    }
}

