<?php

/**
 * @brief       Extension Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Proxy Class Generator
 * @since       1.3.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\Proxy\Helpers;

use function mb_strtolower;


class _Extension
{
    protected function loop( array $elements, &$classDoc )
    {
        $prefix = \null;
        if ( isset( $elements[ 'prefix' ] ) ) {
            $prefix = $elements[ 'prefix' ];
        }

        foreach ( $elements as $el ) {
            if ( isset( $el[ 'name' ] ) && $el[ 'name' ] !== 'namespace' ) {
                if ( isset( $el[ 'class' ] ) && 'stack' === mb_strtolower( $el[ 'class' ] ) ) {
                    $key = 'array';
                }
                else {
                    $key = 'string';
                }

                $classDoc[ $el[ 'name' ] ] = [ 'pt' => 'p', 'prop' => "{$prefix}{$el['name']}", 'type' => $key ];
            }
        }
    }

}
