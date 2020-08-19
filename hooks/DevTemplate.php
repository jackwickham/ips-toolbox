//<?php namespace toolbox_IPS_Theme_Dev_Template_ac409d81bb8f8d5165119ca65e20be252;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class toolbox_hook_DevTemplate extends _HOOK_CLASS_
{

    public function __construct($app, $templateLocation, $templateName)
    {
        parent::__construct($app, $templateLocation, $templateName);
        if( defined('DT_THEME') && defined('DT_THEME_ID') && DT_THEME === true && DT_THEME_ID !== 0) {

            $this->sourceFolder = \IPS\ROOT_PATH . '/themes/'.\IPS\toolbox\Application::getThemeId().'/html/' . $app . '/' . $templateLocation . '/' . mb_strtolower($templateName) . '/';
        }

    }
}
