//<?php namespace a0ecfa264c6bf67afab964bb707de3cb6;

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
