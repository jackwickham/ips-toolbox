<?php

/**
 * @brief       ReservedWords Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox;

use function in_array;
use function mb_strtolower;

class _ReservedWords
{
    /**
     * a list of reserved kw in php, that class names can not be, this isn't an exhaustive list
     *
     * @var array
     */
    public static $reserved = [
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
    ];

    /**
     * ReservedWords constructor.
     */
    public function __construct()
    {

    }

    /**
     * checks to make sure a trait/interface/class name isn't a reserved word in php!
     *
     * @param $data
     *
     * @return bool
     */
    public static function check( $data ): bool
    {
        if ( in_array( mb_strtolower( $data ), static::$reserved, \true ) ) {
            return \true;
        }

        return \false;
    }

    /**
     * returns the reserved words list
     *
     * @return array
     */
    public static function get(): array
    {
        return static::$reserved;
    }
}
