//<?php namespace toolbox_IPS_Dispatcher_a130eb6d340e5f6ae39fc3e9de5feaeb4;

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
