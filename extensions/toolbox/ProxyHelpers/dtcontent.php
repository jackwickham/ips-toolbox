<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Dtcontent
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Content Generator
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * dtcontent
 */
class _dtcontent
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
        $classDoc[] = [ 'pt' => 'p', 'prop' => 'oldDo', 'type' => 'string' ];

    }
}
