<?php

/**
 * @brief       Plugins Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Folders
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\modules\admin\devfolder;

use IPS\Application;
use IPS\Db;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevFolder\Plugins;
use IPS\toolbox\Form;
use IPS\Xml\XMLReader;
use UnderflowException;
use function copy;
use function defined;
use function header;
use function md5_file;
use function move_uploaded_file;
use function tempnam;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * plugins
 */
class _plugins extends Controller
{

    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function execute()
    {

        if ( \IPS\NO_WRITES === \true ) {
            Output::i()->error( 'Dev Folder generator can not be used for as NO_WRITES are enabled in constants.php.', '100foo' );
        }
        Dispatcher\Admin::i()->checkAcpPermission( 'plugins_manage' );
        if ( !Application::appIsEnabled( 'toolbox' ) ) {
            Output::i()->error( 'Sorry you need to have the Devtoolbox: Base app installed to continue.', '2DT100' );
        }
        parent::execute();
    }

    /**
     * @inheritdoc
     */
    protected function manage()
    {

        $form = Form::create()->formPrefix( 'dtdevfolder_plugin_' );
        $form->add( 'upload', 'upload' )->options( [
            'allowedFileTypes' => [ 'xml' ],
            'temporary'        => true,
        ] )->required();

        if ( $values = $form->values() ) {
            $xml = new XMLReader;
            $xml->open( $values[ 'upload' ] );

            if ( !@$xml->read() ) {
                Output::i()->error( 'xml_upload_invalid', '2C145/D', 403, '' );
            }

            try {
                Db::i()->select( 'plugin_id', 'core_plugins', [
                    'plugin_name=? AND plugin_author=?',
                    $xml->getAttribute( 'name' ),
                    $xml->getAttribute( 'author' ),
                ] )->first();

                $tempFileStir = tempnam( \IPS\TEMP_DIRECTORY, 'IPSStorm' );
                move_uploaded_file( $values[ 'upload' ], $tempFileStir );
                Output::i()->redirect( $this->url->setQueryString( [
                    'do'    => 'doDev',
                    'storm' => $tempFileStir,
                ] ) );
            } catch ( UnderflowException $e ) {
                $tempFile = tempnam( \IPS\TEMP_DIRECTORY, 'IPS' );
                move_uploaded_file( $values[ 'upload' ], $tempFile );
                $secondTemp = tempnam( \IPS\TEMP_DIRECTORY, 'Storm' );
                copy( $tempFile, $secondTemp );
                $url = Url::internal( 'app=core&module=applications&controller=plugins&do=doInstall' )->setQueryString( [
                    'file'  => $tempFile,
                    'key'   => md5_file( $tempFile ),
                    'storm' => $secondTemp,
                ] );

                if ( isset( Request::i()->id ) ) {
                    $url = $url->setQueryString( 'id', Request::i()->id );
                }

                Output::i()->redirect( $url );
            }
        }

        Output::i()->title = Member::loggedIn()->language()->addToStack( 'dtdevfolder_plugins_title' );
        Output::i()->output = $form;
    }

    /**
     * builds the dev files
     */
    protected function doDev()
    {

        Plugins::i()->finish( Request::i()->storm );
    }
}
