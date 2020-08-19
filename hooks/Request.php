//<?php namespace toolbox_IPS_Request_a743acbd2721a1bfcd8cd2f548ada658c;

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
