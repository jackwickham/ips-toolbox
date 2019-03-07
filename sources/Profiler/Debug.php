<?php
/**
 * @brief      Debug Class
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Profiler;
use function class_exists;
use function method_exists;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

use function defined;
use function header;

/**
 * Debug Class
 *
 * @mixin \IPS\toolbox\Profiler\Debug
 */
class _Debug
{

    /**
     * _Debug constructor
     */
    public function __construct()
    {
    }

    public static function __callStatic( $method, $args )
    {
        if ( defined( '\DTPROFILER' ) && \DTPROFILER && class_exists( \IPS\tooblox\Profiler\Debug::class ) ) {
            $class = \IPS\tooblox\Profiler\Debug::class;
            if ( method_exists( $class, $method ) ) {
                $class::{$method}( ...$args );
            }
        }
        else if ( $method === 'add' ) {
            list( $message, $key, ) = $args;
            \IPS\Log::debug( $message, $key );
        }
    }


}

