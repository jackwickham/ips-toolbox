//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\toolbox\Application;
use IPS\toolbox\Profiler\Debug;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

Application::loadAutoLoader();

abstract class toolbox_hook_DataStore extends _HOOK_CLASS_
{

    //    public function clearAll($exclude = \null)
    //    {
    //        $paths = [
    //            \IPS\ROOT_PATH . '/toolbox_templates/',
    //            \IPS\ROOT_PATH . '/hook_temp/',
    //        ];
    //
    //        foreach ($paths as $path) {
    //            if (\is_dir($path)) {
    //                $this->removeFiles($path);
    //            }
    //        }
    //
    //        parent::clearAll($exclude);
    //    }

    protected function removeFiles( $path )
    {
        try {
            $files = new Finder();
            $files->in( $path )->files();
            $fs = new Filesystem;

            foreach ( $files as $file ) {
                $fs->remove( $file->getRealPath() );
            }
        } catch ( \Exception $e ) {
            Debug::add( 'Data Store Clear', $e );
        }
    }
}
