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

use IPS\Http\Request\Curl;
use IPS\toolbox\Generator\Builders\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use function defined;
use function header;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Url implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process( $class, ClassGenerator $classGenerator, &$classExtends )
    {

        $params = [
            [ 'name' => '$timeout', 'value' => null ],
            [ 'name' => 'httpVersion', 'value' => 200 ],
            [ 'name' => 'followRedirects', 'value' => 5 ],
            [ 'name' => 'skipLocalhostRedirects', 'value' => false ],

        ];
        $extra = [
            'document' => [
                '@param int|null $timeout',
                '@param string $httpVersion',
                '@param bool|int $followRedirects',
                '@param bool $skipLocalhostRedirects',
                '@return \\' . Curl::class,
            ],
        ];
        $body = 'return parent::request(... func_get_args());';

        $classGenerator->addMethod( 'request', $body, $params, $extra );

    }
}
