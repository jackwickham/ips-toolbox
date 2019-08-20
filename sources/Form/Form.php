<?php

namespace IPS\toolbox;

use InvalidArgumentException;
use IPS\Content\Item;
use IPS\Helpers\Form\FormAbstract;
use IPS\Helpers\Form\Matrix;
use IPS\Lang;
use IPS\Log;
use IPS\Login;
use IPS\Member;
use IPS\Request;
use IPS\Session;
use IPS\stratagem\Form;
use IPS\Theme;
use IPS\toolbox\Form\Element;
use function array_merge;
use function class_exists;
use function count;
use function in_array;
use function is_array;
use function is_object;
use function mb_strlen;
use function mb_strpos;
use function mb_strtolower;
use function mb_substr;
use function property_exists;
use function sha1;

/**
 * Class Form
 *
 * @package Form
 * @mixin Form
 */
class _Form
{

    /**
     * @var
     */
    public $error;

    /**
     * @var \IPS\Helpers\Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $id = 'default';

    /**
     * @var array
     */
    protected $elementStore = [];

    /**
     * @var object
     */
    protected $object;

    /**
     * @var array
     */
    protected $bitOptions;

    /**
     * @var string
     */
    protected $formPrefix;

    /**
     * @var Lang
     */
    protected $lang;

    /**
     * @var string
     */
    protected $header;

    /**
     * @var bool
     */
    protected $built = \false;

    /**
     * @var array
     */
    protected $bitOptionsPrefixes = [];

    /**
     * @var bool
     */
    protected $stripPrefix = \true;

    /**
     * @var bool
     */
    protected $suffix = \true;

    /**
     * @var bool
     */
    protected $addDbPrefix = true;

    protected $dialogForm = false;

    protected $doBitWise = true;

    protected $activeTab;

    /**
     * Form constructor.
     *
     * @param \IPS\Helpers\Form|null $form
     */
    public function __construct( \IPS\Helpers\Form $form = \null )
    {

        $this->lang = Member::loggedIn()->language();
        if ( $form === \null ) {
            $this->form = new \IPS\Helpers\Form();
        }
        else if ( $form instanceof \IPS\Helpers\Form ) {
            $this->form = $form;
        }
    }

    /**
     * @param \IPS\Helpers\Form|Form|null $form
     *
     * @return \IPS\stratagem\_Form
     */
    public static function create( \IPS\Helpers\Form $form = \null ): self
    {

        return new static( $form );
    }

    /**
     * @param $class
     *
     * @return self
     */
    public function formClass( $class ): self
    {

        $this->form->class = $class;

        return $this;
    }

    public function dialogForm()
    {

        $this->dialogForm = true;

        return $this;
    }

    /**
     * @param $prefix
     *
     * @return self
     */
    public function formPrefix( $prefix ): self
    {

        $this->formPrefix = $prefix;

        return $this;
    }

    /**
     * @param array $bitOptions
     *
     * @return self
     */
    public function bitOptions( array $bitOptions ): self
    {

        $this->bitOptions = $bitOptions;

        return $this;
    }

    /**
     * @param $object
     *
     * @return self
     */
    public function object( $object ): self
    {

        $this->object = $object;
        if ( $this->formPrefix === \null && property_exists( $object, 'formLangPrefix' ) ) {
            $this->formPrefix = $object::$formLangPrefix;
        }

        return $this;
    }

    /**
     * @param $id
     *
     * @return self
     */
    public function formId( $id ): self
    {

        $this->form->id = $id;

        return $this;
    }

    /**
     * @param $action
     *
     * @return self
     */
    public function action( $action ): self
    {

        $this->form->action = $action;

        return $this;
    }

    /**
     * @param $langKey
     *
     * @return self
     * @throws \UnexpectedValueException
     */
    public function submitLang( $langKey ): self
    {

        $this->form->actionButtons[ 0 ] = Theme::i()->getTemplate( 'forms', 'core', 'global' )->button( $langKey, 'submit', \null, 'ipsButton ipsButton_primary', [
            'tabindex'  => '2',
            'accesskey' => 's',
        ] );

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return self
     */
    public function attributes( array $attributes ): self
    {

        $this->form->attributes = array_merge( $this->form->attributes, $attributes );

        return $this;
    }

    /**
     * @param FormAbstract $helper
     *
     * @return self
     */
    public function addHelper( FormAbstract $helper ): self
    {

        $this->elementStore[] = $helper;

        return $this;
    }

    /**
     * @param $name
     *
     * @return \IPS\cjmedia\Form\Element
     */
    public function element( $name ): \IPS\cjmedia\Form\Element
    {

        if ( isset( $this->elementStore[ $name ] ) ) {

            return $this->elementStore[ $name ];
        }

        throw new InvalidArgumentException( 'element ' . $name . ' doesn\'t exist' );
    }

    public function noSuffix()
    {

        $this->suffix = \false;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->build();
    }

    public function build()
    {

        $this->built = \true;
        if ( $this->error !== \null ) {
            $this->form->error = $this->error;
        }
        $typesWName = [
            'tab',
            'header',
            'sidebar',
            'helper',
            'dummy',
            'matrix',
            'hidden',
            'message',
        ];

        /** @var \IPS\stratagem\Form\Element $el */
        foreach ( $this->elementStore as $el ) {
            if ( $el instanceof FormAbstract ) {
                $this->form->add( $el );
                continue;
            }

            if ( !( $el instanceof Element ) ) {
                continue;
            }

            $type = $el->type ?? 'helper';
            $name = \null;
            $plain = '';
            $extra = $el->extra ?? [];
            $default = $el->value;
            $id = $el->id;
            if ( in_array( $type, $typesWName, \true ) ) {
                $skip = $el->skip;
                $plain = $el->name;
                $name = $skip ? '' : $this->formPrefix ?? '';
                $name .= $plain;
            }

            if ( $id === \null ) {
                $id = 'js_' . $name;
            }

            if ( $el->tab !== \null ) {
                $suffix = $this->suffix ? '_tab' : '';
                $tab = $this->formPrefix . $el->tab . $suffix;
                $this->form->addTab( $tab );
            }

            if ( $el->header !== \null ) {
                $suffix = $this->suffix ? '_header' : '';
                $header = $this->formPrefix . $el->header . $suffix;
                $this->form->addHeader( $header );
            }

            if ( $el->sidebar ) {
                $suffix = $this->suffix ? '_sidebar' : '';
                $sideBar = $this->formPrefix . $el->sidebar . $suffix;
                if ( $this->lang->checkKeyExists( $sideBar ) ) {
                    $sideBar = $this->lang->addToStack( $sideBar );
                }

                $this->form->addSidebar( $sideBar );
            }

            switch ( $type ) {
                case 'tab':
                    $suffix = $this->suffix ? '_tab' : '';
                    $names = $name . $suffix;
                    $this->form->addTab( $names );
                    break;
                case 'header':
                    $suffix = $this->suffix ? '_header' : '';
                    $this->form->addHeader( $name . $suffix );
                    break;
                case 'sidebar':
                    if ( $this->lang->checkKeyExists( $name ) ) {
                        $name = $this->lang->addToStack( $name );
                    }

                    $this->form->addSidebar( $name );
                    break;
                case 'separator':
                    $this->form->addSeparator();
                    break;
                case 'message':
                    $parse = \false;
                    if ( $this->lang->checkKeyExists( $name ) ) {
                        $parse = \true;
                        if ( isset( $extra[ 'sprintf' ] ) ) {
                            $parse = \false;
                            $sprintf = $extra[ 'sprintf' ];
                            $name = $this->lang->addToStack( $name, \false, [ 'sprintf' => $sprintf ] );
                        }
                    }
                    $css = $extra[ 'css' ] ?? '';
                    $this->form->addMessage( $name, $css, $parse, $id );
                    break;
                case 'helper':
                    $class = $el->class;
                    if ( $el->custom === \false && isset( Element::$helpers[ mb_strtolower( $class ) ] ) ) {
                        $class = Element::$helpers[ $class ] ?? $class;
                        $class = '\\IPS\\Helpers\\Form\\' . $class;
                    }

                    if ( !class_exists( $class, \true ) ) {
                        Log::debug( 'invalid form class ' . $class );
                        continue 2;
                    }

                    $required = $el->required;
                    $options = $el->options;
                    $validation = $el->validationCallback;
                    $prefix = $el->prefix;
                    $suffix = $el->suffix;
                    $toggles = $el->toggles;
                    if ( $default === \null ) {
                        $obj = $this->object;
                        $prop = $plain;
                        $prop2 = $this->formPrefix . $prop;

                        if ( is_object( $obj ) ) {
                            $default = $obj->{$prop} ?? $obj->{$prop2} ?? \null;
                        }

                        if ( $default === \null && empty( $this->bitOptions ) === \false && is_object( $this->object ) ) {
                            /* @var array $val */
                            foreach ( $this->bitOptions as $bit => $val ) {
                                foreach ( $val as $k => $v ) {
                                    if ( !empty( $obj->{$k}[ $prop ] ) ) {
                                        $default = $obj->{$k}[ $prop ];
                                        break 2;
                                    }

                                    if ( !empty( $obj->{$k}[ $prop2 ] ) ) {
                                        $default = $obj->{$k}[ $prop2 ];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    if ( empty( $default ) === \true && $el->empty !== \null ) {
                        $default = $el->empty;
                    }

                    /* @var array $toggles */

                    if ( empty( $toggles ) !== \true ) {
                        foreach ( $toggles as $toggle ) {
                            if ( isset( $toggle[ 'key' ] ) ) {
                                switch ( $toggle[ 'key' ] ) {
                                    case 'toggles':
                                    case 'natoggles':
                                        foreach ( $toggle[ 'elements' ] as $k => $val ) {
                                            foreach ( $val as $v ) {
                                                $options[ 'toggles' ][ $k ][] = $toggle[ 'key' ] === 'toggles' ? 'js_' . $this->formPrefix . $v : $v;
                                            }
                                        }
                                        break;
                                    case 'togglesOn':
                                    case 'natogglesOn':
                                        foreach ( $toggle[ 'elements' ] as $val ) {
                                            $options[ 'togglesOn' ][] = $toggle[ 'key' ] === 'togglesOn' ? 'js_' . $this->formPrefix . $val : $val;
                                        }
                                        break;
                                    case 'togglesOff':
                                    case 'natogglesOff':
                                        foreach ( $toggle[ 'elements' ] as $val ) {
                                            $options[ 'togglesOff' ][] = $toggle[ 'key' ] === 'togglesOff' ? 'js_' . $this->formPrefix . $val : $val;
                                        }
                                        break;
                                }

                            }
                        }
                    }

                    if ( $class === '\\IPS\\Helpers\\Form\\Select' && is_array( $options ) && isset( $options[ 'options' ] ) && isset( $options[ 'parse' ] ) && $options[ 'parse' ] === 'lang' ) {
                        $langs = [];

                        foreach ( $options[ 'options' ] as $key => $val ) {
                            $langs[ $key ] = $this->formPrefix . $val . '_options';
                        }

                        $options[ 'options' ] = $langs;
                    }

                    $element = new $class( $name, $default, $required, $options, $validation, $prefix, $suffix, $id );
                    $element->appearRequried = $el->appearRequired;

                    if ( is_array( $el->label ) && isset( $el->label[ 'key' ] ) ) {
                        $label = $el->label[ 'key' ];
                        if ( $this->lang->checkKeyExists( $this->formPrefix . $label ) ) {
                            if ( isset( $el->label[ 'sprintf' ] ) && is_array( $el->label[ 'sprintf' ] ) ) {
                                $label = $this->lang->addToStack( $this->formPrefix . $label, [ 'sprintf' => $el->label[ 'sprintf' ] ] );
                            }
                            else {
                                $label = $this->lang->addToStack( $this->formPrefix . $label );
                            }
                        }

                        if ( $label === $el->label[ 'key' ] && $this->lang->checkKeyExists( $label ) ) {
                            if ( isset( $el->label[ 'sprintf' ] ) && is_array( $el->label[ 'sprintf' ] ) ) {
                                $label = $this->lang->addToStack( $label, [ 'sprintf' => $el->label[ 'sprintf' ] ] );
                            }
                            else {
                                $label = $this->lang->addToStack( $label );
                            }
                        }

                        $element->label = $label;
                    }

                    if ( is_array( $el->description ) && isset( $el->description[ 'key' ] ) ) {
                        $desc = $el->description[ 'key' ];

                        if ( $this->lang->checkKeyExists( $this->formPrefix . $desc ) ) {
                            if ( isset( $el->description[ 'sprintf' ] ) ) {

                                $desc = $this->lang->addToStack( $this->formPrefix . $desc, \false, [ 'sprintf' => $el->description[ 'sprintf' ] ] );
                            }
                            else {
                                $desc = $this->lang->addToStack( $this->formPrefix . $desc );
                            }
                        }

                        if ( $desc === $el->description[ 'key' ] && $this->lang->checkKeyExists( $desc ) ) {
                            if ( isset( $el->description[ 'sprintf' ] ) ) {

                                $desc = $this->lang->addToStack( $desc, \false, [ 'sprintf' => $el->description[ 'sprintf' ] ] );
                            }
                            else {
                                $desc = $this->lang->addToStack( $desc );
                            }
                        }

                        $this->lang->words[ $name . '_desc' ] = $desc;
                    }

                    $this->form->add( $element );
                    break;
                case 'dummy':
                    $desc = '';
                    $warning = '';

                    if ( is_array( $el->description ) && isset( $el->description[ 'key' ] ) ) {
                        $desc = $el->description[ 'key' ];

                        if ( $this->lang->checkKeyExists( $this->formPrefix . $desc ) ) {
                            if ( isset( $el->description[ 'sprintf' ] ) ) {

                                $desc = $this->lang->addToStack( $this->formPrefix . $desc, \false, [ 'sprintf' => $el->description[ 'sprintf' ] ] );
                            }
                            else {
                                $desc = $this->lang->addToStack( $this->formPrefix . $desc );
                            }
                        }

                        if ( $desc === $el->description[ 'key' ] && $this->lang->checkKeyExists( $desc ) ) {
                            if ( isset( $el->description[ 'sprintf' ] ) ) {

                                $desc = $this->lang->addToStack( $desc, \false, [ 'sprintf' => $el->description[ 'sprintf' ] ] );
                            }
                            else {
                                $desc = $this->lang->addToStack( $desc );
                            }
                        }
                    }

                    if ( isset( $extra[ 'warning' ] ) ) {
                        if ( $this->lang->checkKeyExists( $extra[ 'warning' ] ) ) {
                            $warning = $this->lang->addToStack( $extra[ 'warning' ] );
                        }
                        else {
                            $warning = $extra[ 'warning' ];
                        }
                    }

                    $this->form->addDummy( $name, $default, $desc, $warning, $id );
                    break;
                case 'html':
                    $this->form->addHtml( $default );
                    break;
                case 'matrix':
                    if ( !( $default instanceof Matrix ) ) {
                        continue 2;
                    }
                    $this->form->addMatrix( $name, $default );
                    break;
                case 'hidden':
                    $this->form->hiddenValues[ $name ] = $default;
                    break;
            }
        }
        if ( $this->activeTab !== null ) {
            $this->form->activeTab = $this->activeTab;
        }

        if ( $this->dialogForm === true ) {
            return $this->form->customTemplate( [ Theme::i()->getTemplate( 'forms', 'core' ), 'popupTemplate' ] );
        }

        return $this->form;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $extra
     *
     * @return Element
     */
    public function add( string $name, string $type = 'text', string $custom = '' ): Element
    {

        $this->elementStore[ $name ] = new Element( $name, $type, $custom );

        return $this->elementStore[ $name ];
    }

    public function header( $header )
    {

        $this->elementStore[ $header ] = new Element( $header, 'header' );

        return $this;
    }

    public function separator()
    {

        $name = 'separator_' . ( count( $this->elementStore ) + 1 );
        $this->elementStore[ $name ] = new Element( $name, 'separator' );

        return $this;
    }

    public function message( string $name, $css = '', array $sprintf = [] )
    {

        $key = $name . '_message';
        $this->elementStore[ $key ] = ( new Element( $name, 'message' ) )->extra( [ 'css' => $css ] );

        return $this;
    }

    public function dummy( string $name, $value, array $desc = [], array $warning = [] )
    {

        $key = $name . '_dummy';
        $this->elementStore[ $key ] = ( new Element( $name, 'dummy' ) )->value( $value )->description( $desc )->extra( [ 'warning' => $warning ] );

        return $this;
    }

    public function saveAsSettings( $values = \null )
    {

        if ( $values === \null ) {
            $values = $this->values();
        }

        $this->form->saveAsSettings( $values );
    }

    /**
     * @return bool|array
     */
    public function values()
    {

        $name = "{$this->form->id}_submitted";

        $newValues = [];
        /* Did we submit the form? */
        if ( isset( Request::i()->{$name} ) && Login::compareHashes( (string)Session::i()->csrfKey, (string)Request::i()->csrfKey ) ) {
            if ( $this->built === \false ) {
                $this->build();
            }

            if ( $values = $this->form->values() ) {
                foreach ( $values as $key => $value ) {
                    $og = $key;
                    $key = $this->stripPrefix( $key );

                    $dbPrefix = '';
                    if ( $this->formPrefix && mb_strpos( $og, $this->formPrefix ) !== \false && is_object( $this->object ) && !( $this->object instanceof Item ) && property_exists( $this->object, 'databasePrefix' ) ) {
                        $object = $this->object;
                        $dbPrefix = $object::$databasePrefix;
                    }
                    $newValues[ $dbPrefix . $key ] = $value;
                }
            }

            return $newValues;
        }

        return false;
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function stripPrefix( $key ): string
    {

        if ( $this->formPrefix && $this->stripPrefix === \true && mb_strpos( $key, $this->formPrefix ) !== \false ) {
            return mb_substr( $key, mb_strlen( $this->formPrefix ) );
        }

        return $key;
    }

    public function noBitWise()
    {

        $this->doBitWise = false;

        return $this;
    }

    public function removePrefix( bool $strip = \false )
    {

        $this->stripPrefix = $strip;

        return $this;
    }

    public function activeTab( $tab )
    {

        $this->activeTab = $this->formPrefix . $tab . '_tab';

        return $this;
    }

    public function store(): array
    {

        return $this->elementStore;
    }

    public function getLastUsedTab()
    {

        return $this->form->getLastUsedTab();
    }

    public function tab( $name )
    {

        $key = $name . '_tab';
        $this->elementStore[ $key ] = new Element( $name, 'tab' );

        return $this;
    }

    public function html( $html )
    {

        $name = sha1( $html );
        $this->elementStore[ $name ] = ( new Element( $name, 'html' ) )->value( $html );

        return $this;
    }

    public function matrix( string $name, Matrix $matrix )
    {

        $key = $name . '_matrix';
        $this->elementStore[ $key ] = ( new Element( $name, 'matrix' ) )->value( $matrix );

        return $this;
    }

    public function hidden( string $name, $value )
    {

        $key = $name . '_hidden';
        $this->elementStore[ $key ] = ( new Element( $name, 'hidden' ) )->value( $value );

        return $this;
    }

    public function sideBar( $content, $prefix = \true )
    {

        $name = sha1( $content );
        $this->elementStore[ $name ] = new Element( $content, 'sidebar' );

        if ( $prefix === \false ) {
            $this->elementStore[ $name ]->skip();
        }

        return $this;
    }
}
