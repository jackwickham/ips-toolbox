<?php

/**
 * @brief       Build Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.2.0
 * @version     -storm_version-
 */

namespace IPS\toolbox;

use Exception;
use IPS\Application;
use IPS\Application\BuilderIterator;
use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Log;
use IPS\Member;
use IPS\Output;
use IPS\Patterns\Singleton;
use IPS\Request;
use Phar;
use PharData;
use RuntimeException;
use ZipArchive;

class _Build extends Singleton
{

    protected static $instance;

    /**
     * Undocumented function
     *
     * @return void
     */
    public function export()
    {

        if ( !Application::appIsEnabled( 'toolbox' ) || !\IPS\IN_DEV ) {
            throw new \InvalidArgumentException( 'toolbox not installed' );
        }

        $app = Request::i()->appKey;
        \IPS\toolbox\Application::loadAutoLoader();
        $application = Application::load( $app );
        $title = $application->_title;
        Member::loggedIn()->language()->parseOutputForDisplay( $title );
        $e = [];
        $newLong = $application->long_version + 1;
        $exploded = explode( '.', $application->version );
        $newShort = "{$exploded[0]}.{$exploded[1]}." . ( (int)$exploded[ 2 ] + 1 );
        $e = [];

        $e[] = [
            'name'     => 'toolbox_long_version',
            'class'    => '#',
            'label'    => 'Long Version',
            'required' => \true,
            'default'  => $newLong,
        ];
        $e[] = [
            'name'     => 'toolbox_short_version',
            'label'    => 'Short Version',
            'required' => \true,
            'default'  => $newShort,
        ];

        $e[] = [
            'name'        => 'toolbox_skip_dir',
            'class'       => 'stack',
            'label'       => 'Skip Directories',
            'default'     => [ '3rdparty', 'vendor' ],
            'description' => 'Folders to skip using slasher on.',
        ];

        $e[] = [
            'name'        => 'toolbox_skip_files',
            'class'       => 'stack',
            'label'       => 'Skip Files',
            'description' => 'Files to skip using slasher on.',
        ];
        $form = Forms::execute( [ 'elements' => $e ] );

        if ( $values = $form->values() ) {
            $long = $values[ 'toolbox_long_version' ];
            $short = $values[ 'toolbox_short_version' ];
            $application->long_version = $long;
            $application->version = $short;
            $application->save();
            unset( Store::i()->applications );
            $path = \IPS\ROOT_PATH . '/' . $application->directory . '/' . $short . '/';

            try {
                Slasher::i()->start( $application, $values[ 'toolbox_skip_files' ] ?? [], $values[ 'toolbox_skip_dir' ] ?? [] );

                try {
                    $application->assignNewVersion( $long, $short );
                    $application->build();
                    $application->save();
                    if ( !is_dir( $path ) ) {
                        if ( !mkdir( $path, \IPS\IPS_FOLDER_PERMISSION, \true ) && !is_dir( $path ) ) {
                            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $path ) );
                        }
                        chmod( $path, \IPS\IPS_FOLDER_PERMISSION );
                    }
                    $pharPath = $path . $application->directory . '.tar';
                    $download = new PharData( $pharPath, 0, $application->directory . '.tar', Phar::TAR );
                    $download->buildFromIterator( new BuilderIterator( $application ) );
                } catch ( Exception $e ) {
                    Log::log( $e, 'phar' );
                }
            } catch ( Exception $e ) {
                Log::log( $e, 'phar' );

            }

            $directions = \IPS\ROOT_PATH . '/applications/' . $application->directory . '/data/defaults/instructions.txt';
            $apps = [];

            if ( is_file( $directions ) ) {
                copy( $directions, $path . 'instructions.txt' );
                $apps[] = 'instructions.txt';
            }

            $apps[] = $application->directory . '.tar';

            $zip = new ZipArchive;
            if ( $zip->open( $path . $title . ' - ' . $short . '.zip', ZIPARCHIVE::CREATE ) === \true ) {
                foreach ( $apps as $app ) {
                    $zip->addFile( $path . $app, $app );
                }
                $zip->close();
            }
            unset( Store::i()->applications, $download );
            \Phar::unlinkArchive( $pharPath );
            $url = Url::internal( 'app=core&module=applications&controller=applications' );
            Output::i()->redirect( $url, $application->_title . ' successfully built!' );
        }

        Output::i()->title = 'Build ' . $application->_title;
        Output::i()->output = $form;
    }
}
