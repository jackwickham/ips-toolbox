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

use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

use function defined;
use function header;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER[ 'SERVER_PROTOCOL' ]) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Output implements HelpersAbstract
{
    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $methodDocBlock = new DocBlockGenerator(
            'Send JSON output', \null, [
            new ParamTag('data', 'array|string'),
            new ParamTag('httpStatusCode', 'int'),
        ]
        );

        try {
            $body[] = MethodGenerator::fromArray(
                [
                    'name'       => 'json',
                    'parameters' => [
                        new ParameterGenerator('data', \null, \null, 0),
                        new ParameterGenerator('httpStatusCode', 'int', 200, 1),

                    ],
                    'body'       => 'return parent::json(... func_get_arguments());',
                    'docblock'   => $methodDocBlock,
                    'static'     => \false,
                ]
            );
        } catch (InvalidArgumentException $e) {
        }
    }
}
