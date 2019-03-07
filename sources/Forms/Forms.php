<?php

/**
 * @brief       Forms Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox;

use InvalidArgumentException;
use IPS\Helpers\Form;
use IPS\Helpers\Form\FormAbstract;
use IPS\Helpers\Form\Matrix;
use IPS\Helpers\Form\Text;
use IPS\Member;
use IPS\toolbox\Profiler\Debug;
use function array_key_exists;
use function class_exists;
use function count;
use function defined;
use function header;
use function in_array;
use function is_array;
use function is_object;
use function json_encode;
use function md5;
use function method_exists;
use function random_int;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Forms
{
    /**
     * @var Form
     */
    protected static $instance = [];

    /**
     * the class map for form elements
     *
     * @var array
     */
    protected static $classMap = [
        'address'      => 'Address',
        'addy'         => 'Address',
        'captcha'      => 'Captcha',
        'checkbox'     => 'Checkbox',
        'cb'           => 'Checkbox',
        'checkboxset'  => 'CheckboxSet',
        'cbs'          => 'CheckboxSet',
        'codemirror'   => 'Codemirror',
        'cm'           => 'Codemirror',
        'color'        => 'Color',
        'custom'       => 'Custom',
        'date'         => 'Date',
        'daterange'    => 'DateRange',
        'dr'           => 'DateRange',
        'editor'       => 'Editor',
        'email'        => 'Email',
        'ftp'          => 'Ftp',
        'item'         => 'Item',
        'keyvalue'     => 'KeyValue',
        'kv'           => 'KeyValue',
        'matrix'       => 'Matrix',
        'member'       => 'Member',
        'node'         => 'Node',
        'number'       => 'Number',
        '#'            => 'Number',
        'password'     => 'Password',
        'pw'           => 'Password',
        'poll'         => 'Poll',
        'radio'        => 'Radio',
        'rating'       => 'Rating',
        'search'       => 'Search',
        'select'       => 'Select',
        'socialgroup'  => 'SocialGroup',
        'sg'           => 'SocialGroup',
        'sort'         => 'Sort',
        'stack'        => 'Stack',
        'Telephone'    => 'Tel',
        'tel'          => 'Tel',
        'text'         => 'Text',
        'textarea'     => 'TextArea',
        'ta'           => 'TextArea',
        'timezone'     => 'TimeZone',
        'translatable' => 'Translatable',
        'trans'        => 'Translatable',
        'upload'       => 'Upload',
        'up'           => 'Upload',
        'url'          => 'Url',
        'widthheight'  => 'WidthHeight',
        'wh'           => 'WidthHeight',
        'yesno'        => 'YesNo',
        'yn'           => 'YesNo',
    ];

    /**
     * for use in run once the object is instantiated
     *
     * @var Form|null
     */
    protected $form;

    /**
     * form helpers store
     *
     * @var array
     */
    protected $elements = [];

    /**
     * the form record object
     *
     * @var null
     **/
    protected $obj;

    /**
     * the language prefix
     *
     * @var null
     */
    protected $langPrefix;

    /**
     * add suffix
     *
     * @var bool
     */
    protected $suffix = \false;

    /**
     * header stores
     *
     * @var null
     */
    protected $header;

    /**
     * tabs store
     *
     * @var null
     */
    protected $tab;

    /**
     * @var array
     */
    protected $bitOptions = [];

    /**
     * _Forms constructor.
     *
     * @param $config
     */
    final protected function __construct( $config )
    {

        $this->elements = $config[ 'elements' ];

        if ( isset( $config[ 'object' ] ) ) {
            $this->obj = $config[ 'object' ];
        }

        $form = $config[ 'form' ] ?? \null;

        if ( $form instanceof Form ) {
            $this->form = $form;
        }
        else {
            $this->form = new Form( $config[ 'id' ] ?? 'form', $config[ 'submitLang' ] ?? 'save', $config[ 'action' ] ?? \null, $config[ 'attributes' ] ?? [] );
        }

        if ( !isset( $config[ 'suffix' ] ) ) {
            $this->suffix = \true;
        }

        if ( isset( $config[ 'id' ] ) ) {
            $this->form->id = $config[ 'id' ];
        }
    }

    /**
     * @param array $config
     *
     * @return mixed
     * @throws \Exception
     */
    public static function execute( array $config )
    {
        $name = $config[ 'id' ] ?? md5( random_int( 1, 100000 ) );

        if ( !isset( static::$instance[ $name ] ) ) {
            static::$instance[ $name ] = new static( $config );
        }

        /**
         * @var $class static
         */
        $class = static::$instance[ $name ];
        return $class->run();
    }

    /**
     * @throws \Exception
     * @throws InvalidArgumentException
     * @return Form
     */
    public function run(): Form
    {
        $langPrefix = '';
        $currentTab = \null;
        if ( isset( $this->elements[ 'prefix' ] ) ) {
            $this->langPrefix = $langPrefix = $this->elements[ 'prefix' ];
            unset( $this->elements[ 'prefix' ] );
        }

        $typesWName = [
            'tab',
            'header',
            'sidebar',
            'helper',
            'dummy',
            'matrix',
            'hidden',
        ];

        foreach ( $this->elements as $el ) {
            if ( $el instanceof FormAbstract ) {
                $this->form->add( $el );
                continue;
            }

            if ( !is_array( $el ) || !count( $el ) ) {
                continue;
            }

            if ( isset( $el[ 'type' ] ) ) {
                $type = $el[ 'type' ];
            }
            else {
                $type = 'helper';
            }

            $name = \null;
            if ( in_array( $type, $typesWName, \true ) ) {
                if ( isset( $el[ 'name' ] ) ) {
                    $name = isset( $el[ 'skip' ] ) && $el[ 'skip' ] ? $el[ 'name' ] : $langPrefix . $el[ 'name' ];
                }
                else {
                    Debug::add( 'invalid form element', $el );
                    throw new InvalidArgumentException;
                }
            }

            $this->setExtra( $el );

            switch ( $type ) {
                case 'tab':
                    $suffix = $this->suffix ? '_tab' : '';
                    $names = $name . $suffix;
                    $this->form->addTab( $names );
                    break;
                case 'header':
                    $this->form->addHeader( $name . '_header' );
                    break;
                case 'sidebar':
                    if ( Member::loggedIn()->language()->checkKeyExists( $name ) ) {
                        $name = Member::loggedIn()->language()->addToStack( $name );
                    }

                    $this->form->addSidebar( $name );
                    break;
                case 'separator':
                    $this->form->addSeparator();
                    break;
                case 'message':
                    if ( isset( $el[ 'msg' ] ) ) {
                        $lang = $el[ 'msg' ];
                    }
                    else {
                        throw new InvalidArgumentException( 'No message set' );
                    }

                    $css = '';
                    if ( isset( $el[ 'css' ] ) ) {
                        $css = $el[ 'css' ];
                    }

                    $parse = \true;
                    if ( isset( $el[ 'parse' ] ) ) {
                        $parse = $el[ 'parse' ] ? \true : \false;
                    }

                    $id = \null;
                    if ( isset( $el[ 'id' ] ) ) {
                        $id = $el[ 'id' ];
                    }

                    $this->form->addMessage( $lang, $css, $parse, $id );
                    break;
                case 'helper':
                    if ( !isset( $el[ 'customClass' ] ) ) {
                        if ( isset( $el[ 'class' ] ) ) {
                            $class = $el[ 'class' ];

                            if ( isset( static::$classMap[ $class ] ) ) {
                                $class = static::$classMap[ $class ];
                            }

                            $class = '\\IPS\\Helpers\\Form\\' . $class;
                        }
                        else {
                            $class = Text::class;
                        }
                    }
                    else {
                        $class = $el[ 'customClass' ];
                    }

                    if ( !class_exists( $class, \true ) ) {
                        throw new InvalidArgumentException( json_encode( $el ) );
                    }

                    $default = \null;

                    if ( array_key_exists( 'default', $el ) ) {
                        $default = $el[ 'default' ];
                    }
                    else if ( array_key_exists( 'def', $el ) ) {
                        $default = $el[ 'def' ];
                    }
                    else {
                        if ( ( !array_key_exists( 'default', $el ) || !array_key_exists( 'def', $el ) ) && is_object( $this->obj ) ) {
                            $obj = $this->obj;

                            if ( method_exists( $obj, 'getProps' ) ) {
                                $props = $obj->getProps();
                                $prop = $el[ 'name' ];
                                if ( array_key_exists( $prop, $props ) ) {
                                    $default = $props[ $prop ];
                                }
                                else {
                                    $prop2 = $langPrefix . $prop;
                                    if ( array_key_exists( $prop2, $props ) ) {
                                        $default = $props[ $prop2 ];
                                    }
                                }
                            }
                            else {
                                $prop = $el[ 'name' ];
                                if ( $obj->{$prop} ) {
                                    $default = $obj->{$prop};
                                }
                                else {
                                    $prop2 = $langPrefix . $prop;
                                    if ( $obj->{$prop2} ) {
                                        $default = $obj->{$prop2};
                                    }
                                }
                            }

                            if ( $default === \null && !count( $this->bitOptions ) ) {
                                $bitoptions = $this->bitOptions;
                                /* @var array $val */
                                foreach ( $bitoptions as $key => $val ) {
                                    $break = \false;
                                    foreach ( $val as $k => $v ) {
                                        if ( !empty( $obj->{$k}[ $prop ] ) ) {
                                            $default = $obj->{$k}[ $prop ];
                                            $break = \true;
                                            break;
                                        }
                                    }
                                    if ( $break ) {
                                        break;
                                    }
                                }
                            }
                        }

                    }

                    $required = \false;
                    if ( isset( $el[ 'required' ] ) ) {
                        $required = $el[ 'required' ];
                    }

                    $options = [];
                    if ( isset( $el[ 'options' ] ) ) {
                        $options = $el[ 'options' ];
                    }
                    else {
                        if ( isset( $el[ 'ops' ] ) ) {
                            $options = $el[ 'ops' ];
                        }
                    }

                    /* @var array $toggles */
                    if ( is_array( $options ) && count( $options ) ) {
                        if ( isset( $options[ 'toggles' ] ) ) {
                            $toggles = $options[ 'toggles' ];
                            unset( $options[ 'toggles' ] );
                            foreach ( $toggles as $key => $val ) {
                                foreach ( $val as $k => $v ) {
                                    $options[ 'toggles' ][ $key ][] = 'js_' . $langPrefix . $v;
                                }
                            }
                        }

                        if ( isset( $options[ 'togglesOn' ] ) ) {
                            $toggles = $options[ 'togglesOn' ];
                            unset( $options[ 'togglesOn' ] );
                            foreach ( $toggles as $key => $val ) {
                                $options[ 'togglesOn' ][] = 'js_' . $langPrefix . $val;
                            }
                        }

                        if ( isset( $options[ 'togglesOff' ] ) ) {
                            $toggles = $options[ 'togglesOff' ];
                            unset( $options[ 'togglesOff' ] );
                            foreach ( $toggles as $key => $val ) {
                                $options[ 'togglesOff' ][] = 'js_' . $langPrefix . $val;
                            }
                        }

                        //no append
                        /* @var array $naoptions */
                        if ( isset( $options[ 'natoggles' ] ) ) {
                            $naoptions = $options[ 'natoggles' ];
                            foreach ( $naoptions as $key => $val ) {
                                foreach ( $val as $k => $v ) {
                                    $options[ 'toggles' ][ $key ][] = $v;
                                }
                            }
                        }

                        /* @var array $natogglesOn */
                        if ( isset( $options[ 'natogglesOn' ] ) ) {
                            $natogglesOn = $options[ 'natogglesOn' ];
                            foreach ( $natogglesOn as $key => $val ) {
                                $options[ 'togglesOn' ][] = $val;
                            }
                        }

                        /* @var array $naTogglesOff */
                        if ( isset( $options[ 'natogglesOff' ] ) ) {
                            $naTogglesOff = $options[ 'natogglesOff' ];
                            foreach ( $naTogglesOff as $key => $val ) {
                                $options[ 'togglesOff' ][] = $val;
                            }
                        }
                    }

                    $validation = \null;
                    if ( isset( $el[ 'validation' ] ) ) {
                        $validation = $el[ 'validation' ];
                    }
                    else {
                        if ( isset( $el[ 'v' ] ) ) {
                            $validation = $el[ 'v' ];
                        }
                    }

                    $prefix = \null;
                    if ( isset( $el[ 'prefix' ] ) ) {
                        $prefix = $el[ 'prefix' ];
                    }

                    $suffix = \null;
                    if ( isset( $el[ 'suffix' ] ) ) {
                        $suffix = $el[ 'suffix' ];
                    }

                    $id = \null;
                    if ( isset( $el[ 'id' ] ) ) {
                        $id = $el[ 'id' ];
                    }
                    else {
                        if ( !isset( $el[ 'skip_id' ] ) ) {
                            $id = 'js_' . $name;
                        }
                    }

                    $element = new $class( $name, $default, $required, $options, $validation, $prefix, $suffix, $id );

                    if ( isset( $el[ 'appearRequired' ] ) || isset( $el[ 'ap' ] ) ) {
                        $element->appearRequired = \true;
                    }

                    if ( isset( $el[ 'label' ] ) ) {
                        $label = $el[ 'label' ];
                        if ( Member::loggedIn()->language()->checkKeyExists( $label ) ) {
                            $label = Member::loggedIn()->language()->addToStack( $label );
                        }
                        $element->label = $label;
                    }

                    if ( isset( $el[ 'description' ] ) ) {
                        $desc = $el[ 'description' ];
                        if ( Member::loggedIn()->language()->checkKeyExists( $desc ) ) {
                            if ( isset( $el[ 'desc_sprintf' ] ) ) {
                                $sprintf = $el[ 'desc_sprintf' ];
                                $sprintf = (array)$sprintf;
                                $desc = Member::loggedIn()->language()->addToStack( $desc, \false, [ 'sprintf' => $sprintf ] );
                            }
                            else {
                                $desc = Member::loggedIn()->language()->addToStack( $desc );
                            }
                        }

                        Member::loggedIn()->language()->words[ $name . '_desc' ] = $desc;
                    }

                    $this->form->add( $element );
                    break;
                case 'dummy':
                    $default = \null;
                    if ( isset( $el[ 'default' ] ) ) {
                        $default = $el[ 'default' ];
                    }

                    $desc = '';
                    if ( isset( $el[ 'desc' ] ) ) {
                        if ( Member::loggedIn()->language()->checkKeyExists( $el[ 'desc' ] ) ) {
                            $desc = Member::loggedIn()->language()->addToStack( $el[ 'desc' ] );
                        }
                        else {
                            $desc = $el[ 'desc' ];
                        }
                    }

                    $warning = '';

                    if ( isset( $el[ 'warning' ] ) ) {
                        if ( Member::loggedIn()->language()->checkKeyExists( $el[ 'warning' ] ) ) {
                            $warning = Member::loggedIn()->language()->addToStack( $el[ 'warning' ] );
                        }
                        else {
                            $warning = $el[ 'warning' ];
                        }
                    }

                    if ( isset( $el[ 'id' ] ) ) {
                        $id = $el[ 'id' ];
                    }
                    else {
                        $id = $name . '_js';
                    }

                    $this->form->addDummy( $name, $default, $desc, $warning, $id );
                    break;
                case 'html':
                    if ( !isset( $el[ 'html' ] ) ) {
                        throw new InvalidArgumentException;
                    }
                    $this->form->addHtml( $el[ 'html' ] );
                    break;
                case 'matrix':
                    if ( isset( $el[ 'matrix' ] ) && !( $el[ 'matrix' ] instanceof Matrix ) ) {
                        throw new InvalidArgumentException;
                    }

                    $this->form->addMatrix( $name, $el[ 'matrix' ] );
                    break;
                case 'hidden':
                    $this->form->hiddenValues[ $name ] = $el[ 'default' ];
                    break;
            }
        }

        return $this->form;
    }

    /**
     * @param $el
     */
    final protected function setExtra( $el )
    {

        $suffix = $this->suffix ? '_tab' : '';

        if ( isset( $el[ 'tab' ] ) ) {
            $tab = $this->langPrefix . $el[ 'tab' ] . $suffix;
            $this->tab = $tab;
            $this->form->addTab( $tab );
            unset( $el[ 'tab' ] );
        }

        $suffix = $this->suffix ? '_header' : '';
        if ( isset( $el[ 'header' ] ) && $this->header !== $this->langPrefix . $el[ 'header' ] . $suffix ) {
            $header = $this->langPrefix . $el[ 'header' ] . $suffix;
            $this->header = $header;
            $this->form->addHeader( $header );
            unset( $el[ 'header' ] );
        }

        if ( isset( $el[ 'sidebar' ] ) ) {
            $sideBar = $this->langPrefix . $el[ 'sidebar' ] . '_sidebar';
            if ( Member::loggedIn()->language()->checkKeyExists( $sideBar ) ) {
                $sideBar = Member::loggedIn()->language()->addToStack( $sideBar );
            }

            $this->form->addSidebar( $sideBar );
            unset( $el[ 'sidebar' ] );
        }

    }
}
