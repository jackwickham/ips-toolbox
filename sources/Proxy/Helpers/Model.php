<?php

/**
 * @brief       IPSRequest Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox\Proxy
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Proxy\Helpers;

use function defined;
use function header;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER[ 'SERVER_PROTOCOL' ]) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Model implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $classDoc[] = ['pt' => 'p', 'prop' => '_id', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => '_title', 'type' => 'string'];
    }
}
