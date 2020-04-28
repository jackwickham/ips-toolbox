//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Data\Store;
use IPS\Output;
use IPS\Request;
use IPS\Settings;
use IPS\Theme;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_coreGlobalGlobalTheme extends _HOOK_CLASS_
{
    /* !Hook Data - DO NOT REMOVE */
    public static function hookData()
    {
        return parent::hookData();
    }

    /* End Hook Data */

    function includeCSS()
    {
        if ( \IPS\QUERY_LOG && !Request::i()->isAjax() ) {
            Output::i()->cssFiles = \array_merge( Output::i()->cssFiles, Theme::i()->css( 'profiler.css', 'toolbox', 'front' ) );

            foreach ( Output::i()->cssFiles as $key => $val ) {
                if ( \mb_strpos( $val, 'query_log.css' ) !== \false ) {
                    unset( Output::i()->cssFiles[ $key ] );
                }

                if ( \mb_strpos( $val, 'caching_log.css' ) !== \false ) {
                    unset( Output::i()->cssFiles[ $key ] );
                }
            }

            if ( Settings::i()->dtprofiler_enabled_css ) {
                Store::i()->dtprofiler_css = Output::i()->cssFiles;
            }
        }
        return parent::includeCSS();
    }

    function includeJS()
    {
        if ( \IPS\QUERY_LOG && !Request::i()->isAjax() ) {
            Output::i()->jsFiles = \array_merge( Output::i()->jsFiles, Output::i()->js( 'front_profiler.js', 'toolbox', 'front' ) );

            if ( Settings::i()->dtprofiler_enabled_js ) {
                Store::i()->dtprofiler_js = Output::i()->jsFiles;
            }
            if ( Settings::i()->dtprofiler_enabled_jsvars ) {
                Store::i()->dtprofiler_js_vars = Output::i()->jsVars;
            }
        }

        return parent::includeJS();
    }
}
