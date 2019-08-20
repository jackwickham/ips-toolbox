//<?php

use IPS\toolbox\Build;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class toolbox_hook_moduleApplications extends _HOOK_CLASS_toolbox_hook_moduleApplications
{
    
    /**
    * Export an application
    *
    *
    * @return void
    * @note    We have to use a custom RecursiveDirectoryIterator in order to skip the /dev folder
    */
    public function download()
    {

        if ( \defined( '\DTBUILD' ) && \DTBUILD ) {
            Build::i()->export();
        }
        else {
            parent::download();
        }
    }

}






