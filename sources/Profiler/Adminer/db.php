<?php
$path =  str_replace( '/applications/toolbox/sources/Profiler/Adminer/db.php', '', str_replace( '\\', '/', __FILE__ ) ).'/';
require_once $path. 'init.php';
\IPS\Session\Front::i();

$member = \IPS\Member::loggedIn();
if( $member->isAdmin() ) {
    $_GET["username"] = \IPS\Settings::i()->getFromConfGlobal('sql_user');

use function str_replace;

    $_GET["password"] = \IPS\Settings::i()->getFromConfGlobal('sql_pass');
    $_GET["db"] = \IPS\Settings::i()->getFromConfGlobal('sql_database');
    $_GET["server"] = \IPS\Settings::i()->getFromConfGlobal('sql_host');
    function adminer_object()
    {
        // required to run any plugin
        include_once 'Plugin.php';
        include_once 'Plugins/Frames.php';


        $plugins = array(
            // specify enabled plugins here
            new AdminerFrames()
        );

        /* It is possible to combine customization and plugins:
        class AdminerCustomization extends AdminerPlugin {
        }
        return new AdminerCustomization($plugins);
        */

        return new AdminerPlugin($plugins);
    }

    require_once("adminer.php");
}
