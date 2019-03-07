<?php

/**
 * @brief       Dtbase Settings extension: Proxy
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Proxy Class Generator
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\Settings;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * proxy
 */
class _proxy
{

    /**
     * add in array of form helpers
     *
     * @param array $helpers
     */
    public function elements( &$helpers )
    {
        $helpers[] = [
            'name'  => 'dtproxy_do_props',
            'class' => 'yn',
            'ops'   => [
                'togglesOn' => [ 'do_props_doc' ],
            ],
        ];

        $helpers[] = [
            'name'  => 'dtproxy_do_constants',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtproxy_do_proxies',
            'class' => 'yn',
        ];

    }

    /**
     * return a tab name
     *
     * @return string
     */
    public function tab(): string
    {
        return 'dtproxy';
    }

    /**
     * formValues, format the values before saving as settings
     *
     * @param array $values
     *
     * @return void
     */
    public function formateValues( &$values )
    {

    }
}
