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
use IPS\Patterns\Singleton;
use IPS\Request;
use IPS\toolbox\DevCenter\Dev\Compiler\Javascript;
use IPS\toolbox\DevCenter\Dev\Compiler\Template;
use IPS\toolbox\ReservedWords;
use IPS\Xml\XMLReader;
use LogicException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use function array_pop;
use function count;
use function defined;
use function explode;
use function header;
use function in_array;
use function is_array;
use function is_file;
use function mb_ucfirst;
use function preg_match;

\IPS\toolbox\Application::loadAutoLoader();

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}


/**
 * @brief      _Dev Class
 * @mixin \IPS\toolbox\DevCenter\Dev
 */
class _Dev extends Singleton
{
    /**
     * @inheritdoc
     */
    protected static $instance;

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
    public function __construct( Application $application = null )
    {
        if( $application instanceof Application ) {
            $this->application = $application;
            $this->app = $this->application->directory;
        }
    }

    /**
     * @param array  $config
     * @param string $type
     */
    public function buildForm( array $config, string $type )
    {
        $this->type = $type;

        foreach ( $config as $func ) {
            $method = 'el' . mb_ucfirst( $func );
            $this->{$method}();
        }

        $this->form = Form::buildForm( $this->elements, 'dtdevplus_dev_' );
    }

    /**
     * create file
     */
    public function create()
    {
        if ( $values = $this->form->values() ) {
            /**
             * @var \IPS\tooblox\Dev\Compiler\CompilerAbstract $class ;
             */
            $type = $this->type;
            $values[ 'type' ] = $type;
            if ( $type === 'template' ) {
                $class = Template::class;
            }
            else {
                $class = Javascript::class;
            }

            $class = new $class( $values, $this->application );
            $class->process();
        }
    }

    /**
     * @param $data
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function validateFilename( $data )
    {

        $location = \null;
        $group = \null;
        if ( Request::i()->dtdevplus_dev_group_manual_checkbox === 0 ) {
            $locationGroup = Request::i()->dtdevplus_dev__group;
            [ $location, $group ] = explode( ':', $locationGroup );
        }
        else {
            $location = Request::i()->dtdevplus_dev_group_manual_location;
            $group = Request::i()->dtdevplus_dev_group_manual_folder;
        }
        $dir = \IPS\ROOT_PATH . '/applications/' . $this->app . '/dev/';
        if ( $this->type === 'template' ) {
            $dir .= 'html/';
        }
        else {
            $dir .= 'js/';
        }

        $dir .= $location . '/' . $group;

        $file = $dir . '/' . $data;

        if ( $this->type === 'template' ) {
            $file .= '.phtml';
        }
        else {
            $file .= '.js';
        }

        if ( is_file( $file ) ) {
            throw new InvalidArgumentException( 'The file exist already!' );
        }

        if ( $this->type === 'template' && ReservedWords::check( $data ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_reserved' );
        }

        if ( !$data ) {
            throw new InvalidArgumentException( 'dtdevplus_class_no_blank' );
        }
    }

    protected function elName()
    {
        $this->elements[] = [
            'name'       => 'filename',
            'required'   => \true,
            'validation' => [ $this, 'validateFilename' ],
        ];
    }

    protected function eltemplateName()
    {
        $this->elements[] = [
            'name'     => 'templateName',
            'required' => \true,
            'class'    => 'stack',
        ];
    }

    protected function elArguments()
    {
        $this->elements[] = [
            'name'  => 'arguments',
            'class' => 'stack',
        ];
    }

    protected function elWidgetName()
    {
        $this->elements[] = [
            'name' => 'widgetname',
        ];
    }

    protected function elMixin()
    {
        $controllers = [];
        foreach ( Application::applications() as $app ) {
            $file = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/javascript.xml';
            if ( is_file( $file ) ) {
                $xml = new XMLReader;
                $xml->open( $file );
                $xml->read();
                while ( $xml->read() ) {
                    if ( $xml->nodeType !== XMLReader::ELEMENT ) {
                        continue;
                    }

                    if ( $xml->name === 'file' ) {
                        if ( $xml->getAttribute( 'javascript_type' ) === 'controller' ) {
                            $content = $xml->readString();
                            preg_match( "#ips.controller.register\('(.*?)'#", $content, $match );
                            if ( isset( $match[ 1 ] ) && $match[ 1 ] ) {
                                $controllers[ $app->directory ][ $match[ 1 ] ] = $match[ 1 ];
                            }
                        }
                    }
                }
            }
        }
        $this->ksortRecursive( $controllers );
        $this->elements[] = [
            'name'    => 'mixin',
            'class'   => 'select',
            'options' => [
                'options' => $controllers,
            ],
        ];
    }

    protected function ksortRecursive( &$array, $sort_flags = SORT_REGULAR )
    {
        if ( !is_array( $array ) ) {
            return false;
        }
        ksort( $array, $sort_flags );
        foreach ( $array as &$arr ) {
            $this->ksortRecursive( $arr, $sort_flags );
        }
        return true;
    }

    protected function elGroup()
    {
        $groupManual = \true;

        if ( $this->type === 'template' ) {
            try {
                $this->_getGroups();
                $groupManual = \false;
            } catch ( Exception $e ) {
            }
        }

        if ( in_array( $this->type, [ 'controller', 'module', 'widget' ] ) ) {
            try {
                $this->_getGroups( 'js' );
                $groupManual = \false;
            } catch ( Exception $e ) {
            }
        }

        if ( $this->type === 'jstemplate' ) {
            try {
                $this->_getGroups( 'js', 'templates' );
                $groupManual = \false;
            } catch ( \Exception $e ) {
            }
        }

        $this->elements[] = [
            'name'    => 'group_manual',
            'class'   => 'yn',
            'default' => $groupManual,
            'ops'     => [
                'togglesOn' => [
                    'group_manual_location',
                    'group_manual_folder',
                ],
            ],
        ];

        $this->elements[] = [
            'name'  => 'group_manual_location',
            'class' => 'select',
            'ops'   => [
                'options' => [
                    'admin'  => 'Admin',
                    'front'  => 'Front',
                    'global' => 'Global',
                ],
            ],
        ];

        $this->elements[] = [
            'name'     => 'group_manual_folder',
            'required' => \true,
        ];
    }

    /**
     * @param string $path
     *
     * @param string $altPath
     *
     * @throws InvalidArgumentException
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function _getGroups( $path = 'html', $altPath = 'controllers' )
    {
        $options = [];

        try {
            $base = \IPS\ROOT_PATH . \DIRECTORY_SEPARATOR . 'applications' . \DIRECTORY_SEPARATOR . $this->app . \DIRECTORY_SEPARATOR . 'dev' . \DIRECTORY_SEPARATOR . $path . \DIRECTORY_SEPARATOR;

            /* @var Finder $groups */
            $groups = new Finder;
            $fs = new Filesystem;
            $extended = '';
            if ( $path === 'js' ) {
                $extended = \DIRECTORY_SEPARATOR . $altPath;
            }
            if ( $fs->exists( $base . 'admin' . $extended ) ) {
                $groups->in( $base . 'admin' . $extended );
            }

            if ( $fs->exists( $base . 'front' . $extended ) ) {
                $groups->in( $base . 'front' . $extended );
            }

            if ( $fs->exists( $base . 'global' . $extended ) ) {
                $groups->in( $base . 'global' . $extended );
            }
            $groups->directories();
            foreach ( $groups as $group ) {
                $paths = $group->getRealPath();
                $paths = explode( \DIRECTORY_SEPARATOR, $paths );
                array_pop( $paths );
                $location = array_pop( $paths );
                if ( $path === 'js' ) {
                    $location = array_pop( $paths );
                }
                if ( \in_array( $location, [ 'front', 'global', 'admin' ], \true ) ) {
                    $name = $location . ':' . $group->getFilename();
                    $options[ $name ] = $name;
                }
            }
        } catch ( LogicException $es ) {
        }

        if ( is_array( $options ) && count( $options ) ) {
            $this->elements[] = [
                'class' => 'select',
                'name'  => '_group',
                'ops'   => [
                    'options' => $options,
                ],
            ];
        }
        else {
            throw new InvalidArgumentException;
        }
    }
}
