//<?php namespace toolbox_IPS_Data_Store_aa75b88f1becf4d3a6933ef010e3f6f7d;


use IPS\core\extensions\core\ProfileSteps\Core;
use IPS\toolbox\Application;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Profiler\Parsers\Caching;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

abstract class toolbox_hook_DataStore extends _HOOK_CLASS_
{

//    public function __get($key)
//    {
//        $parent = parent::__get($key);
//        Caching::i()->cache[] = [
//            'type' => 'GET',
//            'name' => $key
//        ];
//        return $parent;
//    }

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
