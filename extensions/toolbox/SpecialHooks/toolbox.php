<?php

namespace IPS\toolbox\extensions\toolbox\SpecialHooks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * toolbox
 */
class _toolbox
{
    public function notneededbutipssaysitneedstobehere(){}
}
