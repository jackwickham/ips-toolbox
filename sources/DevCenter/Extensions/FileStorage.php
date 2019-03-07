<?php

/**
 * @brief       FileStorage Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtdevplus
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Extensions;

use InvalidArgumentException;
use IPS\Request;
use function array_values;
use function defined;
use function header;
use function mb_strpos;


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _FileStorage
 *
 * @package IPS\toolbox\DevCenter\Extensions
 * @mixin \IPS\toolbox\DevCenter\Extensions\ExtensionsAbstract
 */
class _FileStorage extends ExtensionsAbstract
{

    /**
     * @return array
     * @throws \Exception
     */
    public function elements(): array
    {
        /* @var array $tablesDb */
        $tablesDb = \IPS\Db::i()->query( 'SHOW TABLES' );
        $tables = [];
        $tables[ 0 ] = 'Select Table';

        foreach ( $tablesDb as $table ) {
            $app = $this->application->directory . '_';
            $foo = array_values( $table );
            if ( 0 === mb_strpos( $foo[ 0 ], $app ) ) {
                $tables[ $foo[ 0 ] ] = $foo[ 0 ];
            }
        }

        $this->elements[] = [
            'name'       => 'table',
            'class'      => 'Select',
            'ap'         => \true,
            'ops'        => [
                'options' => $tables,
                'parse'   => 'raw',
            ],
            'validation' => function ( $data )
            {

                if ( !$data && !Request::i()->dtdevplus_ext_use_default_checkbox ) {
                    throw new InvalidArgumentException( 'must select table!' );
                }
            },
        ];

        $this->elements[] = [
            'name'       => 'field',
            'class'      => 'select',
            'ap'         => \true,
            'ops'        => [
                'options' => [

                ],
            ],
            'validation' => function ( $data )
            {
                if ( !$data && !Request::i()->dtdevplus_ext_use_default_checkbox ) {
                    throw new InvalidArgumentException( 'must select field!' );
                }
            },
        ];
        return $this->elements;
    }

    /**
     * @inheritdoc
     */
    protected function _content()
    {
        return $this->_getFile( $this->extension );
    }
}
