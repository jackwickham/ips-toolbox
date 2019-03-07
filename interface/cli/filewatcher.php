#!/bin/bash
<?php

use IPS\dtproxy\Proxyclass;
use IPS\toolbox\Profiler\Debug;

require_once str_replace( 'applications/dtproxy/interface/cli/filewatcher.php', '', str_replace( '\\', '/', __FILE__ ) ) . 'init.php';

class filewatcher
{

    public function __construct()
    {
    }

    public function check( $args )
    {
        list( $junk, $app, $hooks, $file ) = explode( \DIRECTORY_SEPARATOR, $args[ 1 ] );
        $file = str_replace( '.php', '', $file );


        if ( $hooks === 'hooks' ) {
            $fnpath = \IPS\ROOT_PATH . '/dtProxy/hooked/';
            $hookFile = $fnpath . $app . '_' . $file . '.php';
            if ( !file_exists( $hookFile ) ) {

                if ( is_dir( $fnpath ) ) {
                    Proxyclass::i()->emptyDirectory( $fnpath );
                }
                if ( !is_dir( $fnpath ) && mkdir( $fnpath, \IPS\IPS_FOLDER_PERMISSION, \true ) && is_dir( $fnpath ) ) {
                    chmod( $fnpath, \IPS\IPS_FOLDER_PERMISSION );
                }
                if ( is_dir( $fnpath ) ) {

                    try {

                        $where = [
                            'app = ? AND type = ? AND filename=?',
                            $app,
                            'c',
                            $file,
                        ];
                        $class = \IPS\Db::i()->select( 'class', 'core_hooks', $where )->first();
                        $proxy = <<<EOF
<?php

class _HOOK_CLASS_ extends {class} {}
EOF;
                        $proxy = str_replace( '{class}', $class, $proxy );
                        file_put_contents( $hookFile, $proxy );

                    } catch ( \Exception $e ) {
                        Debug::add( 'filewatcher', $e );
                        Debug::add( 'file', $hookFile );
                    }
                }
            }
        }
    }
}

( new filewatcher )->check( $argv );
