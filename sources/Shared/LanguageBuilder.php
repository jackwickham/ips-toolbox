<?php

/**
 * @brief       Language Trait
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Shared;

\IPS\toolbox\Application::loadAutoLoader();

use Exception;
use IPS\Application;
use IPS\toolbox\Profiler\Debug;
use Symfony\Component\Filesystem\Filesystem;
use function defined;
use function file_put_contents;
use function header;
use function is_file;
use function var_export;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

trait LanguageBuilder
{
    /**
     * @param             $key
     * @param             $value
     * @param Application $application
     */
    protected function _addToLangs( $key, $value, Application $application )
    {
        $lang = [];
        $dir = \IPS\ROOT_PATH . "/applications/{$application->directory}/dev/";
        $file = $dir . 'lang.php';

        try {
            $fs = new Filesystem();

            if ( !$fs->exists( $dir ) ) {
                $fs->mkdir( $dir, \IPS\IPS_FOLDER_PERMISSION );
                $fs->chmod( $dir, \IPS\IPS_FOLDER_PERMISSION );
            }

            if ( is_file( $file ) ) {
                require $file;
            }

            $lang[ $key ] = $value;

            file_put_contents( $file, "<?php\n\n \$lang = " . var_export( $lang, \true ) . ";\n" );
        } catch ( Exception $e ) {
            Debug::add( 'Languages Creationg', $e, \true );
        }
    }
}
