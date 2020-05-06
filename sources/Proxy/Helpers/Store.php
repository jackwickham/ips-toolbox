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

use IPS\Application;

use function defined;
use function header;
use function method_exists;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class _Store implements HelpersAbstract
{
    /**
     * @inheritdoc
     */
    public function process($class, &$classDoc, &$classExtends, &$body)
    {
        $classDoc[] = ['pt' => 'p', 'prop' => 'acpBulletin', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'administrators', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'applications', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'bannedIpAddresses', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'cms_databases', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'cms_fieldids', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'emoticons', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'furl_configuration', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'groups', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'languages', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'maxAllowedPacket', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'moderators', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'modules', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'nexusPackagesWithReviews', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'profileSteps', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'rssFeeds', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'settings', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'storageConfigurations', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'themes', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'dt_cascade_proxy', 'type' => 'array'];
        /* @var Application $app */
        foreach (Application::appsWithExtension('toolbox', 'ProxyHelpers') as $app) {
            $extensions = $app->extensions('toolbox', 'ProxyHelpers', \true);
            foreach ($extensions as $extension) {
                if (method_exists($extension, 'store')) {
                    $extension->store($classDoc);
                }
            }
        }
    }
}
