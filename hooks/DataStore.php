//<?php namespace ade7f640859e1938bdc0c4b9b7cb4d443;


use IPS\toolbox\Application;
use IPS\toolbox\Profiler\Debug;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

abstract class toolbox_hook_DataStore extends _HOOK_CLASS_
{

    protected function removeFiles($path)
    {
        try {
            $files = new Finder();
            $files->in($path)->files();
            $fs = new Filesystem;

            foreach ($files as $file) {
                $fs->remove($file->getRealPath());
            }
        } catch (\Exception $e) {
            Debug::add('Data Store Clear', $e);
        }
    }
}
