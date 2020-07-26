//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class toolbox_hook_SessionAdmin extends _HOOK_CLASS_ // \IPS\Session\Admin
{
	public function gc($lifetime) {
		// Never delete admin sessions
		return true;
	}
}
