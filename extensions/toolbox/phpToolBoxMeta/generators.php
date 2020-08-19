<?php

/**
 * @brief       Toolbox Phptoolboxmeta extension: Generators
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\extensions\toolbox\phpToolBoxMeta;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * generators
 */
class _generators
{

    public function addJsonMeta( &$jsonMeta )
    {

        $jsonMeta[ 'registrar' ][] = [
            'signatures' => [
                [
                    'class'  => 'Generator\\Builders\\Traits\\ClassMethods',
                    'method' => 'addMethod',
                    'index'  => 3,
                    'type'   => 'array_key',
                ],
            ],
            'provider'   => 'ClassMethods',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'           => 'ClassMethods',
            'lookup_strings' => [
                'static',
                'abstract',
                'visibility',
                'final',
                'document',
                'returnType',
            ],
        ];

        $jsonMeta[ 'registrar' ][] = [
            'signatures' => [
                [
                    'class'  => 'Generator\\Builders\\Traits\\Constants',
                    'method' => 'addConst',
                    'index'  => 2,
                    'type'   => 'array_key',
                ],
            ],
            'provider'   => 'ConstMethod',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'           => 'ConstMethod',
            'lookup_strings' => [
                'visibility',
                'document',
            ],
        ];

        $jsonMeta[ 'registrar' ][] = [
            'signatures' => [
                [
                    'class'  => 'Generator\\Builders\\Traits\\Properties',
                    'method' => 'addProperty',
                    'index'  => 2,
                    'type'   => 'array_key',
                ],
            ],
            'provider'   => 'addPropertyMethod',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'           => 'addPropertyMethod',
            'lookup_strings' => [
                'document',
                'static',
                'visibility',
                'type',
            ],
        ];

        $jsonMeta[ 'registrar' ][] = [
            'signatures' => [
                [
                    'class'  => 'Generator\\Builders\\Traits\\Properties',
                    'method' => 'addPropertyTag',
                    'index'  => 1,
                    'type'   => 'array_key',
                ],
            ],
            'provider'   => 'addPropertyTagMethod',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'           => 'addPropertyTagMethod',
            'lookup_strings' => [
                'type',
                'hint',
                'comment',
            ],
        ];

        $jsonMeta[ 'registrar' ][] = [
            'signatures' => [
                [
                    'class'  => 'Generator\\Builders\\InterfaceGenerator',
                    'method' => 'addMethod',
                    'index'  => 3,
                    'type'   => 'array_key',
                ],
            ],
            'provider'   => 'InterfaceGeneratorTagMethod',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'           => 'InterfaceGeneratorTagMethod',
            'lookup_strings' => [
                'static',
                'visibility',
                'final',
                'document',
                'returnType',
            ],
        ];
    }
}
