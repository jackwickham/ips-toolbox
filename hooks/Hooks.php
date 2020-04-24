//<?php namespace ad4ce72351d07d412fe530d3249c333ef;

use IPS\Output;
use IPS\Request;
use IPS\Application;
use DomainException; 
use IPS\Helpers\Form;
use IPS\Helpers\Form\Text;

use Generator\Builders\ClassGenerator;
use IPS\toolbox\Proxy\Generator\Proxy;

use const IPS\ROOT_PATH;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    class _HOOK_CLASS_ extends \IPS\Plugin\Hook{}
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_Hooks extends _HOOK_CLASS_
{

    public static function devTable($url, $appOrPluginId, $hookDir)
    {
        \IPS\toolbox\Application::loadAutoLoader();
        $hookTable = Request::i()->hookTable;
        $dtProxyFolder = ROOT_PATH . '/dtProxy/namespace.json';
        if (file_exists($dtProxyFolder) && $hookTable === 'add' && !\is_int($appOrPluginId) && Request::i(
            )->plugin_hook_type === 'C' && Request::i()->plugin_hook_class !== null) {
            $class = Request::i()->plugin_hook_class;
            if ($class !== null && class_exists('IPS\\' . $class)) {
                $app = Application::load($appOrPluginId);
                $hook = new static;
                $hook->app = $appOrPluginId;
                $hook->type = Request::i()->plugin_hook_type;
                $hook->class = ('\IPS\\' . $class);
                $hook->filename = Request::i()->plugin_hook_location ?: md5(mt_rand());
                $hook->save();
                $reflection = new \ReflectionClass($hook->class);
                $classname = "{$appOrPluginId}_hook_{$hook->filename}";
                $hookClass = new ClassGenerator();
                $hookClass->isHook = true;
                // $hookClass->hookClass = $hook->class;
                // $hookClass->hookNamespace = 'a'.md5(time());
                $hookClass->addHeaderCatch();
                $classDoc[] = 'Hook For ' . $hook->class;
                $classDoc[] = '@mixin ' . $hook->class;
                $hookClass->addDocumentComment($classDoc, true);
                $hookClass->addClassName($classname);
                $hookClass->addFileName($hook->filename);
                $hookClass->addPath($hookDir);
                if ($reflection->isAbstract() === true) {
                    $hookClass->isAbstract();
                }

                $hookClass->save();
                static::writeDataFile();
                $app->skip = true;
                $app->buildHooks();
                
                Proxy::i()->buildAppHooks($app );
                Output::i()->redirect($url);
            }
        }
        $parent = parent::devTable($url, $appOrPluginId, $hookDir);

        /** @var Form $parent */
        if (file_exists($dtProxyFolder) && $hookTable === 'add') {
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
                    'plugin_hook_class', null, true, $options, function ($val) {
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
