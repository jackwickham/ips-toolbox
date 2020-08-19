//<?php namespace toolbox_IPS_cms_Theme_a997b2af915e3a3d5828e66a605e95359;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class toolbox_hook_cmsTheme extends _HOOK_CLASS_
{

    protected static function _getHtmlPath( $app, $location=null, $path=null )
    {
        if( defined('DT_THEME') && defined('DT_THEME_ID') && DT_THEME === true && DT_THEME_ID !== 0 && defined('DT_THEME_CMS_USE_DESIGNER_FILES') && DT_THEME_CMS_USE_DESIGNER_FILES === true) {
            return rtrim(\IPS\ROOT_PATH . "/themes/cms/{$location}/{$path}", '/') . '/';
        }

        return parent::_getHtmlPath($app, $location, $path);
    }
}
