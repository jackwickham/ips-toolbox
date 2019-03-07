<?php

/**
 * @brief       Dtbase Settings extension: Profiler
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Profiler
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\Settings;

use IPS\Member;
use IPS\Settings;
use function defined;
use function header;
use function json_decode;
use function json_encode;

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * profiler
 */
class _profiler
{

    /**
     * add in array of form helpers
     *
     * @param array $helpers
     */
    public function elements( &$helpers )
    {
        $members = \null;
        if ( Settings::i()->dtprofiler_can_use ) {
            $users = json_decode( Settings::i()->dtprofiler_can_use, \true );

            foreach ( $users as $user ) {
                $members[] = Member::load( $user );
            }
        }

        $helpers[] = [
            'name'    => 'dtprofiler_can_use',
            'class'   => 'member',
            'default' => $members,
            'ops'     => [
                'multiple' => 10,
            ],
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_show_admin',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'   => 'dtprofiler_enabled_execution',
            'class'  => 'yn',
            'header' => 'dtprofiler_profiler_tabs',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_executions',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_memory',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_memory_summary',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_files',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_enivro',
            'class' => 'yn',
        ];


        $helpers[] = [
            'name'  => 'dtprofiler_enabled_templates',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_css',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_js',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_jsvars',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_enabled_logs',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_logs_amount',
            'class' => '#',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_git_data',
            'class' => 'yn',
        ];

        $helpers[] = [
            'name'  => 'dtprofiler_show_changes',
            'class' => 'yn',
        ];

    }

    /**
     * return a tab name
     *
     * @return string
     */
    public function tab()
    {
        return 'dtprofiler';
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
        $new = [];
        foreach ( $values[ 'dtprofiler_can_use' ] as $key => $value ) {
            $new[] = $value->member_id;
        }
        $values[ 'dtprofiler_can_use' ] = json_encode( $new );
    }
}
