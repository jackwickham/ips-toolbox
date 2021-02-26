<?php

/**
 * @brief       Debug Active Record
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler;

use Exception;
use IPS\Db;
use IPS\Log;
use IPS\Patterns\ActiveRecord;
use IPS\Settings;
use IPS\Theme;
use IPS\toolbox\Editor;
use SplFileInfo;
use UnexpectedValueException;

use function count;
use function defined;
use function get_class;
use function header;
use function htmlentities;
use function is_array;
use function json_decode;
use function json_encode;
use function method_exists;
use function nl2br;
use function time;

use function debug_backtrace;
use function log;
use function mb_substr;
use function str_replace;


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _Debug
 *
 * @package IPS\toolbox\Profiler
 * @mixin Debug
 */
class _Debug extends ActiveRecord
{

    use \IPS\toolbox\Shared\ActiveRecord;

    /**
     * @brief    [ActiveRecord] Database Prefix
     */
    public static $databasePrefix = 'debug_';

    /**
     * @brief    [ActiveRecord] Database table
     */
    public static $databaseTable = 'toolbox_debug';

    /**
     * @brief   Bitwise keys
     */
    public static $bitOptions = [
        'bitoptions' => [
            'bitoptions' => [],
        ],
    ];

    /**
     * @brief    [ActiveRecord] Multiton Store
     */
    protected static $multitons = [];

    public static function log( $message, $key = null )
    {

        static::add( $key, $message, true );
    }

    /**
     * adds a debug message to the log
     *
     * @param $key
     * @param $message
     */
    public static function add( $key, $message, $alias = false )
    {
        if( !Settings::i()->dtprofiler_enable_debug ){
            Log::debug($message, $key );
            return;
        }
        if (  !\IPS\QUERY_LOG ) {
            Log::debug($message, $key );
            return;
        }

        $debug = new static;
        $prev = null;
        $next = null;
        $bt = debug_backtrace();
        foreach ( $bt as $b ) {
            $file = str_replace( [ '/', '.' ], '', $b[ 'file' ] );
            if ( mb_substr( $file, -16 ) === 'ProfilerDebugphp' ) {
                continue;
            }

            if ( $prev === null ) {
                $prev = $b;
                continue;
            }

            if ( $prev !== null && $next === null ) {
                $next = $b;
                break;
            }
        }
        if ( $key === null ) {
            $next = $next ?? $prev;
            $key = $next[ 'function' ];
            if ( !$key ) {
                $file = new SplFileInfo( $next[ 'file' ] );
                $key = $file->getFilename();
            }
        }
        $debug->key = $key;
        $debug->path = $prev[ 'file' ];
        $debug->line = $prev[ 'line' ];
        if ( $message instanceof Exception ) {
            $data[ 'class' ] = get_class( $message );
            $data[ 'ecode' ] = $message->getCode();

            if ( method_exists( $message, 'extraLogData' ) ) {
                $data[ 'message' ] = $message->extraLogData() . "\n" . $message->getMessage();
            }
            else {
                $data[ 'message' ] = $message->getMessage();
            }

            $data[ 'backtrace' ] = nl2br( htmlentities( $message->getTraceAsString() ) );
            $type = 'exception';
            $message = json_encode( $data );
        }
        else if ( is_array( $message ) ) {
            $message = json_encode( $message );
            $type = 'array';
        }
        else {
            $type = 'string';
        }

        $debug->type = $type;
        $debug->log = $message;
        $debug->time = time();
        $debug->save();
    }

    public function save()
    {

        if ( Settings::i()->dtprofiler_enable_debug ) {
            parent::save();
        }
    }

    /**
     * @return null
     * @throws UnexpectedValueException
     */
    public static function build()
    {

        $iterators = static::all( [
            'where' => [ 'debug_ajax = ? AND debug_viewed = ?', 0, 0 ],
            'order' => 'debug_id DESC',
            'limit' => [ 0, 100 ],
        ] );
        $list = [];
        $last = 0;

        /* @var Debug $obj */
        foreach ( $iterators as $obj ) {
            $list[] = $obj->body();
            $obj->viewed();
            $last = $obj->id;
        }
        try {
            Db::i()->update( 'toolbox_debug', [ 'debug_viewed' => 1 ] );
        } catch ( Db\Exception $e ) {
        }

        $count = count( $list ) ?: 0;

        return Theme::i()->getTemplate( 'generic', 'toolbox', 'front' )->button( 'Debug', 'debug', 'List of debug messages', $list, $count, 'bug', \true, $count ? \false : \true, $last, \true );

    }

    /**
     * the body of the message
     *
     * @throws UnexpectedValueException
     */
    public function body(): string
    {

        if ( $this->type === 'exception' || $this->type === 'array' ) {
            $message = json_decode( $this->log, \true );
            $list = Theme::i()->getTemplate( 'generic', 'toolbox', 'front' )->keyvalueDebug( $this );
        }
        else {
            $list = Theme::i()->getTemplate( 'generic', 'toolbox', 'front' )->stringDebug( $this );
        }

        return $list;
    }

    /**
     * updates a debug viewed status
     */
    public function viewed()
    {

        $this->viewed = 1;
        $this->save();
    }

    public function get_messages()
    {

        return json_decode( $this->log, true );
    }

    /**
     * @return string
     */
    public function get_name(): string
    {

        return '#' . $this->_data[ 'id' ] . ' ' . $this->_data[ 'key' ];
    }

    public function url()
    {

        if ( $this->path !== null ) {
            return ( new Editor )->replace( $this->path, $this->line );
        }
    }
}
