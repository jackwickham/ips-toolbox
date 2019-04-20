<?php

/**
 * @brief       Write Trait
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Shared;

\IPS\toolbox\Application::loadAutoLoader();

use Exception;
use IPS\Application;
use IPS\providers\Profiler\Debug;
use IPS\toolbox\Proxy\Proxyclass;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

trait Write
{
    /**
     * set whether or not to create a proxy if dtproxy is installed
     *
     * @var bool
     */
    protected $proxy = \false;

    protected function _createDir( $dir )
    {
        try {
            $fs = new FileSystem;
            if ( !$fs->exists( $dir ) ) {
                $fs->mkdir( $dir, \IPS\IPS_FOLDER_PERMISSION );
                $fs->chmod( $dir, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( RuntimeException $e ) {
            Debug::add( 'Directory Creation Failure', $e );
        } catch ( Exception $e ) {
            Debug::add( 'Write Failure', $e );
        }
    }

    /**
     * @param string $file
     * @param string $content
     * @param string $dir
     */
    protected function _writeFile( string $file, string $content, string $dir, $append = \true )
    {
        try {
            $fs = new Filesystem();

            if ( !$fs->exists( $dir ) ) {
                $fs->mkdir( $dir, \IPS\IPS_FOLDER_PERMISSION );
                $fs->chmod( $dir, \IPS\IPS_FOLDER_PERMISSION );
            }

            if ( $append === \false ) {
                $fs->remove( $dir . '/' . $file );
            }

            $fs->appendToFile( $dir . '/' . $file, $content );
            $fs->chmod( $dir . '/' . $file, \IPS\IPS_FILE_PERMISSION );

            if ( $this->proxy && Application::appIsEnabled( 'dtproxy' ) ) {
                Proxyclass::i()->buildAndMake( $dir . '/' . $file );
            }
        } catch ( RuntimeException $e ) {
            Debug::add( 'Directory Creation Failure', $e );
        } catch ( Exception $e ) {
            Debug::add( 'Write Failure', $e );
        }
    }
}
