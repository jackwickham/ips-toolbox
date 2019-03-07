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
use IPS\toolbox\Forms;
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
        $e = [];
        $e[] = [
            'name'     => 'long_version',
            'class'    => '#',
            'label'    => 'Long Version',
            'required' => \true,
            'default'  => $long,
        ];
        $e[] = [
            'name'     => 'short_version',
            'label'    => 'Short Version',
            'required' => \true,
            'default'  => $human,
        ];

        $form = Forms::execute( [ 'elements' => $e ] );

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

            $url = Url::internal( 'app=core' );
            Output::i()->redirect( $url, 'toolbox_apps_built' );
        }

        Output::i()->title = 'Build DevToolbox';
        Output::i()->output = $form;
    }

    /**
     * @throws Db\Exception
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     * @throws \OverflowException
     * @throws \UnderflowException
     * @throws \Exception
     */
    protected function install()
    {
        $apps = static::APPS;
        ini_set( 'max_execution_time', 0 );
        foreach ( $apps as $app ) {
            $path = \IPS\ROOT_PATH . '/applications/' . $app;
            if ( !is_dir( $path ) ) {
                continue;
            }
            try {
                Application::load( $app );
            } catch ( Exception $e ) {
                $application = json_decode( file_get_contents( $path . '/data/application.json' ), \true );

                if ( !$application[ 'app_directory' ] ) {
                    Output::i()->error( 'app_invalid_data', '4C133/5', 403, '' );
                }

                $application[ 'app_position' ] = Db::i()->select( 'MAX(app_position)', 'core_applications' )->first() + 1;
                $application[ 'app_added' ] = time();
                $application[ 'app_protected' ] = 0;
                $application[ 'app_enabled' ] = 1;
                unset( $application[ 'application_title' ] );
                Db::i()->insert( 'core_applications', $application );
                Application::load( $app )->installDatabaseSchema();
                Application::load( $app )->installJsonData();
                Application::load( $app )->installLanguages( 0, 100000 );
                Application::load( $app )->installEmailTemplates();
                Application::load( $app )->installExtensions();
                Application::load( $app )->installThemeSettings();
                Application::load( $app )->clearTemplates();
                Application::load( $app )->installTemplates( \false, 0, 10000 );
                Application::load( $app )->installJavascript();
                Application::load( $app )->installOther();
            }
        }

        $url = Url::internal( 'app=core' );
        Output::i()->redirect( $url, 'Applications installed' );
    }

    protected function uninstall()
    {
        Request::i()->confirmedDelete();
        $apps = static::APPS;
        ini_set( 'max_execution_time', 0 );
        foreach ( $apps as $app ) {
            try {
                $app = Application::load( $app );
                $app->delete();
            } catch ( Exception $e ) {
            }
        }

        $url = Url::internal( 'app=core' );
        Output::i()->redirect( $url, 'Applications Uninstalled' );
    }

    /**
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws Exception
     */
    protected function buildDev()
    {
        ini_set( 'max_execution_time', 0 );
        $ipsApps = Application::$ipsApps;

        foreach ( $ipsApps as $app ) {
            try {
                $df = ( new Applications( $app ) )->javascript()->email()->language()->templates();
            } catch ( Exception $e ) {

            }
        }

        $df->core();

        Task::queue( 'dtproxy', 'dtProxy', [], 5, [ 'dtProxy' ] );
        $url = Url::internal( 'app=core' );
        Output::i()->redirect( $url, 'IPS Dev Folders Created' );
    }

    protected function inDev()
    {
        if ( \IPS\NO_WRITES === \true ) {
            return;
        }
        $constants = \IPS\ROOT_PATH . '/constants.php';
        $content = '';
        $inDev = \IPS\IN_DEV ? '\false' : '\true';
        $msg = 'IN_DEV Enabled';
        if ( \IPS\IN_DEV === \true ) {
            $msg = 'IN_DEV Disabled';
        }
        $replace = 'define( \'IN_DEV\',' . (string)$inDev . ');';
        if ( is_file( $constants ) ) {
            $content = file_get_contents( $constants );
            preg_match( '#define\(\s?[\'|"]IN_DEV[\'|"]\s?,(.*?)\s?\);#', $content, $match );

            if ( empty( $match ) ) {
                $content .= "\ndefine('IN_DEV',\\true);";
            }
            else {
                $content = preg_replace( '#define\(\s?[\'|"]IN_DEV[\'|"]\s?,(.*?)\s?\);#', $replace, $content );
            }
        }
        else {
            $content = <<<eof
<?php
define( 'IN_DEV', {$inDev} );
eof;

        }

        \file_put_contents( $constants, $content );
        $url = Url::internal( 'app=core' );
        Output::i()->redirect( $url, $msg );
    }

    protected function devBanner()
    {
        $constants = \IPS\ROOT_PATH . '/constants.php';
        $content = '';
        $devBanner = \IPS\DEV_HIDE_DEV_TOOLS ? '\false' : '\true';
        $msg = 'DEV_HIDE_DEV_TOOLS Enabled';
        if ( \IPS\IN_DEV === \true ) {
            $msg = 'DEV_HIDE_DEV_TOOLS Disabled';
        }
        $replace = 'define( \'DEV_HIDE_DEV_TOOLS\',' . (string)$devBanner . ');';
        if ( is_file( $constants ) ) {
            $content = file_get_contents( $constants );
            preg_match( '#define\(\s?[\'|"]DEV_HIDE_DEV_TOOLS[\'|"]\s?,(.*?)\s?\);#', $content, $match );

            if ( empty( $match ) ) {
                $content .= "\ndefine('DEV_HIDE_DEV_TOOLS',\\true);";
            }
            else {
                $content = preg_replace( '#define\(\s?[\'|"]DEV_HIDE_DEV_TOOLS[\'|"]\s?,(.*?)\s?\);#', $replace, $content );
            }

        }
        else {
            $content = <<<eof
<?php
define( 'DEV_HIDE_DEV_TOOLS', {$devBanner} );
eof;

        }
        \file_put_contents( $constants, $content );

        $url = Url::internal( 'app=core' );
        Output::i()->redirect( $url, $msg );
    }

}
