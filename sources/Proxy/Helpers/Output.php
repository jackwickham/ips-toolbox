<?php

/**
 * @brief       IPSDataStore Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox\Proxy
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Proxy\Helpers;

use Generator\Builders\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use function defined;
use function header;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Output implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process( $class, ClassGenerator $classGenerator, &$classExtends )
    {

        $params = [
            [ 'name' => 'data' ],
            [ 'name' => 'httpStatusCode', 'value' => 200 ],
        ];
        $extra = [
            'document' => [
                '@param array|string $data',
                '@param int $httpStatusCode',
            ],
        ];
        $body = 'parent::json(... func_get_args());';
        $classGenerator->addMethod( 'json', $body, $params, $extra );
    }
}
