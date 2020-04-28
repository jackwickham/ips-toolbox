<?php

/**
 * @brief       Files Singleton
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler\Parsers;

use IPS\Patterns\Singleton;
use IPS\Theme;
use IPS\toolbox\Editor;
use function count;
use function defined;
use function get_included_files;
use function header;
use function json_encode;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Files extends Singleton
{

    /**
     * @brief   Singleton Instances
     * @note    This needs to be declared in any child class.
     */
    protected static $instance;

    /**
     * builds the files button
     *
     * @throws \UnexpectedValueException
     */
    public function build(): string
    {
        $files = get_included_files();
        $list = [];
        foreach ( $files as $key => $file ) {
            $url = ( new Editor )->replace( $file );
            $list[ $file ] = [ 'name' => $file, 'url' => $url ];
        }

        return Theme::i()->getTemplate( 'dtpsearch', 'toolbox', 'front' )->button( 'Files', 'files', 'Include Files.', $list, json_encode( $list ), count( $list ), 'file', \true, \false );
    }

}
