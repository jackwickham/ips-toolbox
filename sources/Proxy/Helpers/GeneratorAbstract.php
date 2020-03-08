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

use function mb_strtolower;


class _GeneratorAbstract implements HelpersAbstract
{
    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
//        $el = Sources::i()->elements();
//        foreach ( $el as $val ) {
//            if ( isset( $val[ 'name' ] ) ) {
//                if ( isset( $val[ 'class' ] ) && 'stack' === mb_strtolower( $val[ 'class' ] ) ) {
//                    $classDoc[] = [ 'pt' => 'p', 'prop' => $val[ 'name' ], 'type' => 'array' ];
//                    $classDoc[] = [ 'pt' => 'p', 'prop' => 'dtdevplus_class_' . $val[ 'name' ], 'type' => 'array' ];
//                }
//                else {
//                    $classDoc[] = [ 'pt' => 'p', 'prop' => $val[ 'name' ], 'type' => 'string' ];
//                    $classDoc[] = [ 'pt' => 'p', 'prop' => 'dtdevplus_class_' . $val[ 'name' ], 'type' => 'string' ];
//                }
//            }
//        }

        $classDoc[] = ['pt' => 'p', 'prop' => 'app', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'classname', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'header', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'brief', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'classname_lower', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'extendedParent', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'mixin', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'mixinClass', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'profiler', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'baseNameSpace', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'base_item_node_class', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => '_classname', 'type' => 'string'];
    }
}
