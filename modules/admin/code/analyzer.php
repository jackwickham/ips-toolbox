<?php

/**
 * @brief       View Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Code Analyzer
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\modules\admin\code;

use IPS\Application;
use IPS\Data\Store;
use IPS\Dispatcher\Admin;
use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\Helpers\MultipleRedirect;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\Theme;
use IPS\toolbox\Code\Langs;
use IPS\toolbox\Code\Settings;
use function array_merge;
use function count;
use function defined;
use function header;
use function in_array;
use function is_array;
use function ksort;
use function round;

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * view
 */
class _analyzer extends Controller
{

    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function execute()
    {

        Output::i()->cssFiles = array_merge( Output::i()->cssFiles, Theme::i()->css( 'dtcode.css', 'toolbox', 'admin' ) );

        Output::i()->jsFiles = array_merge( Output::i()->jsFiles, Output::i()->js( 'admin_toggles.js', 'toolbox', 'admin' ) );

        Admin::i()->checkAcpPermission( 'view_manage' );

        parent::execute();
    }

    /**
     * @inheritdoc
     */
    protected function manage()
    {

        $form = new Form();

        foreach ( Application::applications() as $key => $val ) {
            if ( !defined( 'DTCODE_NO_SKIP' ) && in_array( $val->directory, Application::$ipsApps, \true ) ) {
                continue;
            }
            $apps[ $val->directory ] = Member::loggedIn()->language()->addToStack( "__app_{$val->directory}" );
        }

        ksort( $apps );

        $apps = new Form\Select( 'dtcode_app', \null, \true, [
            'options' => $apps,
        ] );

        $form->add( $apps );

        if ( $values = $form->values() ) {
            Output::i()->redirect( $this->url->setQueryString( [
                'do'          => 'queue',
                'application' => $values[ 'dtcode_app' ],
            ] )->csrf() );
        }

        Output::i()->output = $form;
    }

    /**
     *
     * @throws \Exception
     */
    protected function queue()
    {

        Output::i()->output = new MultipleRedirect(

            $this->url->setQueryString( [
                'do'          => 'queue',
                'application' => Request::i()->application,
            ] )->csrf(), function ( $data )
        {

            $total = 4;
            $percent = round( 100 / $total );
            $step = 'langs_check';
            $complete = 0;

            $app = Request::i()->application;

            if ( is_array( $data ) && isset( $data[ 'step' ] ) ) {
                $step = $data[ 'step' ];
                $app = $data[ 'app' ];
            }

            $warnings = [];

            if ( $step !== 'langs_check' && isset( Store::i()->dtcode_warnings ) ) {
                $warnings = Store::i()->dtcode_warnings;
            }

            switch ( $step ) {
                case 'langs_check':
                    $warnings[ 'langs_check' ] = ( new Langs( $app ) )->check();
                    $step = 'langs_verify';
                    $complete = 1;
                    break;
                case 'langs_verify':
                    $warnings[ 'langs_verify' ] = ( new Langs( $app ) )->verify();
                    $step = 'settings_check';
                    $complete = 2;
                    break;
                case 'settings_check':
                    $warnings[ 'settings_check' ] = ( new Settings( $app ) )->buildSettings()->check();
                    $step = 'settings_verify';
                    $complete = 3;
                    break;
                case 'settings_verify':
                    $warnings[ 'settings_verify' ] = ( new Settings( $app ) )->buildSettings()->verify();
                    $step = 'final';
                    $complete = 4;
                    break;
                case 'final':
                    $step = \null;
                    break;
            }

            Store::i()->dtcode_warnings = $warnings;

            if ( $step === \null ) {
                return \null;
            }

            $language = Member::loggedIn()->language()->addToStack( 'dtcode_queue_complete', \false, [
                'sprintf' => [
                    $step,
                    $complete,
                    $total,
                ],
            ] );

            return [
                [ 'step' => $step, 'app' => $app ],
                $language,
                $percent * $complete,
            ];
        }, function ()
        {

            $url = Url::internal( 'app=toolbox&module=code&controller=analyzer&do=results' );
            $url->setQueryString( [ 'application' => Request::i()->application ] );
            Output::i()->redirect( $url->csrf(), 'dtcode_analyzer_complete' );
        } );
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     * @throws \UnexpectedValueException
     */
    protected function results()
    {

        $app = \null;
        if ( isset( Request::i()->application ) ) {
            $app = Application::load( Request::i()->application );
        }

        $title = 'dtcode_results';
        $options = [];
        if ( $app !== \null ) {
            $title = 'dtcode_results_app';
            $options = [ 'sprintf' => [ Member::loggedIn()->language()->addToStack( '__app_' . $app->directory ) ] ];
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack( $title, \false, $options );

        if ( isset( Store::i()->dtcode_warnings ) ) {
            /**
             * @var array $warnings
             */
            $warnings = Store::i()->dtcode_warnings;
            foreach ( $warnings as $key => $val ) {
                if ( $key === 'langs_check' ) {
                    if ( isset( $val[ 'langs' ] ) && count( $val[ 'langs' ] ) ) {
                        Output::i()->output .= Theme::i()->getTemplate( 'results' )->lists( $val[ 'langs' ], 'dtcode_langs_php' );
                    }

                    if ( isset( $val[ 'jslangs' ] ) && count( $val[ 'jslangs' ] ) ) {
                        Output::i()->output .= Theme::i()->getTemplate( 'results' )->lists( $val[ 'jslangs' ], 'dtcode_jslangs_php' );
                    }
                }

                if ( $key === 'langs_verify' ) {
                    Output::i()->output .= Theme::i()->getTemplate( 'results' )->files( $val, 'dtcode_langs_verify' );
                }

                if ( $key === 'settings_check' ) {
                    Output::i()->output .= Theme::i()->getTemplate( 'results' )->lists( $val, 'dtcode_settings_check' );
                }

                if ( $key === 'settings_verify' ) {
                    Output::i()->output .= Theme::i()->getTemplate( 'results' )->files( $val, 'dtcode_settings_verify' );
                }
            }
        }
    }
}
