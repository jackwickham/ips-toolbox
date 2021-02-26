//<?php namespace toolbox_IPS_core_tasks_sitemapgenerator_a278351b94ddda487a0d18b749396dad3;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class toolbox_hook_sitemapGenerator extends _HOOK_CLASS_
{

    public function execute()
    {
        if( \IPS\IN_DEV === false || !defined('TOOLBOXDEV') || (defined('TOOLBOXDEV') && TOOLBOXDEV === false)){
            parent::execute();
        }
    }
}
