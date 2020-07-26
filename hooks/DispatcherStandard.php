//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class toolbox_hook_DispatcherStandard extends _HOOK_CLASS_ // \IPS\Dispatcher\Standard
{
	public function __construct() {
		// Override the base url host to whatever the current requested host is, for easier testing on other devices
		if (isset($_SERVER['HTTP_HOST'])) {
			\IPS\Settings::i()->base_url = (string) (new \IPS\Http\Url(\IPS\Settings::i()->base_url))->setHost($_SERVER['HTTP_HOST']);
		}
	}
}
