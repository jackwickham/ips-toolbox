<?php

namespace IPS\toolbox\extensions\toolbox\constants;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
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
            'BYPASSPROXYDT' => [
                'name'        => 'BYPASSPROXYDT',
                'default'     => \false,
                'current'     => defined( '\BYPASSPROXYDT' ) ? \BYPASSPROXYDT : \null,
                'description' => 'This is a very special use case, if defined, will create dtproxy2 and copy the contents of dtproxy2 to dtproxy when building proxy files.',
                'type'        => 'boolean',
                'tab'         => 'DevTools',
            ],
            'DTBUILD'       => [
                'name'        => 'DTBUILD',
                'default'     => \false,
                'current'     => defined( '\DTBUILD' ) ? \DTBUILD : \null,
                'description' => 'This enables special app build features for toolbox, use with caution.',
                'type'        => 'boolean',
                'tab'         => 'DevTools',

            ],
            'DTPROFILER'    => [
                'name'        => 'DTPROFILER',
                'default'     => \false,
                'current'     => defined( '\DTPROFILER' ) ? \DTPROFILER : \null,
                'description' => 'this will enable/disable extra features for the profiler.',
                'type'        => 'boolean',
                'tab'         => 'DevTools',

            ],
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
    public function formateValues( &$values )
    {

    }
}
