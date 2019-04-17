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

use IPS\Log;
use IPS\toolbox\Profiler\Profiler\Debug;
use function class_exists;
use function defined;
use function header;
use function method_exists;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

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
        if ( defined( '\DTPROFILER' ) && \DTPROFILER && class_exists( Debug::class ) ) {
            $class = Debug::class;
            if ( method_exists( $class, $method ) ) {
                $class::{$method}( ...$args );
            }
        }
        else if ( $method === 'add' ) {
            [ $message, $key, ] = $args;
            Log::debug( $message, $key );
        }
    }


}

