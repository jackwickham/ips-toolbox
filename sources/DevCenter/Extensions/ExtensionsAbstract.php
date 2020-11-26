<?php

/**
 * @brief       ExtensionsAbstract Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtdevplus
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Extensions;

use IPS\Application;
use IPS\Db;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\toolbox\Form;
use IPS\toolbox\Shared\Magic;
use IPS\toolbox\Shared\Read;
use IPS\toolbox\Shared\Replace;
use IPS\toolbox\Shared\Write;

use function count;
use function date;
use function defined;
use function header;
use function is_array;
use function str_replace;

use function array_pop;
use function array_values;
use function explode;
use function file_exists;
use function mb_strlen;
use function mb_substr;
use function uniqid;


if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Class _ExtensionsAbstract
 *
 * @package IPS\toolbox\DevCenter\Extensions
 * @mixin \IPS\toolbox\DevCenter\Extensions\ExtensionsAbstract
 */
abstract class _ExtensionsAbstract
{

    use Magic;
    use Read;
    use Replace;
    use Write;

    /**
     * @var Application|null
     */
    protected $extApp;

    /**
     * @var Application|null
     */
    protected $application;

    /**
     * extension type
     *
     * @var null
     */
    protected $extension;

    /**
     * elements store
     *
     * @var array
     */
    protected $elements = [];

    /**
     * @var Form
     */
    protected $form;

    protected $dir;

    /**
     * _ExtensionsAbstract constructor.
     *
     * @param Application $extApp
     * @param Application $application
     * @param             $extension
     */
    public function __construct(Application $extApp, Application $application, $extension)
    {
        $this->extApp = $extApp;
        $this->application = $application;
        $this->extension = $extension;
        $this->dir = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/extensions/' . $this->extApp->directory . '/' . $this->extension . '/';
        $this->blanks = \IPS\ROOT_PATH . '/applications/toolbox/data/defaults/modExtensions/';
        $this->form = Form::create()->attributes(['data-controller' => 'ips.admin.dtdevplus.query'])->formPrefix(
            'dtdevplus_ext_'
        );
        $this->form->header('title_' . $extension);
        $this->form->add('class')->value($this->getName());
        $this->form->add('use_default', 'yn');
    }

    protected function getName()
    {
        $class = explode('\\', static::class);
        $name = array_pop($class);
        $path = $this->dir . $name . '.php';
        if (!file_exists($path)) {
            return $name;
        }

        return $name . mb_substr(uniqid($name, true), mb_strlen($name) + 1, 5);
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws ExtensionException
     */
    public function form()
    {
        $this->elements();
        if ($values = $this->form->values()) {
            $this->_process($values);
            Output::i()->redirect(
                Url::internal(
                    "app=core&module=applications&controller=developer&appKey={$this->application->directory}&tab=extensions"
                )->csrf(),
                'file_created'
            );
        }

        return $this->form;
    }

    /**
     * elements array for dtbase\forms class
     *
     * @return array
     */
    abstract public function elements();

    /**
     * @param array $values
     *
     * @throws ExtensionException
     * @throws \Exception
     */
    protected function _process(array $values)
    {
        if (!empty($values['dtdevplus_ext_use_default'])) {
            throw new ExtensionException('default');
        }
        $dir = $this->dir;
        foreach ($values as $key => $val) {
            $key = str_replace('dtdevplus_ext_', '', $key);
            $this->{$key} = $val;
        }

        $content = $this->_content();
        $file = $this->class . '.php';
        $find = [
            '{subpackage}',
            '{date}',
            '{app}',
            '{class}',
        ];

        $replace = [
            ($this->application->directory !== 'core') ? (" * @subpackage\t" . Member::loggedIn()->language()->get(
                    "__app_{$this->application->directory}"
                )) : '',
            date('d M Y'),
            $this->application->directory,
            $this->class,
        ];

        if (is_array($this->data) && count($this->data)) {
            foreach ($this->data as $key => $val) {
                $find[] = '{' . $key . '}';
                $replace[] = $val;
            }
        }

        $this->content = $this->_replace($find, $replace, $content);
        $this->_writeFile($file, $this->content, $dir);
        \IPS\Application::writeJson( \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/data/extensions.json', $this->application->buildExtensionsJson() );
  
    }

    /**
     * gets the file content and modify anything thing that might need to be replaced
     *
     * @return mixed
     */
    abstract protected function _content();

    public function getFields($table)
    {
        $fields = Db::i()->query("SHOW COLUMNS FROM " . Db::i()->real_escape_string(Db::i()->prefix . $table));
        $f = [];
        foreach ($fields as $field) {
            $f[array_values($field)[0]] = array_values($field)[0];
        }
        return $f;
    }
}
