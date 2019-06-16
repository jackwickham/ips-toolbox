<?php
/**
 * @brief      Db Class
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Proxy\Generator;

use Exception;
use IPS\Data\Store;
use IPS\Db;
use function array_values;
use function header;
use function str_replace;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Db Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Db
 */
class _Db extends GeneratorAbstract
{

    /**
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
                'IPS\\Db::select:1',
                'IPS\\Db::insert:0',
                'IPS\\Db::delete:0',
                'IPS\\Db::update:0',
                'IPS\\Db::replace:0',
                'IPS\\Db::checkForTable:0',
                'IPS\\Db::createTable:0',
                'IPS\\Db::duplicateTableStructure:0',
                'IPS\\Db::renameTable:0',
                'IPS\\Db::alterTable:0',
                'IPS\\Db::dropTable:0',
                'IPS\\Db::getTableDefinition:0',
                'IPS\\Db::addColumn:0',
                'IPS\\Db::changeColumn:0',
                'IPS\\Db::dropColumn:0',
                'IPS\\Helpers\\Table\\Db::__construct:0',
            ],
            'provider'  => 'database',
            'language'  => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'   => 'database',
            'source' => [
                'contributor' => 'return_array',
                'parameter'   => 'dtProxy\\DatabaseProvider::get',
            ],
        ];

        Store::i()->dt_json = $jsonMeta;

        $this->buildDbClass();
    }

    /**
     *
     */
    public function buildDbClass()
    {
        try {
            $tables = Db::i()->query( 'SHOW TABLES' );
        } catch ( Exception $e ) {
            $tables = [];
        }

        $toWrite = [];

        foreach ( $tables as $table ) {
            $foo = array_values( $table );
            $toWrite[] = str_replace( Db::i()->prefix, '', $foo[ 0 ] );
        }

        $this->writeClass( 'Databases', 'DatabaseProvider', $toWrite );
    }
}

