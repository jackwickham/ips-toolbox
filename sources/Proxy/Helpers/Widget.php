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

class _Widget implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $methodDocBlock = new DocBlockGenerator(
            '@inheritdoc', null, [
            new ParamTag('callback', 'array'),
            new ReturnTag(['dataType' => 'string']),

        ]
        );

        try {
            $body[] = MethodGenerator::fromArray(
                [
                    'name'       => 'template',
                    'parameters' => [
                        new ParameterGenerator('callback', null, null, 0),
                    ],
                    'body'       => 'return parent::template(... func_get_arguments());',
                    'docblock'   => $methodDocBlock,
                    'static'     => \false,
                ]
            );
        } catch (InvalidArgumentException $e) {
        }
    }
}
