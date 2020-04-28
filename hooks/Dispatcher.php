//<?php namespace afa30384fa37b988871d99778e3851dd0;

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
