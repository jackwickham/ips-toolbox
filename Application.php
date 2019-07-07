<?php
/**
 * @brief            Dev Toolbox: Base Application Class
 * @author           -storm_author-
 * @copyright        -storm_copyright-
 * @package          Invision Community
 * @subpackage       Dev Toolbox: Base
 * @since            02 Apr 2018
 * @version          -storm_version-
 */

namespace IPS\toolbox;

use IPS\Application;

/**
 * Dev Toolbox: Base Application Class
 */
class _Application extends Application
{
    public static $toolBoxApps = [
        'toolbox',
        'toolbox',
        'dtproxy',
        'dtprofiler',
    ];
    /**
     * @var string
     */
    protected static $baseDir = \IPS\ROOT_PATH . '/applications/toolbox/sources/vendor/';

    protected static $loaded = \false;

    public static function loadAutoLoader(): void
    {
        if ( static::$loaded === \false ) {
            static::$loaded = \true;
            require static::$baseDir . '/autoload.php';
        }
    }

    public static function templateSlasher( $source )
    {
        $replace = [
            'array_slice',
            'boolval',
            'chr',
            'count',
            'doubleval',
            'floatval',
            'func_get_args',
            'func_get_args',
            'func_num_args',
            'get_called_class',
            'get_class',
            'gettype',
            'in_array',
            'intval',
            'is_array',
            'is_bool',
            'is_double',
            'is_float',
            'is_int',
            'is_integer',
            'is_long',
            'is_null',
            'is_numeric',
            'is_object',
            'is_real',
            'is_resource',
            'is_string',
            'ord',
            'strval',
            'function_exists',
            'is_callable',
            'extension_loaded',
            'dirname',
            'constant',
            'define',
            'call_user_func',
            'call_user_func_array',
        ];

        foreach ( $replace as $value ) {
            $rep = '\\' . $value;
            $callback = function ( $m ) use ( $rep )
            {
                return $rep;
            };
            $source = preg_replace_callback( "#(?<!\\\\)\b" . $value . '\b#u', $callback, $source );
            $source = str_replace( [ 'function \\', 'const \\', "::\\", "$\\", "->\\" ], [
                'function ',
                'const ',
                '::',
                '$',
                '->',
            ], $source );

        }

        return $source;
    }

    /**
     * @inheritdoc
     */
    protected function get__icon()
    {
        return 'wrench';
    }
}
