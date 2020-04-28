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
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

use function defined;
use function header;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Url implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $methodDocBlock = new DocBlockGenerator(
            '@inheritdoc', \null, [
            new ParamTag('timeout', null),
            new ParamTag('httpVersion', null),
            new ParamTag('followRedirects', null),
            new ParamTag('skipLocalhostRedirects', null),
            new ReturnTag(['dataType' => '\\' . Curl::class]),
        ]
        );

        try {
            $body[] = MethodGenerator::fromArray(
                [
                    'name'       => 'request',
                    'parameters' => [
                        new ParameterGenerator('timeout', \null, 'null', 0),
                        new ParameterGenerator('httpVersion', \null, 'null', 1),
                        new ParameterGenerator('followRedirects', \null, 'null', 2),
                        new ParameterGenerator('skipLocalhostRedirects', \null, 'null', 4),
                    ],
                    'body'       => 'return parent::request(... func_get_arguments());',
                    'docblock'   => $methodDocBlock,
                    'static'     => \false,
                ]
            );
        } catch (InvalidArgumentException $e) {
        }
    }
}
