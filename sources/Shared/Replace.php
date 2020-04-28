<?php

/**
 * @brief       Replace Trait
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Shared;

use function str_replace;

trait Replace
{
    /**
     * find's and replaces strings in another string.
     *
     * @param $find
     * @param $replace
     * @param $subject
     *
     * @return string
     */
    protected function _replace( $find, $replace, $subject ): string
    {
        return str_replace( $find, $replace, $subject );
    }
}
