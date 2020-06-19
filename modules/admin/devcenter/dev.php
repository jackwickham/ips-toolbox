<?php


namespace IPS\toolbox\modules\admin\devcenter;

use IPS\Application;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevCenter\Dev;
use IPS\toolbox\DevCenter\Sources;

use function defined;
use function header;
use function mb_strtoupper;

/* To prevent PHP errors (extending class does not exist) revealing path */

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * sources
 */
class _dev extends Controller
{

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Elements
     */
    protected $elements;

    public function execute()
    {
        Dispatcher::i()->checkAcpPermission('sources_manage');
        Sources::menu();
        $this->application = Application::load(Request::i()->appKey);
        $this->elements = new Dev($this->application);
        parent::execute();
    }

    protected function manage()
    {
    }

    protected function template()
    {
        $config = [
            'group',
            'name',
            'arguments',
        ];

        $this->doOutput($config, 'template', 'Template');
    }

    protected function doOutput($config, $type, $title)
    {
        $this->elements->buildForm($config, $type);
        $this->elements->create();
        $url = (string)Url::internal(
            'app=core&module=applications&controller=developer&appKey=' . Request::i()->appKey
        )->csrf();
        Output::i()->breadcrumb[] = [$url, 'Developer Ceneter'];
        Output::i()->breadcrumb[] = [$url, $this->application->directory];
        Output::i()->breadcrumb[] = [null, $title];
        Output::i()->title = mb_strtoupper($this->application->directory) . ': ' . $title;
        Output::i()->output = $this->elements->form;
    }

    protected function controller()
    {
        $config = [
            'name',
            'group',
        ];

        $this->doOutput($config, 'controller', 'Controller');
    }

    protected function module()
    {
        $config = [
            'name',
            'group',
        ];

        $this->doOutput($config, 'module', 'Module');
    }

    protected function widget()
    {
        $config = [
            'name',
            'group',
            'WidgetName',
            'Options'
        ];

        $this->doOutput($config, 'widget', 'Widget');
    }

    protected function jstemplate()
    {
        $config = [
            'name',
            'group',
            'templateName',
        ];

        $this->doOutput($config, 'jstemplate', 'jstemplate');
    }

    protected function jsmixin()
    {
        $config = [
            'name',
            'group',
            'mixin',
        ];

        $this->doOutput($config, 'jsmixin', 'jsmixin');
    }
}
