<?php

/**
 * @brief       Caching Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler\Parsers;

use IPS\Data\Cache;
use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Patterns\Singleton;
use IPS\Theme;
use function count;
use function defined;
use function header;
use function json_encode;
use function sha1;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Caching extends Singleton
{

    /**
     * @brief   Singleton Instances
     * @note    This needs to be declared in any child class.
     */
    protected static $instance;

    /**
     * @var array
     */
    public $cache = [];

    /**
     * _Caching constructor.
     */
    public function __construct()
    {
        $this->cache = Cache::i()->log + Store::i()->log;
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function build(): string
    {

        $list = [];
        $hash = [];
        $caches = $this->cache;

        foreach ( $caches as list( $type, $name, $content, $bt ) ) {
            if ( $type === 'check' ) {
                continue;
            }

            $h = sha1( $type . $name );
            $hash[ $h ] = [ 'content' => $content, 'bt' => $bt ];
            $url = Url::internal( 'app=toolbox&module=bt&controller=bt', 'front' )->setQueryString( [
                'do' => 'cache',
                'bt' => $h,
            ] );
            $list[ $name ] = [ 'extra' => ' : ' . $type, 'name' => $name, 'url' => (string)$url, 'dialog' => \true ];
        }

        Store::i()->dtprofiler_bt_cache = $hash;

        return Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'Cache Log', 'CacheLog', 'Cache Log.', $list, json_encode( $list ), count( $list ), 'usd', \true, \false, \null, \true );
    }
}
