<?php

/**
 * @brief       Dtbase Menu extension: Menu
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Proxy Class Generator
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\Menu;

use IPS\Http\Url;
use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * menu
 */
class _menu
{

    /**
     * return an array for the menu
     */
    public function menu( &$menus )
    {


        $menus[ 'toolbox' ][] = [
            'id'   => 'proxy',
            'name' => 'Proxy Class Generator',
            'url'  => (string)Url::internal( 'app=toolbox&module=proxy&controller=proxy' ),
        ];
    }
}
