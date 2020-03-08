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

use IPS\Application;

use function defined;
use function header;
use function method_exists;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER[ 'SERVER_PROTOCOL' ]) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Request implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $classDoc[] = ['pt' => 'p', 'prop' => 'app', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'module', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'controller', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'id', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'pid', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'do', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'appKey', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'tab', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'adsess', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'group', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'new', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => '_new', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'path', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'c', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'd', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'application', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'type', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'limit', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'password', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'club', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'page', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'perPage', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'value', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'sortby', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'sortdirection', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'parent', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'filter', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'params', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'input', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'action', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'chunk', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'chunks', 'type' => 'string'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'last', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'enabled', 'type' => 'int'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'gitApp', 'type' => 'string'];

        /* @var Application $app */
        foreach (Application::appsWithExtension('toolbox', 'ProxyHelpers') as $app) {
            $extensions = $app->extensions('toolbox', 'ProxyHelpers', \true);
            /* @var \IPS\toolbox\Proxy\extensions\toolbox\Proxy\ProxyHelpers\ProxyHelpers $extension */
            foreach ($extensions as $extension) {
                if (method_exists($extension, 'request')) {
                    $extension->request($classDoc);
                }
            }
        }
    }
}
