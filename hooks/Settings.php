//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_Settings extends _HOOK_CLASS_
{

    public function getData()
    {
        if ( !$this->loaded ) {
            $this->loadFromDb();
        }

        return $this->data;
    }
}
