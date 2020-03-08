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

use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

use function defined;
use function header;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Item implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        try {
            $config = [
                'name'   => 'application',
                'static' => true,
            ];
            $body[] = PropertyGenerator::fromArray($config);
        } catch (InvalidArgumentException $e) {
        }
    }
}
