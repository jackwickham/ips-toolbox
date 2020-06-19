//<?php namespace toolbox_IPS_Patterns_ActiveRecord_a2890fb4cbabe7e53c2935c2ca9a757c3;

use http\Exception\InvalidArgumentException;
use IPS\Application;
use IPS\Member;
use IPS\Patterns\ActiveRecord;

if ( !defined('\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}


/**
* Hook For \IPS\Patterns\ActiveRecord
* @mixin \IPS\Patterns\ActiveRecord
*/
class toolbox_hook_ActiveRecord extends _HOOK_CLASS_
{
    public function __construct()
    {
        if( \IPS\IN_DEV === true && DTPROFILER === true){
            $r = new \ReflectionClass($this);
            if ( $r->getProperty('multitons')->getDeclaringClass()->getName() === 'IPS\Patterns\_ActiveRecord') {

                throw new \RuntimeException('You are missing protected static $multions=[]; property in your class '.static::class );
            }
        }
        parent::__construct(...\func_get_args());
    }
}
