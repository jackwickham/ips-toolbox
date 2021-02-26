//<?php namespace toolbox_IPS_Application_a9c79968882bc47948bd3964ea259cdf0;

use IPS\Settings;
use IPS\toolbox\DevCenter\Headerdoc;
use IPS\toolbox\DevFolder\Applications;
use Exception;
use IPS\toolbox\Proxy\Generator\Proxy;
use IPS\toolbox\Proxy\Proxyclass;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}


/**
 * Class toolbox_hook_Application
 * @mixin \IPS\Application
 */
class toolbox_hook_Application extends _HOOK_CLASS_
{
    public $skip = false;


    /**
     * @inheritdoc
     */
    public function assignNewVersion($long, $human)
    {
        parent::assignNewVersion($long, $human);
        if (static::appIsEnabled('toolbox')) {
            $this->version = $human;
            Headerdoc::i()->process($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        if (static::appIsEnabled('toolbox')) {
            Headerdoc::i()->addIndexHtml($this);
        }
        parent::build();
    }

    /**
     * @inheritdoc
     */
    public function installOther()
    {
        if (\IPS\IN_DEV && $this->marketplace_id === null) {
            $dir = \IPS\ROOT_PATH . '/applications/' . $this->directory . '/dev/';
            if (!\file_exists($dir)) {
                try {
                    $app = new Applications($this);
                    $app->addToStack = \true;
                    $app->email();
                    $app->javascript();
                    $app->language();
                    $app->templates();
                } catch (Exception $e) {
                }
            }
        }

        parent::installOther();
    }

    public function buildHooks()
    {

        parent::buildHooks();
        Proxyclass::i()->buildHooks();
    }

    public static function writeJson($file, $data)
    {
        parent::writeJson($file, $data);
        if (mb_strpos($file,'settings.json') !== false) {
            Settings::i()->clearCache();
            Proxy::i()->generateSettings();
        }
    }
}
