<?php

/**
 * @brief       CompilerAbstract Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Dev\Compiler;

use IPS\Application;
use IPS\toolbox\Shared\Magic;
use IPS\toolbox\Shared\Read;
use IPS\toolbox\Shared\Replace;
use IPS\toolbox\Shared\Write;

use function explode;
use function is_array;
use function str_replace;
use function trim;

use const IPS\ROOT_PATH;


/**
 * @brief      _CompilerAbstract Class
 * @mixin CompilerAbstract
 */
abstract class _CompilerAbstract
{
    use Magic;
    use Read;
    use Replace;
    use Write;

    /**
     * @var Application
     */
    protected $application;

    protected $type;

    /**
     * cause it looks pretty?
     * _CompilerAbstract constructor.
     *
     * @param array $values
     * @param Application $application
     * @param string $type
     */
    public function __construct(array $values, Application $application, string $type)
    {
        $this->type = $type;
        $this->application = $application;
        $this->blanks = ROOT_PATH . '/applications/toolbox/data/defaults/dev/';
        foreach ($values as $key => $val) {
            $key = str_replace('dtdevplus_dev_', '', $key);
            $val = !is_array($val) ? trim($val) : $val;
            if (!empty($val)) {
                $this->{$key} = $val;
            } else {
                $this->{$key} = null;
            }
        }
    }

    /**
     * process the values for file creation
     */
    final public function process()
    {
        $this->app = $this->application->directory;

        if ($this->group_manual) {
            $this->location = $this->group_manual_location;
            $this->group = $this->group_manual_folder;
        } else {
            $locationGroup = $this->_group;
            [$this->location, $this->group] = explode(':', $locationGroup);
        }

        $content = $this->content();
        $file = $this->filename;

        $dir = ROOT_PATH . '/applications/' . $this->app . '/dev/';
        if ($this->type === 'template') {
            $dir .= 'html/';
        } else {
            $dir .= 'js/';
        }

        $dir .= $this->location . '/' . $this->group;

        if ($this->type === 'template') {
            $this->proxy = true;
        }
        $this->_writeFile($file, $content, $dir, false);
    }


    /**
     * sets and gathers the class body blank
     *
     * @return string
     */
    abstract protected function content(): string;
}
