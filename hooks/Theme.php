//<?php

use IPS\Settings;
use IPS\Theme\Dev\Template;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class toolbox_hook_Theme extends _HOOK_CLASS_
{

    public static function runProcessFunction( $content, $functionName ){

        /* If it's already been built, we don't need to do it again */
        if ( \function_exists( 'IPS\Theme\\' . $functionName ) ) {
            return;
        }

        if ( Template::$debugFileName !== null && \IPS\IN_DEV === true && \IPS\NO_WRITES === false && mb_strpos( $functionName, 'css_' ) === false && Settings::i()->toolbox_debug_templates ) {
            $path = \IPS\ROOT_PATH . '/toolbox_templates/';
            $filename = $path . Template::$debugFileName;
            if ( !is_dir( $path ) ) {
                mkdir( $path, 0777, true );
            }
            //lki
            $content = "<?php\nnamespace IPS\Theme;\n" . $content;
            try {
                \file_put_contents( $filename, $content );
            } catch ( Exception $e ) {
            }
            include_once( $filename );
        }
        else {
            parent::runProcessFunction( $content, $functionName );
        }
    }
}