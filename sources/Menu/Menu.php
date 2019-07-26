<?php

/**
 * @brief       Menu Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox;

use IPS\Application;
use IPS\Http\Url;
use IPS\Patterns\Singleton;
use IPS\Plugin;
use IPS\Theme;

class _Menu extends Singleton
{

    /**
     * @inheritdoc
     */
    protected static $instance;

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function build(): string
    {

        return Theme::i()->getTemplate( 'devBar', 'toolbox' )->devBar( $this->execute() );
    }

    /**
     * add the menu to cache
     */
    public function execute(): array
    {

        $store = [];
        $store[ 'roots' ][ 'toolbox' ] = [
            'id'   => 'toolbox',
            'name' => 'Dev Toolbox',
            'url'  => 'elDevToolstoolbox',
        ];

        $store[ 'toolbox' ][] = [
            'id'   => 'settings',
            'name' => 'Settings',
            'url'  => (string)Url::internal( 'app=toolbox&module=settings&controller=settings' ),
        ];
        $store[ 'toolbox' ][] = [
            'id'   => 'cons',
            'name' => 'Change Constants',
            'url'  => (string)Url::internal( 'app=toolbox&module=settings&controller=cons' ),
        ];
        $store[ 'toolbox' ][] = [
            'id'   => 'proxy',
            'name' => 'Proxy Class Generator',
            'url'  => (string)Url::internal( 'app=toolbox&module=proxy&controller=proxy' ),
        ];
        /**
         * @var Application $app
         */
        foreach ( Application::appsWithExtension( 'toolbox', 'menu' ) as $app ) {
            /* @var \IPS\toolbox\extensions\toolbox\menu\menu $menu */
            foreach ( $app->extensions( 'toolbox', 'menu', \true ) as $menu ) {
                $menu->menu( $store );
            }
        }

        $store[ 'toolbox' ][] = [
            'id'   => 'content',
            'name' => 'Content Generator',
            'url'  => (string)Url::internal( 'app=toolbox&module=content&controller=generator' ),
        ];
 
        $store[ 'toolbox' ][] = [
            'id'   => 'proxy',
            'name' => 'Generate Application Dev Folder',
            'url'  => (string)Url::internal( 'app=toolbox&module=devfolder&controller=applications' ),
        ];

        $store[ 'toolbox' ][] = [
            'id'   => 'proxy',
            'name' => 'Generate Plugin Dev Folder',
            'url'  => (string)Url::internal( 'app=toolbox&module=devfolder&controller=plugins' ),
        ];

        $this->menu( $store );

        $store[ 'roots' ][] = [
            'id'   => 'apps',
            'name' => 'Apps',
            'url'  => 'elDevToolBoxApps',
        ];

        /**
         * @var $apps Application
         */
        foreach ( Application::applications() as $apps ) {
            $store[ 'apps' ][ $apps->directory ] = [
                'id'   => $apps->directory,
                'name' => '__app_' . $apps->directory,
                'url'  => (string)Url::internal( 'app=core&module=applications&controller=developer&appKey=' . $apps->directory ),
            ];
        }

        $plugins = \false;

        foreach ( Plugin::plugins() as $plugin ) {
            $plugins = \true;
            $store[ 'plugins' ][ $plugin->name ] = [
                'id'   => $plugin->name,
                'name' => $plugin->name,
                'url'  => (string)Url::internal( 'app=core&module=applications&controller=plugins&do=developer&id=' . $plugin->id ),
            ];
        }

        if ( $plugins ) {
            $store[ 'roots' ][] = [
                'id'   => 'plugins',
                'name' => 'Plugins',
                'url'  => 'elDevToolsPlugins',
            ];
        }

        return $store;
    }

    /**
     * default menu stuff
     *
     * @param $store
     */
    protected function menu( &$store )
    {

        $store[ 'roots' ][] = [
            'id'   => 'ips',
            'name' => 'IPS',
            'url'  => 'elDevToolboxIPS',
        ];

        $store[ 'ips' ][] = [
            'id'   => 'guides',
            'name' => 'Guides',
            'url'  => 'https://invisioncommunity.com/4guides/how-to-use-ips-community-suite/first-steps/terminology-r7/',
        ];

        $store[ 'ips' ][] = [
            'id'   => 'devdocs',
            'name' => 'Developer Documentation',
            'url'  => 'https://invisioncommunity.com/developers/',
        ];

        $store[ 'ips' ][] = [
            'id'   => 'comms',
            'name' => 'Community Forums',
            'url'  => 'https://invisioncommunity.com/forums/forum/503-customization-resources/',
        ];

        $store[ 'ips' ][] = [
            'id'   => 'notes',
            'name' => 'Release Notes',
            'url'  => 'https://invisioncommunity.com/release-notes/',
        ];

        $store[ 'roots' ][] = [
            'id'   => 'sys',
            'name' => 'System',
            'url'  => 'elDevToolboxsys',
        ];

        $store[ 'sys' ][] = [
            'id'   => 'apps',
            'name' => 'Applications',
            'url'  => (string)Url::internal( 'app=core&module=applications&controller=applications' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'plugins',
            'name' => 'Plugins',
            'url'  => (string)Url::internal( 'app=core&module=applications&controller=plugins' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'logs',
            'name' => 'Logs',
            'url'  => (string)Url::internal( 'app=core&module=support&controller=systemLogs' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'task',
            'name' => 'Tasks',
            'url'  => (string)Url::internal( 'app=core&module=settings&controller=advanced&do=tasks' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'sql',
            'name' => 'SQL Toolbox',
            'url'  => (string)Url::internal( 'app=core&module=support&controller=sql' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'support',
            'name' => 'Support',
            'url'  => (string)Url::internal( 'app=core&module=support&controller=support' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'error',
            'name' => 'Error Logs',
            'url'  => (string)Url::internal( 'app=core&module=support&controller=errorLogs' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'syscheck',
            'name' => 'System Check',
            'url'  => (string)Url::internal( 'app=core&module=support&controller=support&do=systemCheck' ),
        ];

        $store[ 'sys' ][] = [
            'id'   => 'phpinfo',
            'name' => 'PHP Info',
            'url'  => (string)Url::internal( 'app=core&module=support&controller=support&do=phpinfo' ),
        ];
    }
}
