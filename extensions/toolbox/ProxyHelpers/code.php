<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Code
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Code Analyzer
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use IPS\toolbox\Generator\Builders\ClassGenerator;
use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * code
 */
class _code
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param ClassGenerator $classDoc
     */
    public function store( ClassGenerator $classGenerator )
    {

        $classGenerator->addPropertyTag( 'dtcode_warnings', [ 'hint' => 'array' ] );
    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param ClassGenerator $classGenerator
     */
    public function request( ClassGenerator $classGenerator )
    {

    }
}
