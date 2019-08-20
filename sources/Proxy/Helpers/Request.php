<?php

/**
 * @brief       IPSRequest Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox\Proxy
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Proxy\Helpers;

use IPS\Application;
use Generator\Builders\ClassGenerator;
use function defined;
use function header;
use function method_exists;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Request implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process( $class, ClassGenerator $classGenerator, &$classExtends )
    {

        $classDoc[] = [ 'prop' => 'app', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'module', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'controller', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'id', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'pid', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'do', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'appKey', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'tab', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'adsess', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'group', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'new', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => '_new', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'path', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'c', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'd', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'application', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'hint', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'limit', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'password', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'club', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'page', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'perPage', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'value', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'sortby', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'sortdirection', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'parent', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'filter', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'params', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'input', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'action', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'chunk', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'chunks', 'hint' => 'string' ];
        $classDoc[] = [ 'prop' => 'last', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'enabled', 'hint' => 'int' ];
        $classDoc[] = [ 'prop' => 'gitApp', 'hint' => 'string' ];
        foreach ( $classDoc as $doc ) {
            $classGenerator->addPropertyTag( $doc[ 'prop' ], [ 'hint' => $doc[ 'hint' ] ] );
        }
        /* @var Application $app */
        foreach ( Application::appsWithExtension( 'toolbox', 'ProxyHelpers' ) as $app ) {
            $extensions = $app->extensions( 'toolbox', 'ProxyHelpers', \true );
            /* @var \IPS\toolbox\extensions\toolbox\ProxyHelpers $extension */
            foreach ( $extensions as $extension ) {
                if ( method_exists( $extension, 'request' ) ) {
                    $extension->request( $classGenerator );
                }
            }
        }
    }
}
