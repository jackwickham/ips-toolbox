//<?php namespace a6517e3008015908ad2a08d5fd4883306;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_Request extends _HOOK_CLASS_
{

    public function returnData()
    {
        return $this->data;
    }
}
