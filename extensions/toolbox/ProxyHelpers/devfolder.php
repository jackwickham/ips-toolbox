<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Devfolder
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Folders
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\dtproxy\ProxyHelpers;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * devfolder
 */
class _devfolder
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param array $classDoc
     */
    public function store( &$classDoc )
    {

    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param array $classDoc
     */
    public function request( &$classDoc )
    {
        $classDoc[] = [ 'pt' => 'p', 'prop' => 'dtdevfolder_app', 'type' => 'string' ];
        $classDoc[] = [ 'pt' => 'p', 'prop' => 'storm', 'type' => 'string' ];

    }
}
