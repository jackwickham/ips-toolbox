//<?php


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
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