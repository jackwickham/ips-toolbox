<?php

use IPS\Theme;

require_once str_replace( 'applications/dtprofiler/interface/debug/debug.php', '', str_replace( '\\', '/', __FILE__ ) ) . 'init.php';
\IPS\Session\Front::i();

$max = ( ini_get( 'max_execution_time' ) / 2 ) - 5;
$time = time();

while ( \true ) {

    $ct = time() - $time;
    if ( $ct >= $max ) {
        \IPS\Output::i()->json( [ 'end' => 1 ] );
    }

    $query = \IPS\Db::i()->select( '*', 'toolbox_debug', [ 'debug_ajax = ?', 1 ], 'debug_id DESC' );

    if ( count( $query ) ) {

        $iterators = new \IPS\Patterns\ActiveRecordIterator( $query, \IPS\toolbox\Profiler\Profiler\Debug::class );

        foreach ( $iterators as $obj ) {
            if ( $obj->type === 'exception' || $obj->type === 'array' ) {
                $message = json_decode( $obj->log, \true );
                $list[] = Theme::i()->getTemplate( 'generic', 'dtprofiler', 'front' )->keyvalue( $message, $obj->key );
            }
            else {
                $list[] = Theme::i()->getTemplate( 'generic', 'dtprofiler', 'front' )->string( $obj->log, $obj->key );
            }
            $obj->delete();
        }

        $return = [];
        if ( is_array( $list ) && count( $list ) ) {
            $count = count( $list );
            $return[ 'count' ] = $count;
            $return[ 'items' ] = $list;
            $return[ 'whole' ] = Theme::i()->getTemplate( 'generic', 'dtprofiler', 'front' )->button( 'Debug', 'debug', 'List of debug messages', $list, $count, 'bug' );
        }

        if ( is_array( $return ) and count( $return ) ) {
            \IPS\Output::i()->json( $return );
        }
    }
    else {
        sleep( 1 );
        continue;
    }
}
