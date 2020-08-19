<?php

/**
 * @brief       Toolbox Constants extension: Toolbox
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\extensions\toolbox\constants;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * toolbox
 */
class _toolbox
{

    /**
     * add in array of constants
     */
    public function getConstants()
    {
        return [
            'DTBUILD' => [
                'name' => 'DTBUILD',
                'default' => \false,
                'current' => defined('\DTBUILD') ? \DTBUILD : \null,
                'description' => 'This enables special app build features for toolbox, use with caution.',
                'type' => 'boolean',
                'tab' => 'DevTools',

            ],
            'DTPROFILER' => [
                'name' => 'DTPROFILER',
                'default' => \false,
                'current' => defined('\DTPROFILER') ? \DTPROFILER : \null,
                'description' => 'this will enable/disable extra features for the profiler.',
                'type' => 'boolean',
                'tab' => 'DevTools',

            ],
            'TOOLBOXDEV' => [
                'name' => 'TOOLBOXDEV',
                'default' => \false,
                'current' => defined('\TOOLBOXDEV') ? \TOOLBOXDEV : \false,
                'description' => 'this will enable/disable extra features for toolbox..',
                'type' => 'boolean',
                'tab' => 'DevTools',

            ],
            'DT_THEME' => [
                'name' => 'DT_THEME',
                'default' => \false,
                'current' => defined('\DT_THEME') ? \DT_THEME : \false,
                'description' => 'this will enable/disable designer mode templates to be used with IN_DEV. check out the HowToUseDesignerDevMode.txt.',
                'type' => 'boolean',
                'tab' => 'DevTools',

            ],
            'DT_THEME_ID' => [
                'name' => 'DT_THEME_ID',
                'default' => 0,
                'current' => defined('\DT_THEME_ID') ? \DT_THEME_ID : 0,
                'description' => 'enter the theme ID number to use.',
                'type' => 'int',
                'tab' => 'DevTools',

            ],
            'DT_THEME_ID_ADMIN' => [
                'name' => 'DT_THEME_ID_ADMIN',
                'default' => 0,
                'current' => defined('\DT_THEME_ID_ADMIN') ? \DT_THEME_ID_ADMIN : 0,
                'description' => 'if you want to use a different theme for the ACP than on the front end, enter theme ID number here, leave 0 to keep disabled.',
                'type' => 'int',
                'tab' => 'DevTools',

            ],
            'DT_THEME_CMS_USE_DESIGNER_FILES' => [
                'name' => 'DT_THEME_CMS_USE_DESIGNER_FILES',
                'default' => 0,
                'current' => defined('\DT_THEME_CMS_USE_DESIGNER_FILES') ? \DT_THEME_CMS_USE_DESIGNER_FILES : false,
                'description' => 'use the designer mode templates.',
                'type' => 'boolean',
                'tab' => 'DevTools',

            ]
        ];
    }

    /**
     * define an array of constant names to add to the important tab
     *
     * @return array
     */
    public function add2Important()
    {
        return [
            'BYPASS_ACP_IP_CHECK',
            'IN_DEV',
            'IN_DEV_STRICT_MODE',
            'USE_DEVELOPMENT_BUILDS',
            'DEV_WHOOPS_EDITOR',
            'DEV_DEBUG_JS',
            'QUERY_LOG',
            'COOKIE_PREFIX',
            'CP_DIRECTORY',
            'DEV_USE_WHOOPS',
            'DEV_HIDE_DEV_TOOLS',
            'DEV_DEBUG_CSS',
            'DEBUG_TEMPLATES',
            'DEBUG_LOG',
            'COOKIE_PATH',
        ];
    }

    /**
     * formValues, format the values before saving as settings
     *
     * @param array $values
     *
     * @return void
     */
    public function formateValues(&$values)
    {
    }
}
