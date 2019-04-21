<?php

namespace IPS\toolbox\Forms;

use IPS\Helpers\Form\FormAbstract;
use IPS\Helpers\Form\Matrix;
use IPS\Helpers\Form\Text;
use IPS\Log;
use IPS\Member;
use IPS\Theme;
use function in_array;
use function mb_strlen;
use function mb_strtolower;

/**
 * Class _Form
 *
 * @package IPS\toolbox\Forms
 * @mixin \IPS\toolbox\Forms\Form
 */
class _Form
{
    /**
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
        'interval'     => 'Interval',
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
     * @var Form
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
     * @var \IPS\Lang
     */
    protected $lang;

    /**
     * @var string
     */
    protected $header;

    /**
     * Form constructor.
     *
     * @param Form|null $form
     */
    public function __construct( \IPS\Helpers\Form $form = null )
    {
        $this->lang = Member::loggedIn()->language();
        if ( $form === null ) {
            $this->form = new \IPS\Helpers\Form();
        }
        else if ( $form instanceof \IPS\Helpers\Form ) {
            $this->form = $form;
        }
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    public static function constructFromForm( \IPS\Helpers\Form $form ): Form
    {
        return new static( $form );
    }

    /**
     * @return Form
     */
    public static function create(): Form
    {
        return new static();
    }

    /**
     * @param $class
     *
     * @return Form
     */
    public function formClass( $class ): Form
    {
        $this->form->class = $class;
        return $this;
    }

    /**
     * @param $prefix
     *
     * @return Form
     */
    public function formPrefix( $prefix ): Form
    {
        $this->formPrefix = $prefix;
        return $this;
    }

    /**
     * @param array $bitOptions
     *
     * @return Form
     */
    public function bitOptions( array $bitOptions ): Form
    {
        $this->bitOptions = $bitOptions;
        return $this;
    }

    /**
     * @param $object
     *
     * @return Form
     */
    public function object( $object ): Form
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @param $id
     *
     * @return Form
     */
    public function formId( $id ): Form
    {
        $this->form->id = $id;
        return $this;
    }

    /**
     * @param $action
     *
     * @return Form
     */
    public function action( $action ): Form
    {
        $this->form->action = $action;
        return $this;
    }

    /**
     * @param $langKey
     *
     * @return Form
     * @throws \UnexpectedValueException
     */
    public function submitLang( $langKey ): Form
    {
        $this->form->actionButtons[ 0 ] = Theme::i()
                                               ->getTemplate( 'forms', 'core', 'global' )
                                               ->button( $langKey, 'submit', null, 'ipsButton ipsButton_primary', [
                                                   'tabindex'  => '2',
                                                   'accesskey' => 's',
                                               ] );

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return Form
     */
    public function attributes( array $attributes ): Form
    {
        $this->form->attributes = array_merge( $this->form->attributes, $attributes );
        return $this;
    }

    /**
     * @param FormAbstract $helper
     *
     * @return $this
     */
    public function addHelper( FormAbstract $helper ): self
    {
        $this->elementStore[] = $helper;
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $extra
     *
     * @return \IPS\toolbox\Forms\FormAbstract
     */
    public function element( string $name, string $type = 'text', array $extra = [] ): \IPS\toolbox\Forms\Element
    {
        $notHelpers = [
            'tab',
            'header',
            'separator',
            'message',
            'dummy',
            'html',
            'matrix',
            'hidden',
        ];
        $type = static::$classMap[ $type ] ?? 'text';
        $el = [ 'name' => $name, 'id' => 'js_' . $name ];
        if ( in_array( $type, $notHelpers, true ) ) {
            unset( $el[ 'class' ] );
            $el[ 'type' ] = $type;
        }
        else {
            $el[ 'class' ] = $type;
        }

        $el[ 'extra' ] = $extra;

        $this->elementStore[ $name ] = $el;
        return new \IPS\toolbox\Forms\Element( $name, $type, $this );
    }

    public function updateElement( $name, $key, $value ): void
    {
        if ( isset( $this->elementStore[ $name ] ) ) {
            $el = $this->elementStore[ $name ];
            $options = [
                'toggles'      => 1,
                'natoggles'    => 1,
                'togglesOff'   => 1,
                'natogglesOff' => 1,
                'togglesOn'    => 1,
                'natogglesOn'  => 1,
            ];
            if ( isset( $options[ $key ] ) ) {
                $el[ 'options' ][ $key ] = $value;
            }
            else {
                $el[ $key ] = $value;
            }
            $this->elementStore[ $name ] = $el;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->build();
    }

    protected $built = false;
    /**
     * @return Form
     */
    public function build(): \IPS\Helpers\Form
    {
        $this->built = true;
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

        foreach ( $this->elementStore as $key => $el ) {
            if ( $el instanceof FormAbstract ) {
                $this->form->add( $el );
                continue;
            }

            if ( empty( $el ) ) {
                continue;
            }

            $type = $el[ 'type' ] ?? 'helper';
            $name = \null;
            $plain = '';
            $extra = $el[ 'extra' ] ?? [];
            if ( in_array( $type, $typesWName, \true ) ) {

                if ( empty( $key ) && !isset( $el[ 'name' ] ) ) {
                    Log::log( 'Form Helper type requires a name!' );
                    continue;
                }

                $skip = $el[ 'skip' ] ?? \false;
                if ( !$skip ) {
                    $name = $this->formPrefix;
                    $plain = $el[ 'name' ] ?? $key;
                    $name .= $plain;
                }
                else {
                    $name = $el[ 'name' ] ?? $key;
                    $plain = $name;
                }
            }

            $this->extra( $el );

            switch ( $type ) {
                case 'tab':
                    $suffix = '_tab';
                    $names = $name . $suffix;
                    $this->form->addTab( $names );
                    break;
                case 'header':
                    $this->form->addHeader( $name . '_header' );
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
                    if ( $name === \null ) {
                        continue 2;
                    }
                    $id = $el[ 'id' ] ?? \null;
                    $parse = false;
                    if ( $this->lang->checkKeyExists( $name ) ) {
                        $parse = true;
                        if ( isset( $extra[ 'sprintf' ] ) ) {
                            $parse = false;
                            $sprintf = explode( ',', $extra[ 'sprintf' ] );
                            $name = $this->lang->addToStack( $name, \false, [ 'sprintf' => $sprintf ] );
                        }
                    }
                    $css = $extra[ 'css' ] ?? '';
                    $this->form->addMessage( $name, $css, $parse, $id );
                    break;
                case 'helper':

                    $class = $el[ 'class' ] ?? Text::class;

                    if ( $class !== Text::class && isset( static::$classMap[ mb_strtolower( $class ) ] ) ) {
                        $class = static::$classMap[ $class ] ?? $class;
                        $class = '\\IPS\\Helpers\\Form\\' . $class;
                    }

                    if ( !class_exists( $class, \true ) ) {
                        Log::debug( 'invalid form class ' . $class );
                        continue 2;
                    }

                    $required = $el[ 'required' ] ?? \false;
                    $options = $el[ 'options' ] ?? [];
                    $validation = $el[ 'validation' ] ?? \null;
                    $prefix = $el[ 'prefix' ] ?? \null;
                    $suffix = $el[ 'suffix' ] ?? \null;
                    $id = $el[ 'id' ] ?? \null;
                    $default = $el[ 'default' ] ?? \null;

                    if ( $id === \null ) {
                        $id = 'js_' . $name;
                    }

                    if ( $default === \null ) {
                        $obj = $this->object;
                        $prop = $plain;
                        $prop2 = $this->formPrefix . $prop;

                        if ( \is_object( $obj ) ) {
                            $default = $obj->{$prop} ?? $obj->{$prop2} ?? \null;
                        }

                        if ( $default === \null && !empty( $this->bitOptions ) && \is_object( $this->obj ) ) {
                            /* @var array $val */
                            foreach ( $this->bitOptions as $bit => $val ) {
                                $break = \false;
                                foreach ( $val as $k => $v ) {
                                    if ( !empty( $obj->{$k}[ $prop ] ) ) {
                                        $default = $obj->{$k}[ $prop ];
                                        $break = \true;
                                        break;
                                    }

                                    if ( !empty( $obj->{$k}[ $prop2 ] ) ) {
                                        $default = $obj->{$k}[ $prop2 ];
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

                    /* @var array $toggles */
                    if ( !empty( $options ) ) {
                        if ( isset( $options[ 'toggles' ] ) ) {
                            $toggles = $options[ 'toggles' ];
                            unset( $options[ 'toggles' ] );
                            foreach ( $toggles as $k => $val ) {
                                foreach ( $val as $v ) {
                                    $options[ 'toggles' ][ $k ][] = 'js_' . $this->formPrefix . $v;
                                }
                            }
                        }

                        if ( isset( $options[ 'togglesOn' ] ) ) {
                            $toggles = $options[ 'togglesOn' ];
                            unset( $options[ 'togglesOn' ] );
                            foreach ( $toggles as $val ) {
                                $options[ 'togglesOn' ][] = 'js_' . $this->formPrefix . $val;
                            }
                        }

                        if ( isset( $options[ 'togglesOff' ] ) ) {
                            $toggles = $options[ 'togglesOff' ];
                            unset( $options[ 'togglesOff' ] );
                            foreach ( $toggles as $val ) {
                                $options[ 'togglesOff' ][] = 'js_' . $this->formPrefix . $val;
                            }
                        }

                        //no append
                        /* @var array $naoptions */
                        if ( isset( $options[ 'natoggles' ] ) ) {
                            $naoptions = $options[ 'natoggles' ];
                            foreach ( $naoptions as $k => $val ) {
                                foreach ( $val as $v ) {
                                    $options[ 'toggles' ][ $k ][] = $v;
                                }
                            }
                        }

                        /* @var array $natogglesOn */
                        if ( isset( $options[ 'natogglesOn' ] ) ) {
                            $natogglesOn = $options[ 'natogglesOn' ];
                            foreach ( $natogglesOn as $val ) {
                                $options[ 'togglesOn' ][] = $val;
                            }
                        }

                        /* @var array $naTogglesOff */
                        if ( isset( $options[ 'natogglesOff' ] ) ) {
                            $naTogglesOff = $options[ 'natogglesOff' ];
                            foreach ( $naTogglesOff as $val ) {
                                $options[ 'togglesOff' ][] = $val;
                            }
                        }
                    }

                    $element = new $class( $name, $default, $required, $options, $validation, $prefix, $suffix, $id );

                    $element->appearRequried = $el[ 'appearRequired' ] ?? \false;

                    if ( isset( $el[ 'label' ] ) ) {
                        $prefix = $this->formPrefix;
                        $label = $el[ 'label' ];
                        if ( $this->lang->checkKeyExists( $label ) ) {
                            $label = $this->lang->addToStack( $label );
                        }

                        if ( $this->lang->checkKeyExists( $prefix . $label ) ) {
                            $label = $this->lang->addToStack( $prefix . $label );
                        }

                        $element->label = $label;
                    }

                    if ( isset( $el[ 'description' ] ) ) {
                        $desc = $el[ 'description' ];
                        if ( $this->lang->checkKeyExists( $desc ) ) {
                            if ( isset( $el[ 'descriptionSprintf' ] ) ) {
                                $sprintf = $el[ 'descriptionSprintf' ];
                                $sprintf = (array)$sprintf;
                                $desc = $this->lang->addToStack( $desc, \false, [ 'sprintf' => $sprintf ] );
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
                    $default = $el[ 'default' ] ?? \null;
                    $desc = '';
                    if ( isset( $el[ 'desc' ] ) ) {
                        if ( $this->lang->checkKeyExists( $el[ 'desc' ] ) ) {
                            $desc = $this->lang->addToStack( $el[ 'desc' ] );
                        }
                        else {
                            $desc = $el[ 'desc' ];
                        }
                    }

                    $warning = '';

                    if ( isset( $extra[ 'warning' ] ) ) {
                        if ( $this->lang->checkKeyExists( $extra[ 'warning' ] ) ) {
                            $warning = $this->lang->addToStack( $extra[ 'warning' ] );
                        }
                        else {
                            $warning = $extra[ 'warning' ];
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
                    if ( !isset( $extra[ 'html' ] ) ) {
                        continue 2;
                    }
                    $this->form->addHtml( $extra[ 'html' ] );
                    break;
                case 'matrix':
                    if ( isset( $extra[ 'matrix' ] ) && !( $el[ 'matrix' ] instanceof Matrix ) ) {
                        continue 2;
                    }

                    $this->form->addMatrix( $name, $extra[ 'matrix' ] );
                    break;
                case 'hidden':
                    $this->form->hiddenValues[ $name ] = $el[ 'default' ];
                    break;
            }
        }

        return $this->form;
    }

    /**
     * @param array $el
     */
    protected function extra( array $el ): void
    {
        $suffix = '_tab';

        if ( isset( $el[ 'tab' ] ) ) {
            $tab = $this->formPrefix . $el[ 'tab' ] . $suffix;
            $this->tab = $tab;
            $this->form->addTab( $tab );
            unset( $el[ 'tab' ] );
        }

        $suffix = '_header';
        if ( isset( $el[ 'header' ] ) && $this->header !== $this->formPrefix . $el[ 'header' ] . $suffix ) {
            $header = $this->formPrefix . $el[ 'header' ] . $suffix;
            $this->header = $header;
            $this->form->addHeader( $header );
            unset( $el[ 'header' ] );
        }

        if ( isset( $el[ 'sidebar' ] ) ) {
            $sideBar = $this->formPrefix . $el[ 'sidebar' ] . '_sidebar';
            if ( $this->lang->checkKeyExists( $sideBar ) ) {
                $sideBar = $this->lang->addToStack( $sideBar );
            }

            $this->form->addSidebar( $sideBar );
            unset( $el[ 'sidebar' ] );
        }
    }

    /**
     * @return bool|array
     */
    public function values()
    {
        if( $this->built === false ) {
            $this->build();
        }
        $newValues = false;
        if ( $values = $this->form->values() ) {
            foreach ( $values as $key => $value ) {
                $key = $this->stripPrefix( $key );
                $newValues[ $key ] = $value;
            }
        }

        return $newValues;
    }

    public function saveAsSettings( $values = null ){
        if( $values === null ){
            $values = $this->values();
        }

        $this->form->saveAsSettings($values);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function stripPrefix( $key ): string
    {
        return mb_substr( $key, mb_strlen( $this->formPrefix ) );
    }
}
