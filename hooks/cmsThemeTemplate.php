//<?php namespace toolbox_IPS_cms_Theme_Template_aed8e2c7d4e593adf901a5012cc2e8b57;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class toolbox_hook_cmsThemeTemplate extends _HOOK_CLASS_
{

    public function __construct($app, $templateLocation, $templateName)
    {
        parent::__construct($app, $templateLocation, $templateName);
        if( defined('DT_THEME') && defined('DT_THEME_ID') && DT_THEME === true && DT_THEME_ID !== 0) {
            $this->sourceFolder = \IPS\ROOT_PATH . "/themes/cms/{$templateLocation}/{$templateName}/";
        }
    }
}
