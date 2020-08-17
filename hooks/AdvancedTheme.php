//<?php namespace toolbox_IPS_Theme_Advanced_Theme_ab1a02afbf6b4766298c92d5b6adfb7e6;

/* To prevent PHP errors (extending class does not exist) revealing path */

use const IPS\ROOT_PATH;

if ( !\defined('\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class toolbox_hook_AdvancedTheme extends _HOOK_CLASS_
{
    public static function getToBuild()
    {
        $parent = parent::getToBuild();
        if( defined('DT_THEME') && defined('DT_THEME_ID') && DT_THEME === true && DT_THEME_ID !== 0) {
            $themes = [];

            foreach( $parent as $k => $theme ){
                $path = ROOT_PATH.'/themes/'.$theme.'/';
                if( !file_exists( $path ) ){
                    $themes[] = $theme;
                }
            }

            $parent = $themes;
        }
        return $parent;
    }

}
