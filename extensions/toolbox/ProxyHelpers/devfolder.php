<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Devfolder
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Folders
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use IPS\toolbox\Generator\Builders\ClassGenerator;
use function defined;
use function header;

/**
 * devfolder
 */
class _devfolder
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param ClassGenerator $classGenerator
     */
    public function store( ClassGenerator $classGenerator )
    {

    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param ClassGenerator $classDoc
     */
    public function request( ClassGenerator $classGenerator )
    {

        $classGenerator->addPropertyTag( 'dtdevfolder_app', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'storm', [ 'hint' => 'string' ] );

    }
}
