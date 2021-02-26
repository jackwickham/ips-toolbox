<?php

namespace IPS\toolbox\modules\admin\devcenter;

use IPS\Application;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevCenter\Sources;
use IPS\toolbox\Proxy\Generator\Cache;

use function defined;
use function header;
use function mb_strtoupper;

use function array_shift;
use function explode;
use function implode;
use function ltrim;
use function preg_grep;
use function preg_quote;
use function str_replace;


/* To prevent PHP errors (extending class does not exist) revealing path */

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * sources
 */
class _sources extends Controller
{
    use \IPS\toolbox\Shared\Sources;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Sources
     */
    protected $elements;
    protected $front = false;

    public function execute()
    {
        Dispatcher::i()->checkAcpPermission('sources_manage');
        Sources::menu();
        $app = (string)Request::i()->appKey;
        if (!$app) {
            $app = 'core';
        }
        $this->application = Application::load($app);
        $this->elements = new Sources($this->application);
        parent::execute();
    }

    protected function manage()
    {
    }


}
