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

use IPS\Theme;
use function count;
use function defined;
use function floor;
use function header;
use function json_encode;
use function log;
use function round;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _Memory
 *
 * @package IPS\toolbox\Profiler\Profiler
 * @mixin Time
 */
class _Time
{

    protected static $store = [];

    /**
     * start time
     *
     * @var null
     */
    protected $start;

    public function __construct()
    {

        $this->start = \microtime( \true );
    }

    /**
     * @param     $size
     * @param int $precision
     *
     * @return string
     */
    public static function formatBytes( $size, $precision = 2 ): string
    {

        $base = log( $size, 1024 );
        $suffixes = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
        $expo = 1024 ** ( $base - floor( $base ) );
        $suffix = (int)floor( $base );

        return round( $expo, $precision ) . ' ' . $suffixes[ $suffix ];
    }

    /**
     * @throws \UnexpectedValueException
     */
    public static function build()
    {

        if ( empty( static::$store ) ) {
            return \null;
        }

        $list = [];

        /* @var Memory $obj */
        foreach ( static::$store as $obj ) {
            $list[ $obj[ 'name' ] ] = [
                'url'   => $obj[ 'key' ],
                'name'  => $obj[ 'name' ],
                'extra' => ' : ' . round( $obj[ 'log' ], 6 ) * 1000 . 'ms',
            ];
        }

        $count = count( $list ) ?: \null;

        return Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'Executions', 'executions', 'Execution Times.', $list, json_encode( $list ), $count, 'clock-o', \true, \false );
    }

    public function endFormated()
    {

        return ' : ' . $this->endConvertedToMs() . 'ms';
    }

    public function endConvertedToMs()
    {

        $end = \microtime( true ) - $this->start;

        return round( $end, 6 ) * 1000;
    }

    public function end( $key = \null, $name = \null )
    {

        $end = \microtime( \true ) - $this->start;

        if ( $key !== \null ) {
            static::$store[] = [
                'name' => $name,
                'key'  => $key,
                'log'  => $end,
            ];
        }

        return $end;
    }
}
