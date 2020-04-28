//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Output;
use IPS\Request;
use IPS\Theme;

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class toolbox_hook_adminGlobal extends _HOOK_CLASS_
{

    /* !Hook Data - DO NOT REMOVE */
    public static function hookData()
    {
        return \array_merge_recursive(
            [
                'globalTemplate' => [
                    0 => [
                        'selector' => '#ipsLayout_header',
                        'type'     => 'add_inside_start',
                        'content'  => '{{if $menu = \IPS\toolbox\Menu::i()->build()}}
	{$menu|raw}
{{endif}}',
                    ],
                    1 => [
                        'selector' => 'html > body',
                        'type'     => 'add_inside_end',
                        'content'  => '<!--ipsQueryLog-->',
                    ],
                ],
            ],
            parent::hookData()
        );
    }

    /* End Hook Data */

    public function globalTemplate($title, $html, $location = [])
    {
        Output::i()->cssFiles = \array_merge(Output::i()->cssFiles, Theme::i()->css('devbar.css', 'toolbox', 'admin'));

        return parent::globalTemplate($title, $html, $location);
    }

    /* End Hook Data */
    public function tabs(
        $tabNames,
        $activeId,
        $defaultContent,
        $url,
        $tabParam = 'tab',
        $tabClasses = '',
        $panelClasses = ''
    ) {
        if (Request::i()->app === 'core' && Request::i()->module === 'applications' && Request::i(
            )->controller === 'developer' && !Request::i()->do) {
            $tabNames[ 'SchemaImports' ] = 'dtdevplus_schema_imports';
//            $tabNames[ 'GitHooks' ] = 'dtdevplus_dev_git_hooks';
        }

        return parent::tabs($tabNames, $activeId, $defaultContent, $url, $tabParam, $tabClasses, $panelClasses);
    }
}
