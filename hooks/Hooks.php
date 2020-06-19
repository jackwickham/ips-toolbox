//<?php namespace toolbox_IPS_Plugin_Hook_ab9712a0d65901062b22f5262a724bd72;

use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;
use IPS\Application;
use DomainException; 
use IPS\Helpers\Form;
use IPS\Helpers\Form\Text;

use Generator\Builders\ClassGenerator;
use IPS\toolbox\Proxy\Generator\Proxy;

use IPS\toolbox\Proxy\Proxyclass;

use const IPS\ROOT_PATH;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    class _HOOK_CLASS_ extends \IPS\Plugin\Hook{}
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_Hooks extends _HOOK_CLASS_
{

    /**
     * @param Url $url
     * @param $appOrPluginId
     * @param $hookDir
     * @return Form|\IPS\Helpers\Table\Db
     * @throws \ReflectionException
     */
    public static function devTable($url, $appOrPluginId, $hookDir)
    {
        \IPS\toolbox\Application::loadAutoLoader();
        $dtProxyFolder = ROOT_PATH . '/dtProxy/namespace.json';

        $parent = parent::devTable($url, $appOrPluginId, $hookDir);

        /** @var Form $parent */
        if ($parent instanceof Form && file_exists($dtProxyFolder)) {
            $elements = $parent->elements;

            $options = [
                'placeholder'  => 'Namespace',
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass&appKey=' . Request::i(
                        )->appKey,
                    'minimized'            => false,
                    'commaTrigger'         => false,
                    'unique'               => true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ];

            unset($elements[ 'plugin_hook_class' ]);
            $parent->elements = $elements;

            $parent->add(
                new Text(
                    'plugin_hook_class', null, true, $options, static function ($val) {
                    if ($val && !class_exists('IPS\\' . $val)) {
                        throw new DomainException('plugin_hook_class_err');
                    }
                }, 'IPS\\', null, 'plugin_hook_class'
                )
            );
        }

        return $parent;
    }
}
