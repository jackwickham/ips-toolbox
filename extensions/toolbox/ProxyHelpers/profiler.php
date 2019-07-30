<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Profiler
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Profiler
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use IPS\toolbox\Generator\Builders\ClassGenerator;
use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * profiler
 */
class _profiler
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param ClassGenerator $classGenerator
     */
    public function store( ClassGenerator $classGenerator )
    {

        $classDoc[] = [ 'prop' => 'dtprofiler_css', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dtprofiler_js', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dtprofiler_js_vars', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dtprofiler_templates', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dtprofiler_bt_cache', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dtprofiler_bt', 'hint' => 'array' ];

        foreach ( $classDoc as $doc ) {
            $classGenerator->addPropertyTag( $doc[ 'prop' ], [ 'hint' => $doc[ 'hint' ] ] );

        }
    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param ClassGenerator $classGenerator
     */
    public function request( ClassGenerator $classGenerator )
    {

        $classGenerator->addPropertyTag( 'bt', [ 'hint' => 'string' ] );
    }
}
