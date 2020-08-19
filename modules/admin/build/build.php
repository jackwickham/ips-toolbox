<?php

/**
 * @brief       Build Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.0.2
 * @version     -storm_version-
 */

namespace IPS\toolbox\modules\admin\build;

use function explode;

use Exception;
use IPS\Application;
use IPS\Application\BuilderIterator;
use IPS\Db;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;
use IPS\Task;
use IPS\toolbox\DevFolder\Applications;
use IPS\toolbox\Form;
use IPS\toolbox\Profiler\Debug;
use Phar;
use PharData;
use RuntimeException;
use Slasher\Slasher;
use ZipArchive;
use function chmod;
use function copy;
use function defined;
use function file_get_contents;
use function header;
use function ini_set;
use function is_dir;
use function is_file;
use function json_decode;
use function mkdir;
use function preg_match;
use function preg_replace;
use function sprintf;
use function time;
use function unlink;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * build
 */
class _build extends Controller
{

    const APPS = [
        'toolbox',
        'toolbox',
        'dtproxy',
        'dtprofiler',
    ];

    /**
     * @throws RuntimeException
     */
    public function execute()
    {

        Dispatcher\Admin::i()->checkAcpPermission( 'build_manage' );
        parent::execute();
    }

    /**
     * @inheritdoc
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     * @throws Exception
     */
    protected function manage()
    {

        $apps = static::APPS;
        $application = Application::load( 'toolbox' );
        $exploded = explode( '.', $application->version );
        $human = "{$exploded[0]}.{$exploded[1]}." . ( (int)$exploded[ 2 ] + 1 );
        $long = (int)$application->long_version + 1;
        $form = Form::create();
        $form->add( 'long_version', 'number' )->label( 'Long Version' )->required()->empty( $long );
        $form->add( 'short_version' )->label( 'Short Version' )->required()->empty( $human );

        if ( $values = $form->values() ) {
            $long = $values[ 'long_version' ];
            $short = $values[ 'short_version' ];
            $path = \IPS\ROOT_PATH . \DIRECTORY_SEPARATOR . 'devtoolbox' . \DIRECTORY_SEPARATOR . $short . \DIRECTORY_SEPARATOR;

            if ( !is_dir( $path ) ) {
                if ( !mkdir( $path, \IPS\IPS_FOLDER_PERMISSION, \true ) && !is_dir( $path ) ) {
                    throw new RuntimeException( sprintf( 'Directory "%s" was not created', $path ) );
                }
                chmod( $path, \IPS\IPS_FOLDER_PERMISSION );
            }

            $slasherPath = \IPS\ROOT_PATH . '/applications/toolbox/sources/vendor/slasher.php';
            require_once $slasherPath;

            foreach ( $apps as $app ) {
                try {
                    $application = Application::load( $app );
                    $application->long_version = $long;
                    $application->version = $short;
                    $application->save();
                    //lets slash them before we go forward
                    $appPath = \IPS\ROOT_PATH . '/applications/' . $application->directory . '/';
                    $args = [
                        'foo.php',
                        $appPath,
                        '-all',
                        '-use',
                        '-skip=vendor,slasher.php',
                    ];

                    $slasher = new Slasher( $args, \true );
                    $slasher->execute();
                    $application->assignNewVersion( $long, $short );
                    try {
                        $application->build();
                        $pharPath = $path . $application->directory . '.tar';
                        $download = new PharData( $pharPath, 0, $application->directory . '.tar', Phar::TAR );
                        $download->buildFromIterator( new BuilderIterator( $application ) );
                        unset( $download );
                    } catch ( Exception $e ) {
                        Debug::add( 'phar', $e );
                    }

                } catch ( Exception $e ) {
                    Debug::add( 'other', $e );
                }
            }

            //create slasher phar
            //        ini_set('phar.readonly', 'Off');
            //        $slasher = $path.'slasher.phar';
            //        $phar = new Phar($slasher );
            //        $phar->startBuffering();
            //        $phar->setStub("#!/usr/bin/env php \n".$phar::createDefaultStub('index.php'));
            //        $phar->buildFromDirectory(\IPS\ROOT_PATH.'/applications/slasher/', '/\.php$/');
            //
            //        $phar->stopBuffering();

            //            copy(\IPS\ROOT_PATH . '/applications/slasher/slasher.php', $path . 'slasher.php');
            copy( \IPS\ROOT_PATH . '/applications/toolbox/data/defaults/install.txt', $path . 'install.txt' );

            $files = [];

            foreach ( $apps as $app ) {
                $files[] = $app . '.tar';
            }

            $files[] = 'install.txt';
            //            $files[] = 'slasher.php';
            $zip = new ZipArchive;
            if ( $zip->open( $path . 'Dev Toolbox - ' . $short . '.zip', ZIPARCHIVE::CREATE ) === \true ) {
                foreach ( $files as $file ) {
                    $zip->addFile( $path . $file, $file );
                }
                $zip->close();
            }

            foreach ( $files as $file ) {
                @unlink( $path . $file );
            }

            $url = Url::internal( 'app=core' )->csrf();
            Output::i()->redirect( $url, 'toolbox_apps_built' );
        }

        Output::i()->title = 'Build DevToolbox';
        Output::i()->output = $form;
    }

}
