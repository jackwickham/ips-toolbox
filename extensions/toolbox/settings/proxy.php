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

use IPS\toolbox\Form;
use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
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
     * @param Form $form
     */
    public function elements( &$form ): void
    {

        $form->add( 'dtproxy_do_props', 'yn' )->toggles( [ 'do_props_doc' ] )->tab( 'dtproxy' );
        $form->add( 'dtproxy_do_constants', 'yn' );
        $form->add( 'dtproxy_do_proxies', 'yn' );
    }

    /**
     * formValues, format the values before saving as settings
     *
     * @param array $values
     *
     * @return void
     */
    public function formatValues( &$values )
    {

    }
}
