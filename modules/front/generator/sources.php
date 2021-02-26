<?php


namespace IPS\toolbox\modules\front\generator;

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Application;
use IPS\Dispatcher;
use IPS\Request;
use IPS\Theme;
use IPS\toolbox\DevCenter\Sources;
use IPS\Output;

if ( !\defined('\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * sources
 */
class _sources extends \IPS\Dispatcher\Controller
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

    protected $front = true;

    public function execute()
    {
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
        Output::i()->output = Theme::i()->getTemplate('generator', 'toolbox','front')->sources($this->application->directory);
    }
}
