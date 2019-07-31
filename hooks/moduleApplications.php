//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\toolbox\Build;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_moduleApplications extends _HOOK_CLASS_
{


    /**
     * Export an application
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
