<?php

/**
 * @brief       IPSdtdevplusSourcesCompilerCompilerAbstract Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Proxy Class Generator
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Proxy\Helpers;

use IPS\dtdevplus\Sources;
use Generator\Builders\ClassGenerator;
use function mb_strtolower;

class _GeneratorAbstract implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process( $class, ClassGenerator $classGenerator, &$classExtends )
    {

        $classGenerator->addPropertyTag( 'app', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'classname', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'header', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'brief', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'classname_lower', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'extendedParent', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'mixin', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'mixinClass', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'profiler', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'baseNameSpace', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'base_item_node_class', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( '_classname', [ 'hint' => 'string' ] );

    }
}
