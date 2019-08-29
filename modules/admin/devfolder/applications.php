<?php

/**
 * @brief       Apps Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Folders
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\modules\admin\devfolder;

use InvalidArgumentException;
use IPS\Application;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Helpers\MultipleRedirect;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevFolder\Applications;
use IPS\toolbox\Form;
use function defined;
use function file_exists;
use function header;
use function in_array;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _applications extends Controller
{

    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function execute()
    {

        if ( \IPS\NO_WRITES === true ) {
            Output::i()->error( 'Dev Folder generator can not be used for as NO_WRITES are enabled in constants.php.', '100foo' );
        }
        Dispatcher\Admin::i()->checkAcpPermission( 'apps_manage' );
        if ( !Application::appIsEnabled( 'toolbox' ) ) {
            Output::i()->error( 'Sorry you need to have the Devtoolbox: Base app installed to continue.', '2DT100' );
        }
        parent::execute();
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     * @throws \Exception
     * @throws \RuntimeException
     */
    protected function manage()
    {

        $groups[ 'select' ] = Member::loggedIn()->language()->addToStack( 'dtdevfolder_apps_select' );

        foreach ( Application::applications() as $key => $val ) {
            if ( !in_array( $val->directory, Application::$ipsApps, true ) ) {
                $groups[ $val->directory ] = Member::loggedIn()->language()->addToStack( "__app_{$val->directory}" );
            }
        }

        $langs = [
            'select'     => Member::loggedIn()->language()->addToStack( 'dtdevfolder_type_select' ),
            'all'        => Member::loggedIn()->language()->addToStack( 'dtdevfolder_type_all' ),
            'language'   => Member::loggedIn()->language()->addToStack( 'dtdevfolder_type_lang' ),
            'javascript' => Member::loggedIn()->language()->addToStack( 'dtdevfolder_type_js' ),
            'templates'  => Member::loggedIn()->language()->addToStack( 'dtdevfolder_type_template' ),
            'email'      => Member::loggedIn()->language()->addToStack( 'dtdevfolder_type_email' ),
        ];

        /**
         * @param $data
         */
        $form = Form::create()->formPrefix( 'dtdevfolder_app' );
        $validate = static function ( $data )
        {

            if ( $data === 'select' ) {
                throw new InvalidArgumentException( 'form_bad_value' );
            }
        };
        $form->add( 'app', 'select' )->options( [ 'options' => $groups ] )->appearRequired()->validation( $validate )->empty( 'select' );
        $validation = static function ( $data )
        {

            if ( $data === 'select' ) {
                throw new InvalidArgumentException( 'form_bad_value' );
            }
            $app = Request::i()->dtdevfolder_app;
            $folders = \IPS\ROOT_PATH . "/applications/{$app}/dev";
            $f = $folders;
            $folders2 = false;
            $folders3 = false;

            if ( $data !== 'all' ) {
                switch ( $data ) {
                    case 'language':
                        $folders .= '/lang.php';
                        $folders2 = $f . '/jslang.php';
                        break;
                    case 'javascript':
                        $folders .= '/js/';
                        break;
                    case 'templates':
                        $folders .= '/html/';
                        $folders2 = $f . '/css/';
                        $folders3 = $f . '/resources/';
                        break;
                    case 'email':
                        $folders .= '/email/';
                        break;
                }
            }

            if ( file_exists( $folders ) || ( $folders2 && file_exists( $folders2 ) && $folders = $folders2 ) || ( $folders3 && file_exists( $folders3 ) && $folders = $folders3 ) ) {
                $lang = Member::loggedIn()->language()->addToStack( 'dtdevfolder_folder_exist', false, [ 'sprintf' => $folders ] );
                throw new InvalidArgumentException( $lang );
            }
        };
        $form->add( 'type', 'select' )->options( [ 'options' => $langs ] )->validation( $validation )->appearRequired();

        if ( $values = $form->values() ) {
            $app = $values[ 'app' ];
            $type = $values[ 'type' ];

            if ( $type === 'all' ) {
                Output::i()->redirect( $this->url->setQueryString( [ 'do' => 'queue', 'appKey' => $app ] ) );
            }
            else {
                $return = ( new Applications( $app ) )->{$type}();
                Output::i()->redirect( $this->url, $return );
            }
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack( 'dtdevfolder_title' );
        Output::i()->output = $form;
    }

    protected function queue()
    {

        Output::i()->title = Member::loggedIn()->language()->addToStack( 'dtdevfolder_queue_title' );

        $app = Request::i()->appKey;

        Output::i()->output = new MultipleRedirect( Url::internal( 'app=toolbox&module=devfolder&controller=applications&do=queue&appKey=' . $app ), static function ( $data )
        {

            $app = Request::i()->appKey;
            $next = null;
            $end = false;
            $do = $data[ 'next' ] ?? 'language';
            $done = 0;

            switch ( $do ) {
                case 'language':
                    ( new Applications( $app ) )->language();
                    $done = 25;
                    $next = 'javascript';
                    break;
                case 'javascript':
                    ( new Applications( $app ) )->javascript();
                    $done = 50;
                    $next = 'templates';
                    break;
                case 'templates':
                    ( new Applications( $app ) )->templates();
                    $done = 75;
                    $next = 'email';
                    break;
                case 'email':
                    ( new Applications( $app ) )->email();
                    $done = 100;
                    $next = 'default';
                    break;
                default:
                    $end = true;
                    break;
            }

            if ( $end ) {
                if ( $app === 'core' ) {
                    ( new Applications( $app ) )->core();
                }

                return null;
            }

            $language = Member::loggedIn()->language()->addToStack( 'dtdevfolder_total_done', false, [
                'sprintf' => [
                    $done,
                    100,
                ],
            ] );

            return [ [ 'next' => $next ], $language, $done ];
        }, static function ()
        {

            $app = Request::i()->appKey;
            $app = Member::loggedIn()->language()->addToStack( "__app_{$app}" );
            $msg = Member::loggedIn()->language()->addToStack( 'dtdevfolder_completed', false, [ 'sprintf' => [ $app ] ] );
            $url = Url::internal( 'app=toolbox&module=devfolder&controller=applications' );
            /* And redirect back to the overview screen */
            Output::i()->redirect( $url, $msg );
        } );
    }
}
