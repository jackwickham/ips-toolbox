<?php

/**
 * @brief       HelpersAbstract Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Proxy Class Generator
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Proxy\Helpers;

use Generator\Builders\ClassGenerator;

interface HelpersAbstract
{

    /**
     * the method that can be used to add extra data to a proxy before it is written.
     *
     * @param string         $class        the class with NS
     * @param ClassGenerator $classGenerator
     * @param array          $classDoc     if a class doc was built (if it is a AR descendant it will most likely have
     *                                     one)
     * @param string         $classExtends the IPS Class it is extending
     *
     * @return void
     */
    public function process( $class, ClassGenerator $classGenerator, &$classExtends );
}
