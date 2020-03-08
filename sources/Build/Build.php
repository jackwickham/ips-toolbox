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
use IPS\toolbox\Profiler\Debug;
use function explode;
use IPS\Application;
use IPS\Application\BuilderIterator;
use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Patterns\Singleton;
use IPS\Request;
use Phar;
use PharData;
use RuntimeException;

\IPS\toolbox\Application::loadAutoLoader();

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
        $application = Application::load( $app );
        $title = $application->_title;
        Member::loggedIn()->language()->parseOutputForDisplay( $title );

        $newLong = $application->long_version + 1;

        if ( empty( $application->version ) !== true ) {
            $exploded = explode( '.', $application->version );
            $newShort = "{$exploded[0]}.{$exploded[1]}." . ( (int)$exploded[ 2 ] + 1 );
        }
        else {
            $newShort = '1.0.0';
            $newLong = 10000;
        }

        $form = Form::create();
        $form->add( 'toolbox_long_version', 'number' )->label( 'Long Version' )->required()->empty( $newLong );
        $form->add( 'toolbox_short_version' )->label( 'Short Version' )->required()->empty( $newShort );
        $form->add( 'toolbox_skip_dir', 'stack' )->label( 'Skip Directories' )->description( 'Folders to skip using slasher on.' )->empty( [
            '3rdparty',
            'vendor',
        ] );
        $form->add( 'toolbox_skip_files', 'stack' )->label( 'Skip Files' )->description( 'Files to skip using slasher on.' );

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
                        if ( !mkdir( $path, \IPS\IPS_FOLDER_PERMISSION, true ) && !is_dir( $path ) ) {
                            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $path ) );
                        }
                        chmod( $path, \IPS\IPS_FOLDER_PERMISSION );
                    }
                    $pharPath = $path . $application->directory . ' - ' . $application->version . '.tar';
                    $download = new PharData( $pharPath, 0, $application->directory . '.tar', Phar::TAR );
                    $download->buildFromIterator( new BuilderIterator( $application ) );
                } catch ( Exception $e ) {
                    Debug::log( $e, 'phar' );
                }
            } catch ( Exception $e ) {
                Debug::log( $e, 'phar' );

            }

            unset( Store::i()->applications, $download );
            //            Phar::unlinkArchive( $pharPath );
            $url = Url::internal( 'app=core&module=applications&controller=applications' );
            Output::i()->redirect( $url, $application->_title . ' successfully built!' );
        }

        Output::i()->title = 'Build ' . $application->_title;
        Output::i()->output = $form;
    }
}
