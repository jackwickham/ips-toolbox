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
use IPS\Http\Url;
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

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
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

    use Read, Write, Replace, Magic;

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

    /**
     * _ExtensionsAbstract constructor.
     *
     * @param Application $extApp
     * @param Application $application
     * @param             $extension
     */
    public function __construct( Application $extApp, Application $application, $extension )
    {

        $this->extApp = $extApp;
        $this->application = $application;
        $this->extension = $extension;
        $this->blanks = \IPS\ROOT_PATH . '/applications/dtdevplus/data/defaults/modExtensions/';
        $this->form = Form::create()->attributes( [ 'data-controller' => 'ips.admin.dtdevplus.query' ] );

        $this->elements = [
            'prefix' => 'dtdevplus_ext_',
            [
                'name'   => 'class',
                'header' => 'title_' . $extension,
            ],
            [
                'name'  => 'use_default',
                'class' => 'yn',
            ],
        ];
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws ExtensionException
     */
    public function form()
    {

        $this->elements();
        if ( $values = $this->form->values() ) {
            $this->_process( $values );
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
    protected function _process( array $values )
    {

        if ( !empty( $values[ 'dtdevplus_ext_use_default' ] ) ) {
            throw new ExtensionException();
        }

        foreach ( $values as $key => $val ) {
            $key = str_replace( 'dtdevplus_ext_', '', $key );
            $this->{$key} = $val;
        }

        $content = $this->_content();
        $dir = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/extensions/' . $this->extApp->directory . '/' . $this->extension . '/';
        $file = $this->class . '.php';
        $find = [
            '{subpackage}',
            '{date}',
            '{app}',
            '{class}',
        ];

        $replace = [
            ( $this->application->directory !== 'core' ) ? ( " * @subpackage\t" . \IPS\Member::loggedIn()->language()->get( "__app_{$this->application->directory}" ) ) : '',
            date( 'd M Y' ),
            $this->application->directory,
            $this->class,
        ];

        if ( is_array( $this->data ) && count( $this->data ) ) {
            foreach ( $this->data as $key => $val ) {
                $find[] = '{' . $key . '}';
                $replace[] = $val;
            }
        }

        $this->content = $this->_replace( $find, $replace, $content );
        $this->_writeFile( $file, $this->content, $dir );
        Output::i()->redirect( Url::internal( "app=core&module=applications&controller=developer&appKey={$this->application->directory}&tab=extensions" ), 'file_created' );
    }

    /**
     * gets the file content and modify anything thing that might need to be replaced
     *
     * @return mixed
     */
    abstract protected function _content();
}
