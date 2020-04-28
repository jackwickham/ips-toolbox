<?php

namespace IPS\toolbox\extensions\toolbox\phpToolBoxMeta;

use IPS\toolbox\Form;
use IPS\toolbox\Form\Element;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * devcenter
 */
class _devcenter
{

    public function addJsonMeta( &$jsonMeta )
    {

        $jsonMeta[ 'registrar' ][] = [
            'signatures' => [
                [
                    'class'  => Form::class,
                    'method' => 'add',
                    'index'  => 1,
                ],
            ],
            'provider'   => 'FormAddMethod',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'           => 'FormAddMethod',
            'lookup_strings' => array_keys( Element::$helpers ),
        ];
    }
}
