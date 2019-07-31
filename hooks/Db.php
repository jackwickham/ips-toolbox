//<?php

/**
* @inheritdoc
*/

use IPS\toolbox\Profiler\Memory;
use IPS\toolbox\Profiler\Time;
use IPS\toolbox\Proxy\Generator\Db;
use IPS\toolbox\Proxy\Generator\Proxy;
use IPS\toolbox\Proxy\Proxyclass;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class toolbox_hook_Db extends _HOOK_CLASS_
{
    protected  $dtkey = 0;

    public function query( $query, $log = true, $read = false )
    {
        if ( \IPS\QUERY_LOG && \class_exists( Memory::class, \true ) ) {
            $memory = new Memory;
            $time = new Time;
        }

        $parent = parent::query( $query, $log, $read );

        if ( \IPS\QUERY_LOG && \class_exists( Memory::class, \true ) ) {
            $final = $time->end();
            $mem = $memory->end();
            $this->finalizeLog( $final, $mem );
        }

        return $parent;
    }

    protected function finalizeLog( $time, $mem )
    {
        $id = $this->dtkey - 1;
        $this->log[ $id ][ 'time' ] = $time;
        $this->log[ $id ][ 'mem' ] = $mem;
    }

    public function preparedQuery( $query, array $_binds, $read = false )
    {
        if ( \IPS\QUERY_LOG && \class_exists( Memory::class, \true ) ) {
            $memory = new Memory;
            $time = new Time;
        }

        $parent = parent::preparedQuery( $query, $_binds, $read );

        if ( \IPS\QUERY_LOG && \class_exists( Memory::class, \true ) ) {
            $final = $time->end();
            $mem = $memory->end();
            $this->finalizeLog( $final, $mem );
        }

        return $parent;
    }

    public function createTable( $data )
    {
        $return = parent::createTable( $data );

        if ( \class_exists( Proxyclass::class, \true ) ) {
            Db::i()->create();
        }
        return $return;
    }

    public function addColumn( $table, $definition )
    {
        parent::addColumn( $table, $definition );
        if ( \class_exists( Proxy::class, \true ) ) {
            Proxy::adjustModel( $table );
        }
    }

    protected function log( $query, $server = null )
    {
        $this->dtkey++;
        parent::log( $query, $server );
    }

}

