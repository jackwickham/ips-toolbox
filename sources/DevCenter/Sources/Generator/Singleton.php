<?php

/**
 * @brief       Singleton Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Sources\Generator;

use IPS\Patterns\Singleton;
use Zend\Code\Generator\PropertyValueGenerator;
use function defined;
use function header;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Singleton extends GeneratorAbstract
{
    /**
     * @inheritdoc
     * @throws \Zend\Code\Exception\InvalidArgumentException
     * @throws \Exception
     */
    protected function bodyGenerator()
    {
        $this->brief = 'Singleton';
        $this->extends = Singleton::class;

        if ( $this->useImports ) {
            $this->generator->addUse( Singleton::class );
        }

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Singleton Instances' ],
                [ 'name' => 'note', 'description' => 'This needs to be declared in any child class.' ],
                [ 'name' => 'var', 'description' => 'static' ],
            ],
        ];

        $config = [
            'name'   => 'instance',
            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }
}
