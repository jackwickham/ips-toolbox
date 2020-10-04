//<?php namespace toolbox_IPS_Session_a47cc58f1650fe50fb02f1fedde046363;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class toolbox_hook_Session extends _HOOK_CLASS_
{
	/**
	 * @return int
	 */
	public static function sessionLifetime() {
		return 86400 * 7; // 1 week
	}
}
