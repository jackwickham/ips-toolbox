<?php

/**
 * @brief       Dtdevplus Headerdoc extension: Header
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\DevCenter\Headerdoc;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * header
 */
class _header extends \IPS\toolbox\DevCenter\Headerdoc\HeaderdocAbstract
{

    /**
     * enable headerdoc
     **/
    public function enabled()
    {
        return \true;
    }

    /**
     * if enabled, will add a blank index.html to each folder
     **/
    public function indexEnabled()
    {
        return \true;
    }

    /**
     * files to skip during building of the tar
     **/
    public function filesSkip( &$skip )
    {
    }

    /**
     * directories to skip during building of the tar
     **/
    public function dirSkip( &$skip )
    {
    }

    /**
     * an array of files/folders to exclude in the headerdoc
     **/
    public function exclude( &$skip )
    {

    }
}
