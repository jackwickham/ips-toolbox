//<?php namespace toolbox_IPS_Patterns_Singleton_a6a1e2be5400a800cdcfdccb75549ddce;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}


/**
* Hook For \IPS\Patterns\Singleton
* @mixin \IPS\Patterns\Singleton
*/
class toolbox_hook_Singleton extends _HOOK_CLASS_
{
    public function __construct()
    {
        if( \IPS\IN_DEV === true && defined('TOOLBOXDEV') && TOOLBOXDEV === true){
            $r = new \ReflectionClass($this);
            if ( $r->getProperty('instance')->getDeclaringClass()->getName() === 'IPS\Patterns\_Singleton') {

                throw new \RuntimeException('You are missing protected static $instance=null; property in your class '.static::class );
            }
        }
    }
}
