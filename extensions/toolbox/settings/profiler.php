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
use IPS\toolbox\Forms\Form;
use function defined;
use function header;
use function json_decode;
use function json_encode;
use function is_array;

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
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
     * @param Form $form
     */
    public function elements( &$form ): void
    {
        $members = \null;
        if ( Settings::i()->dtprofiler_can_use ) {
            $users = json_decode( Settings::i()->dtprofiler_can_use, \true );

            foreach ( $users as $user ) {
                $members[] = Member::load( $user );
            }
        }

        $form->element( 'dtprofiler_can_use', 'members' )
             ->tab( 'dtprofiler' )
             ->value( $members )
             ->options( [ 'multiple' => 10 ] );
        $form->element( 'dtprofiler_show_admin', 'yn' );
        $form->element( 'dtprofiler_enabled_execution', 'yn' )->header( 'dtprofiler_profiler_tabs' );
        $form->element( 'dtprofiler_enabled_executions', 'yn' );
        $form->element( 'dtprofiler_enabled_memory', 'yn' );
        $form->element( 'dtprofiler_enabled_memory_summary', 'yn' );
        $form->element( 'dtprofiler_enabled_files', 'yn' );
        $form->element( 'dtprofiler_enabled_enivro', 'yn' );
        $form->element( 'dtprofiler_enabled_templates', 'yn' );
        $form->element( 'dtprofiler_enabled_css', 'yn' );
        $form->element( 'dtprofiler_enabled_js', 'yn' );
        $form->element( 'dtprofiler_enabled_jsvars', 'yn' );
        $form->element( 'dtprofiler_enabled_logs', 'yn' );
        $form->element( 'dtprofiler_logs_amount', '#' );
        $form->element( 'dtprofiler_git_data', 'yn' );
        $form->element( 'dtprofiler_show_changes', 'yn' );
    }


    /**
     * formValues, format the values before saving as settings
     *
     * @param array $values
     *
     * @return void
     */
    public function formatValues( &$values ): void
    {
        $new = [];
        if( is_array( $values['dtprofiler_can_use'] ) ) {
            foreach ( $values[ 'dtprofiler_can_use' ] as $key => $value ) {
                $new[] = $value->member_id;
            }

            $values[ 'dtprofiler_can_use' ] = json_encode( $new );
        }
    }
}
