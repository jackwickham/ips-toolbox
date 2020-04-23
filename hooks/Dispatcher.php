//<?php namespace a03f7b3d0b96b3b71f524f3044f59e230;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

abstract class toolbox_hook_Dispatcher extends _HOOK_CLASS_
{

    public function run()
    {
        \IPS\Widget::deleteCaches();
        parent::run();
    }
}
