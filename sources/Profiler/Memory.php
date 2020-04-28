<?php

/**
 * @brief       Memory Active Record
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler;

use IPS\Settings;
use IPS\Theme;
use function count;
use function defined;
use function floor;
use function header;
use function json_encode;
use function log;
use function memory_get_usage;
use function round;
use function time;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _Memory
 *
 * @package IPS\toolbox\Profiler
 * @mixin Memory
 */
class _Memory
{

    protected static $store = [];

    /**
     * start time
     *
     * @var null
     */
    protected $start = [];

    public function __construct()
    {

        $this->start = memory_get_usage();
    }

    /**
     * @throws \UnexpectedValueException
     */
    public static function build()
    {

        $list = [];
        /* @var Memory $obj */
        foreach ( static::$store as $obj ) {
            $list[ $obj[ 'name' ] ] = [
                'url'   => $obj[ 'key' ],
                'name'  => $obj[ 'name' ],
                'extra' => ' : ' . $obj[ 'log' ],
            ];
        }

        $count = count( $list ) ?: \null;
        $total = static::total();

        return Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( $total, 'memory', 'Memory Total.', $list, json_encode( $list ), $count, 'microchip', \true, \false );
    }

    /**
     * @return string
     */
    protected static function total(): string
    {

        return static::formatBytes( memory_get_usage() );
    }

    /**
     * @param     $size
     * @param int $precision
     *
     * @return string
     */
    public static function formatBytes( $size, $precision = 2, $suffix = true ): string
    {

        $base = log( $size, 1024 );
        $suffixes = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
        $expo = 1024 ** ( $base - floor( $base ) );
        $mem = round( $expo, $precision );
        if ( $suffix === true ) {
            $suffix = (int)floor( $base );
            $mem .= ' ' . $suffixes[ $suffix ];
        }

        return $mem;
    }

    public function endWithNoSuffix()
    {

        $end = memory_get_usage();
        $memEnd = $end - $this->start;

        return static::formatBytes( $memEnd );
    }

    public function end( $key = \null, $name = \null ): string
    {

        $end = memory_get_usage();
        $memEnd = $end - $this->start;
        $mem = static::formatBytes( $memEnd );

        if ( $mem === 'NAN B' ) {
            $mem = '> 1 B';
        }

        if ( $key !== \null && Settings::i()->dtprofiler_enabled_memory_summary ) {
            static::$store[] = [
                'name' => $name,
                'key'  => $key,
                'log'  => $mem,
                'time' => time(),
            ];
        }

        return $mem;
    }
}
