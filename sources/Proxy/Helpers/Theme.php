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
    header((isset($_SERVER[ 'SERVER_PROTOCOL' ]) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Theme implements HelpersAbstract
{
    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $methodDocBlock = new DocBlockGenerator(
            'Send JSON output', \null, [
                new ReturnTag('static')
            ]
        );

        try {
            $body[] = MethodGenerator::fromArray(
                [
                    'name'       => 'i',
                    'parameters' => [],
                    'body'       => 'return parent::i();',
                    'docblock'   => $methodDocBlock,
                    'static'     => \true,
                ]
            );
        } catch (InvalidArgumentException $e) {
        }
    }
}
