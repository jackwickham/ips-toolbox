<?php

/**
 * @brief       Dev Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter;

use Exception;
use InvalidArgumentException;
use IPS\Application;
use IPS\Http\Url;
use IPS\Output;
use IPS\Patterns\Singleton;
use IPS\Request;
use IPS\toolbox\DevCenter\Dev\Compiler\Javascript;
use IPS\toolbox\DevCenter\Dev\Compiler\Template;
use IPS\toolbox\Form;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\ReservedWords;
use IPS\Xml\XMLReader;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function array_pop;
use function defined;
use function explode;
use function header;
use function in_array;
use function is_array;
use function is_file;
use function mb_ucfirst;
use function preg_match;

\IPS\toolbox\Application::loadAutoLoader();

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}


/**
 * @brief      _Dev Class
 * @mixin Dev
 */
class _Dev extends Singleton
{
    /**
     * @inheritdoc
     */
    protected static $instance;
    /**
     * @var Form
     */
    public $form;
    /**
     * The current application object
     *
     * @var Application
     */
    protected $application;
    /**
     * application directory
     *
     * @var null|string
     */
    protected $app;
    protected $elements;

    /**
     * _Dev constructor.
     *
     * @param Application|null $application
     */
    public function __construct(Application $application = null)
    {
        if ($application instanceof Application) {
            $this->application = $application;
            $this->app = $this->application->directory;
        }
        $this->form = Form::create()->formPrefix('dtdevplus_dev_');
    }

    /**
     * @param array $config
     * @param string $type
     */
    public function buildForm(array $config, string $type)
    {
        $this->type = $type;

        foreach ($config as $func) {
            $method = 'el' . mb_ucfirst($func);
            $this->{$method}();
        }
    }

    /**
     * create file
     */
    public function create()
    {
        if ($values = $this->form->values()) {
            if ($this->type === 'template') {
                $class = Template::class;
            } else {
                $class = Javascript::class;
            }
            /**
             * @var \IPS\toolbox\Dev\Compiler\CompilerAbstract $class ;
             */
            $class = new $class($values, $this->application, $this->type);
            $class->process();
            $url = Url::internal('&app=core&module=applications&controller=developer')->setQueryString(
                ['appKey' => $this->app]
            )->csrf();
            Output::i()->redirect($url, 'File Created');
        }
    }

    /**
     * @param $data
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function validateFilename($data)
    {
        $location = null;
        $group = null;
        if (!Request::i()->dtdevplus_dev_group_manual && !isset(
                Request::i()->dtdevplus_dev_group_manual_checkbox
            )) {
            $locationGroup = Request::i()->dtdevplus_dev__group;
            [$location, $group] = explode(':', $locationGroup);
        } else {
            $location = Request::i()->dtdevplus_dev_group_manual_location;
            $group = Request::i()->dtdevplus_dev_group_manual_folder;
        }
        $dir = \IPS\ROOT_PATH . '/applications/' . $this->app . '/dev/';
        if ($this->type === 'template') {
            $dir .= 'html/';
            $file = $dir . '/' . $data;
        } else {
            $dir .= 'js/';
            $file = '';
            if ($this->type === 'widget') {
                $file = 'ips.ui.' . $this->app . '.' . $data;
            } elseif ($this->type === 'controller') {
                $file = 'ips.' . $this->app . '.' . $location . '.' . $group . '.' . $data;
            } elseif ($this->type === 'module') {
                $file = 'ips.' . $this->app . '.' . $data;
            } elseif ($this->type === 'jstemplate') {
                $file = 'ips.templates.' . $data;
            } elseif ($this->type === 'jsmixin') {
                $file = 'ips.' . $this->app . '.' . $data;
            }


            if ($this->type === 'jstemplate') {
                $type = 'templates';
            } elseif ($this->type === 'jsmixin') {
                $type = 'mixin';
            } else {
                $type = 'controllers';
            }
            $dir .= $location . '/' . $type . '/' . $group;
            $file = $dir . '/' . $file;
        }

        if ($this->type === 'template') {
            $file .= '.phtml';
        } else {
            $file .= '.js';
        }

        if (is_file($file)) {
            throw new InvalidArgumentException('The file exist already!');
        }

        if ($this->type === 'template' && ReservedWords::check($data)) {
            throw new InvalidArgumentException('dtdevplus_class_reserved');
        }

        if (!$data) {
            throw new InvalidArgumentException('dtdevplus_class_no_blank');
        }
    }

    protected function elName()
    {
        $this->form->add('filename')->required()->validation([$this, 'validateFilename']);
    }

    protected function eltemplateName()
    {
        $this->form->add('templateName', 'stack')->required();
    }

    protected function elArguments()
    {
        $this->elements[] = [
            'name' => 'arguments',
            'class' => 'stack',
        ];
        $this->form->add('arguments', 'stack');
    }

    protected function elWidgetName()
    {
        $this->form->add('widgetname')->prefix($this->app);
    }

    protected function elMixin()
    {
        $controllers = [];
        foreach (Application::applications() as $app) {
            $file = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/javascript.xml';
            if (is_file($file)) {
                $xml = new XMLReader();
                $xml->open($file);
                $xml->read();
                while ($xml->read()) {
                    if ($xml->nodeType !== XMLReader::ELEMENT) {
                        continue;
                    }

                    if ($xml->name === 'file') {
                        if ($xml->getAttribute('javascript_type') === 'controller') {
                            $content = $xml->readString();
                            preg_match("#ips.controller.register\('(.*?)'#", $content, $match);
                            if (isset($match[1]) && $match[1]) {
                                $controllers[$app->directory][$match[1]] = $match[1];
                            }
                        }
                    }
                }
            }
        }
        $this->ksortRecursive($controllers);

        $this->form->add('mixin', 'select')->options(['options' => $controllers]);
    }

    protected function ksortRecursive(&$array, $sort_flags = SORT_REGULAR)
    {
        if (!is_array($array)) {
            return false;
        }
        ksort($array, $sort_flags);
        foreach ($array as &$arr) {
            $this->ksortRecursive($arr, $sort_flags);
        }

        return true;
    }

    protected function elGroup()
    {
        $groupManual = true;

        if ($this->type === 'template') {
            try {
                $this->_getGroups();
                $groupManual = false;
            } catch (InvalidArgumentException $e) {
                Debug::log($e);
            }
        }

        if (in_array($this->type, ['controller', 'module', 'widget'])) {
            try {
                $this->_getGroups('js');
                $groupManual = false;
            } catch (Exception $e) {
            }
        }

        if ($this->type === 'jstemplate') {
            try {
                $this->_getGroups('js', 'templates');
                $groupManual = false;
            } catch (Exception $e) {
            }
        }
        $this->form->add('group_manual', 'yn')->value($groupManual)->toggles(
            [
                'group_manual_location',
                'group_manual_folder',
            ]
        )->toggles(['_group'], true);

        $this->form->add('group_manual_location', 'select')->options(
            ['options' => ['admin' => 'admin', 'front' => 'front', 'global' => 'global']]
        );
        $this->form->add('group_manual_folder')->required();
    }

    /**
     * @param string $path
     *
     * @param string $altPath
     *
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function _getGroups($path = 'html', $altPath = 'controllers')
    {
        $options = [];

        try {
            $base = \IPS\ROOT_PATH . \DIRECTORY_SEPARATOR . 'applications' . \DIRECTORY_SEPARATOR . $this->app . \DIRECTORY_SEPARATOR . 'dev' . \DIRECTORY_SEPARATOR . $path . \DIRECTORY_SEPARATOR;

            /* @var Finder $groups */
            $groups = new Finder();
            $fs = new Filesystem();
            $extended = '';
            if ($path === 'js') {
                $extended = \DIRECTORY_SEPARATOR . $altPath;
            }
            if ($fs->exists($base . 'admin' . $extended)) {
                $groups->in($base . 'admin' . $extended);
            }

            if ($fs->exists($base . 'front' . $extended)) {
                $groups->in($base . 'front' . $extended);
            }

            if ($fs->exists($base . 'global' . $extended)) {
                $groups->in($base . 'global' . $extended);
            }
            $groups->directories();
            foreach ($groups as $group) {
                $paths = $group->getRealPath();
                $paths = explode(\DIRECTORY_SEPARATOR, $paths);
                array_pop($paths);
                $location = array_pop($paths);
                if ($path === 'js') {
                    $location = array_pop($paths);
                }
                if (in_array($location, ['front', 'global', 'admin'], true)) {
                    $name = $location . ':' . $group->getFilename();
                    $options[$name] = $name;
                }
            }
        } catch (LogicException $es) {
        }

        if (empty($options) !== false) {
            throw new InvalidArgumentException('meh');
        }

        $this->elements[] = [
            'class' => 'select',
            'name' => '_group',
            'ops' => [
                'options' => $options,
            ],
        ];
        $this->form->add('_group', 'select')->options(['options' => $options]);
    }

    protected function elOptions()
    {
        $this->form->add('options', 'stack');
    }
}
