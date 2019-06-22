<?php

/**
 * @brief       Templates Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler\Parsers;

use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Patterns\Singleton;
use IPS\Settings;
use IPS\Theme;
use IPS\toolbox\Editor;
use function count;
use function defined;
use function explode;
use function header;
use function implode;
use function is_array;
use function json_decode;
use function json_encode;
use function ksort;
use function mb_strpos;
use function str_replace;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Templates extends Singleton
{

    /**
     * @brief   Singleton Instances
     * @note    This needs to be declared in any child class.
     */
    protected static $instance;

    /**
     * template store
     *
     * @var array|string
     */
    protected $templates = [];

    /**
     * css store
     *
     * @var array|string
     */
    protected $css = [];

    /**
     * js store
     *
     * @var array|string
     */
    protected $js = [];

    /**
     * jsVar store
     *
     * @var array|string
     */
    protected $jsVars = [];

    /**
     * Templates constructor.
     */
    public function __construct()
    {
        if ( isset( Store::i()->dtprofiler_templates ) ) {
            $this->templates = Store::i()->dtprofiler_templates;
        }

        if ( isset( Store::i()->dtprofiler_css ) ) {
            $this->css = Store::i()->dtprofiler_css;
        }

        if ( isset( Store::i()->dtprofiler_js ) ) {
            $this->js = Store::i()->dtprofiler_js;
        }

        if ( isset( Store::i()->dtprofiler_js_vars ) ) {
            $this->jsVars = Store::i()->dtprofiler_js_vars;
        }

        unset( Store::i()->dtprofiler_js_vars, Store::i()->dtprofiler_js, Store::i()->dtprofiler_css, Store::i()->dtprofiler_templates );

    }

    /**
     * builds the template button and data
     *
     * @return string
     * @throws \UnexpectedValueException
     */
    public function build(): string
    {
        $store = [];
        $this->buildTemplates( $store );
        $this->buildCss( $store );
        $this->buildJs( $store );
        $this->buildJsVars( $store );
        return implode( "\n", $store );
    }

    /**
     * builds the template button
     *
     * @param $store
     *
     * @throws \UnexpectedValueException
     */
    protected function buildTemplates( &$store )
    {
        if ( !Settings::i()->dtprofiler_enabled_templates ) {
            return;
        }
        $list = [];
        $templates = $this->templates;

        if ( !count( $templates ) ) {
            return;
        }

        foreach ( $templates as $template ) {
            if ( $template[ 'app' ] !== 'toolbox' ) {
                $path = \IPS\ROOT_PATH . '/applications/' . $template[ 'app' ] . '/dev/html/' . $template[ 'location' ] . '/' . $template[ 'group' ] . '/' . $template[ 'name' ] . '.phtml';
                $url = ( new Editor )->replace( $path );
                $name = $template[ 'app' ] . ' -> ' . $template[ 'group' ] . ' -> ' . $template[ 'name' ];
                $list[ $path ] = [ 'url' => $url, 'name' => $name ];
            }
        }

        ksort( $list );
        $store[ 'templates' ] = Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'Templates', 'Templates', 'list of loaded Template files.', $list, json_encode( $list ), count( $list ), 'code', \true, \false );
    }

    /**
     * build the css button
     *
     * @param $store
     *
     * @throws \UnexpectedValueException
     */
    protected function buildCss( &$store )
    {
        if ( !Settings::i()->dtprofiler_enabled_css ) {
            return;
        }

        $list = [];
        $css = $this->css;
        if ( !count( $css ) ) {
            return;
        }
        foreach ( $css as $c ) {
            $path = str_replace( Url::baseUrl( Url::PROTOCOL_RELATIVE ) . 'applications/core/interface/css/css.php?css=', '', $c );

            if ( mb_strpos( $path, ',' ) !== \false ) {
                $p = explode( ',', $path );
                foreach ( $p as $pc ) {
                    $url = ( new Editor )->replace( \IPS\ROOT_PATH . '/' . $pc );
                    $list[ $pc ] = [ 'url' => $url, 'name' => $pc ];
                }
            }
            else {
                $url = ( new Editor )->replace( \IPS\ROOT_PATH . '/' . $path );
                $list[ $path ] = [ 'url' => $url, 'name' => $path ];
            }


        }

        ksort( $list );
        $store[ 'css' ] = Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'CSS', 'CSS', 'list of loaded CSS files.', $list, json_encode( $list ), count( $list ), 'hashtag', \true, \false );
    }

    /**
     * build the js button
     *
     * @param $store
     *
     * @throws \UnexpectedValueException
     */
    protected function buildJs( &$store )
    {
        if ( !Settings::i()->dtprofiler_enabled_js ) {
            return;
        }

        $list = [];
        $js = $this->js;

        if ( !count( $js ) ) {
            return;
        }

        foreach ( $js as $c ) {
            $path = str_replace( Url::baseUrl( Url::PROTOCOL_RELATIVE ), '', $c );
            $url = ( new Editor )->replace( \IPS\ROOT_PATH . '/' . $path );
            $list[ $path ] = [ 'url' => $url, 'name' => $path ];
        }

        ksort( $list );
        $store[ 'js' ] = Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'JS', 'js', 'list of loaded javascript files.', $list, json_encode( $list ), count( $list ), 'file-code-o', \true, \false );

    }

    /**
     * build the jsVar button
     *
     * @param $store
     *
     * @throws \UnexpectedValueException
     */
    protected function buildJsVars( &$store )
    {
        if ( !Settings::i()->dtprofiler_enabled_jsvars ) {
            return;
        }

        $js = $this->jsVars;

        if ( !count( $js ) ) {
            return;
        }

        $list = [];
        foreach ( $js as $key => $val ) {
            if ( !is_array( $val ) ) {
                $v = json_decode( $val, \true ) ?? $val;
            }
            else {
                $v = $val;
            }
            $template = Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->keyvalue( $key, $v );
            $list[ $key ] = [ 'name' => $template ];
        }
        $store[ 'jsVars' ] = Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'JSVars', 'jsvars', 'Loaded JS Vars', $list, json_encode( $list ), count( $list ), 'file-code-o', \true, \false );
    }
}
