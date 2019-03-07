//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_Request extends _HOOK_CLASS_
{

    public function returnData()
    {
        return $this->data;
    }

}
