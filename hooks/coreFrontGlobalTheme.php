//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Dispatcher;
use IPS\Member;
use IPS\Output;
use IPS\Settings;
use IPS\toolbox\Profiler;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_coreFrontGlobalTheme extends _HOOK_CLASS_
{

    /* !Hook Data - DO NOT REMOVE */
    public static function hookData()
    {

        return parent::hookData();
    }

    /* End Hook Data */

    function queryLog( $querylog )
    {

        if ( Dispatcher::hasInstance() && Dispatcher::i()->controllerLocation === 'admin' && Settings::i()->dtprofiler_show_admin ) {
            return;
        }
        $member = Member::loggedIn()->member_id;
        $can = \json_decode( Settings::i()->dtprofiler_can_use, \true );
        if ( \property_exists( Output::i(), 'dtContentType' ) && Output::i()->dtContentType === 'text/html' && ( ( !\IPS\IN_DEV && \in_array( $member, $can, \true ) ) || \IPS\IN_DEV ) ) {
            try {

                return Profiler::i()->run();;
            } catch ( \Exception $e ) {
                throw $e;
            }
        }

        return parent::queryLog( $querylog );
    }

    function cacheLog()
    {

        if ( Dispatcher::hasInstance() && Dispatcher::i()->controllerLocation === 'admin' && Settings::i()->dtprofiler_show_admin ) {
            return;
        }
        $member = Member::loggedIn()->member_id;
        $can = \json_decode( Settings::i()->dtprofiler_can_use, \true );
        if ( \property_exists( Output::i(), 'dtContentType' ) && Output::i()->dtContentType === 'text/html' && ( ( !\IPS\IN_DEV && \in_array( $member, $can, \true ) ) || \IPS\IN_DEV ) ) {

        }
        else {
            return parent::cacheLog();
        }
    }
}
