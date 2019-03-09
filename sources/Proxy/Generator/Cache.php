<?php
/**
 * @brief      Cache Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Proxy\Generator;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

use IPS\Patterns\Singleton;
use function defined;
use function file_get_contents;
use function file_put_contents;
use function header;
use function is_file;
use function json_decode;
use function json_encode;
use const IPS\ROOT_PATH;
use const JSON_PRETTY_PRINT;

/**
 * Cache Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Cache
 */
class _Cache extends Singleton
{

    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance = null;
    protected $path = ROOT_PATH . '/dtProxy/';


    public function addClass( $class )
    {
        $cs = $this->getClasses();
        $cs[ $class ] = $class;
        $this->setClasses( $cs );
    }

    public function getClasses()
    {
        $return = [];
        $classPath = $this->path . 'classes.json';
        if ( is_file( $classPath ) ) {
            $return = json_decode( file_get_contents( $classPath ), true );
        }
        return $return;
    }

    public function setClasses( $data )
    {

        $classPath = $this->path . 'classes.json';
        file_put_contents( $classPath, json_encode( $data, JSON_PRETTY_PRINT ) );
    }

    public function addNamespace( $namespace )
    {
        $ns = $this->getNamespaces();
        $ns[ $namespace ] = $namespace;
        $this->setNamespaces( $ns );
    }

    public function getNamespaces()
    {
        $return = [];
        $namespace = $this->path . 'namespace.json';
        if ( is_file( $namespace ) ) {
            $return = json_decode( file_get_contents( $namespace ), true );
        }
        return $return;
    }

    public function setNamespaces( $data )
    {

        $namespace = $this->path . 'namespace.json';
        file_put_contents( $namespace, json_encode( $data, JSON_PRETTY_PRINT ) );
    }
}

