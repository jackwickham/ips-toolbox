<?php

/**
 * @brief       Form Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.3.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Sources\Generator;

use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyValueGenerator;

class _Form extends GeneratorAbstract
{

    public function bodyGenerator()
    {

        $this->brief = 'Class';
        $this->instance();
        $this->classMap();
        $this->form();
        $this->elements();
        $this->object();
        $this->prefix();
        $this->suffix();
        $this->header();
        $this->tab();
        $this->bitOptions();
        $this->props();
        $this->lang();
        $this->constructor();
        $this->buildForm();
        $this->build();
        $this->extra();

        $this->generator->addUse( \IPS\Log::class );
        $this->generator->addUse( \IPS\Helpers\Form\Text::class );
        $this->generator->addUse( \IPS\Helpers\Form\Matrix::class );
        $this->generator->addUse( \IPS\Helpers\Form\FormAbstract::class );
        $this->generator->addUse( \IPS\Member::class );
        $this->generator->addUse( \IPS\Helpers\Form::class );
        $this->generator->addImportFunction( 'defined' );
        $this->generator->addImportFunction( 'header' );
        $this->generator->addImportFunction( 'in_array' );
        $this->generator->addImportFunction( 'is_object' );
        $this->generator->addImportFunction( 'class_exists' );
        $this->generator->addImportFunction( 'in_array' );
        $this->generator->addImportFunction( 'property_exists' );
    }

    protected function instance()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'var', 'description' => 'Form' ],
            ],
        ];

        $config = [
            'name'   => 'nodeTitle',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );

        $doc = [
            '@var Form',
        ];
        $this->generator->addProperty( 'node' );
    }

    protected function classMap()
    {

        $classMap = [
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

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'the class map for form elements' ],
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => 'classMap',
            'value'  => new PropertyValueGenerator( $classMap, PropertyValueGenerator::TYPE_ARRAY, PropertyValueGenerator::OUTPUT_MULTIPLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    protected function form()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'for use in run once the object is instantiated' ],
                [ 'name' => 'var', 'description' => 'Form' ],
            ],
        ];

        $config = [
            'name'   => 'form',
            'value'  => new PropertyValueGenerator( '', PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function elements()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'form helpers store' ],
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => 'elements',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function object()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'the form record object' ],
                [ 'name' => 'var', 'description' => 'null' ],
            ],
        ];

        $config = [
            'name'   => 'obj',
            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function prefix()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'the language prefix' ],
                [ 'name' => 'var', 'description' => 'null' ],
            ],
        ];

        $config = [
            'name'   => 'langPrefix',
            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function suffix()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'add header template' ],
                [ 'name' => 'var', 'description' => 'bool' ],
            ],
        ];

        $config = [
            'name'   => 'suffix',
            'value'  => new PropertyValueGenerator( \false, PropertyValueGenerator::TYPE_BOOL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function header()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'header store' ],
                [ 'name' => 'var', 'description' => 'null' ],
            ],
        ];

        $config = [
            'name'   => 'header',
            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function tab()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'tab store' ],
                [ 'name' => 'var', 'description' => 'null' ],
            ],
        ];

        $config = [
            'name'   => 'tab',
            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function bitOptions()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => 'bitOptions',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function props()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => 'props',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function lang()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'var', 'description' => '\IPS\Lang' ],
            ],
        ];

        $config = [
            'name'   => 'lang',
            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    protected function constructor()
    {

        $methodDocBlock = new DocBlockGenerator( '_Forms constructor', \null, [
            new ParamTag( 'elements', 'array', 'array of form elements' ),
            new ParamTag( 'prefix', 'string|null', 'language prefix for the form elements' ),
            new ParamTag( 'config', 'array', 'extra settings for the form' ),
            //new ReturnTag(['dataType' => 'array']),
        ] );

        $body = <<<'eof'
$this->elements = $elements;
$this->obj = $config[ 'object' ] ?? \null;
$this->langPrefix = $config[ 'prefix' ] ?? \null;
$this->lang = Member::loggedIn()->language();
$this->form = $config[ 'form' ] ?? \null;
$this->suffix = $config[ 'suffix' ] ?? \true;
$this->bitOptions = $config[ 'bitOptions' ] ?? \null;
$this->props = $config[ 'props' ] ?? \null;

if ( $this->langPrefix === \null && $this->obj !== \null && property_exists( $this->obj, 'formLangPrefix' ) ) {
    $class = $this->obj;
    $this->langPrefix = $class::$formLangPrefix;
}

if ( !( $this->form instanceof Form ) ) {
    $this->form = new Form( $config[ 'id' ] ?? 'form', $config[ 'submitLang' ] ?? 'save', $config[ 'action' ] ?? \null, $config[ 'attributes' ] ?? [] );
}
$this->form->id = $config[ 'id' ] ?? 'js_form';

if ( isset( $config[ 'formClass' ] ) ) {
    $this->form->class = $config[ 'formClass' ];
}
eof;
        $this->generator->addUse( \IPS\Member::class );
        $this->generator->addUse( \IPS\Helpers\Form::class );
        $this->methods[] = MethodGenerator::fromArray( [
            'name'       => '__construct',
            'parameters' => [
                new ParameterGenerator( 'elements', 'array' ),
                new ParameterGenerator( 'prefix', 'string' ),
                new ParameterGenerator( 'config', 'array' ),
            ],
            'body'       => $body,
            'docblock'   => $methodDocBlock,
            'static'     => \false,
        ] );
    }

    protected function buildForm()
    {

        $methodDocBlock = new DocBlockGenerator( '', \null, [
            new ParamTag( 'elements', 'array', 'array of form elements' ),
            new ParamTag( 'prefix', 'string|null', 'language prefix for the form elements' ),
            new ParamTag( 'config', 'array', 'extra settings for the form' ),
            new ReturnTag( [ 'dataType' => 'Form' ] ),
        ] );

        $body = <<<'eof'
/**
* @var $class static
*/
$class = new static($elements, $prefix, $config);
return $class->build();
eof;

        $this->generator->addUse( \IPS\Member::class );
        $this->generator->addUse( \IPS\Helpers\Form::class );
        $this->methods[] = MethodGenerator::fromArray( [
            'name'       => 'buildForm',
            'parameters' => [
                new ParameterGenerator( 'elements', 'array' ),
                new ParameterGenerator( 'prefix', 'string', \null ),
                new ParameterGenerator( 'config', 'array', [] ),
            ],
            'body'       => $body,
            'docblock'   => $methodDocBlock,
            'static'     => \true,
        ] );
    }

    protected function build()
    {

        $methodDocBlock = new DocBlockGenerator( '', \null, [
            new ReturnTag( [ 'dataType' => 'Form' ] ),
        ] );

        $body = <<<'eof'
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

foreach ( $this->elements as $key => $el ) {
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

    if ( in_array( $type, $typesWName, \true ) ) {

        if ( empty( $key ) && !isset( $el[ 'name' ] ) ) {
            Log::log( 'Form Helper type requires a name!' );
            continue;
        }

        $skip = $el[ 'skip' ] ?? \false;
        if ( !$skip ) {
            $name = $this->langPrefix;
            $plain = $el[ 'name' ] ?? $key;
            $name .= $plain;
        } else {
            $name = $el[ 'name' ] ?? $key;
            $plain = $name;
        }
    }

    $this->extra( $el );

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
            $parse = $el[ 'parse' ] ?? \false;
            $id = $el[ 'id' ] ?? \null;
            if ( isset( $el[ 'sprintf' ] ) ) {
                $parse = \false;
                $id = \null;
                $sprintf = explode( ',', $el[ 'sprintf' ] );
                $name = $this->lang->addToStack( $name, \false, [ 'sprintf' => $sprintf ] );
            }

            $css = $el[ 'css' ] ?? '';

            $this->form->addMessage( $name, $css, $parse, $id );
            break;
        case 'helper':
            $customClass = $el[ 'customClass' ] ?? \false;

            if ( $customClass === \false ) {
                $class = $el[ 'class' ] ?? Text::class;
                if ( $class !== Text::class ) {
                    $class = static::$classMap[ $class ] ?? $class;
                    $class = '\\IPS\\Helpers\\Form\\' . $class;
                }
            } else {
                $class = $el[ 'customClass' ];
            }

            if ( !class_exists( $class, \true ) ) {
                Log::debug( 'invale form class ' . $class );
                continue 2;
            }

            $required = $el[ 'required' ] ?? \false;
            $options = $el[ 'options' ] ?? $el[ 'ops' ] ?? [];
            $validation = $el[ 'validation' ] ?? $el[ 'val' ] ?? \null;
            $prefix = $el[ 'prefix' ] ?? \null;
            $suffix = $el[ 'suffix' ] ?? \null;
            $id = $el[ 'id' ] ?? \null;
            $default = $el[ 'default' ] ?? $el[ 'def' ] ?? \null;

            if ( $id === \null && !isset( $el[ 'skip_id' ] ) ) {
                $id = 'js_' . $name;
            }

            if ( $default === \null ) {
                $obj = $this->obj;
                $props = $this->props;
                $prop = $plain;
                $prop2 = $this->langPrefix . $prop;

                if ( is_object( $this->obj ) && empty( $props ) ) {
                    $default = $obj->{$prop} ?? $obj->{$prop2} ?? \null;
                }

                if ( $default === \null && !empty( $props ) ) {
                    $default = $props[ $prop ] ?? $props[ $prop2 ] ?? \null;
                }
                if ( $default === \null && !empty( $this->bitOptions ) && is_object( $this->obj ) ) {
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
                            $options[ 'toggles' ][ $k ][] = 'js_' . $this->langPrefix . $v;
                        }
                    }
                }

                if ( isset( $options[ 'togglesOn' ] ) ) {
                    $toggles = $options[ 'togglesOn' ];
                    unset( $options[ 'togglesOn' ] );
                    foreach ( $toggles as $val ) {
                        $options[ 'togglesOn' ][] = 'js_' . $this->langPrefix . $val;
                    }
                }

                if ( isset( $options[ 'togglesOff' ] ) ) {
                    $toggles = $options[ 'togglesOff' ];
                    unset( $options[ 'togglesOff' ] );
                    foreach ( $toggles as $val ) {
                        $options[ 'togglesOff' ][] = 'js_' . $this->langPrefix . $val;
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

            $element->appearRequried = $el[ 'appearRequired' ] ?? $el[ 'ap' ] ?? \false;

            if ( isset( $el[ 'label' ] ) ) {
                $prefix = $this->langPrefix;
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
                    if ( isset( $el[ 'desc_sprintf' ] ) ) {
                        $sprintf = $el[ 'desc_sprintf' ];
                        $sprintf = (array) $sprintf;
                        $desc = $this->lang->addToStack( $desc, \false, [ 'sprintf' => $sprintf ] );
                    } else {
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
                } else {
                    $desc = $el[ 'desc' ];
                }
            }

            $warning = '';

            if ( isset( $el[ 'warning' ] ) ) {
                if ( $this->lang->checkKeyExists( $el[ 'warning' ] ) ) {
                    $warning = $this->lang->addToStack( $el[ 'warning' ] );
                } else {
                    $warning = $el[ 'warning' ];
                }
            }

            if ( isset( $el[ 'id' ] ) ) {
                $id = $el[ 'id' ];
            } else {
                $id = $name . '_js';
            }

            $this->form->addDummy( $name, $default, $desc, $warning, $id );
            break;
        case 'html':
            if ( !isset( $el[ 'html' ] ) ) {
                continue 2;
            }
            $this->form->addHtml( $el[ 'html' ] );
            break;
        case 'matrix':
            if ( isset( $el[ 'matrix' ] ) && !( $el[ 'matrix' ] instanceof Matrix ) ) {
                continue 2;
            }

            $this->form->addMatrix( $name, $el[ 'matrix' ] );
            break;
        case 'hidden':
            $this->form->hiddenValues[ $name ] = $el[ 'default' ];
            break;
    }
}

return $this->form;
eof;

        $this->methods[] = MethodGenerator::fromArray( [
            'name'     => 'build',
            'body'     => $body,
            'docblock' => $methodDocBlock,
            'static'   => \false,
        ] );
    }

    protected function extra()
    {

        $methodDocBlock = new DocBlockGenerator( '', \null, [
            new ParamTag( 'el', 'array' ),
        ] );

        $body = <<<'eof'
$suffix = $this->suffix ? '_tab' : '';

if (isset($el[ 'tab' ])) {
    $tab = $this->langPrefix . $el[ 'tab' ] . $suffix;
    $this->tab = $tab;
    $this->form->addTab($tab);
    unset($el[ 'tab' ]);
}

$suffix = $this->suffix ? '_header' : '';
if (isset($el[ 'header' ]) && $this->header !== $this->langPrefix . $el[ 'header' ] . $suffix) {
    $header = $this->langPrefix . $el[ 'header' ] . $suffix;
    $this->header = $header;
    $this->form->addHeader($header);
    unset($el[ 'header' ]);
}

if (isset($el[ 'sidebar' ])) {
    $sideBar = $this->langPrefix . $el[ 'sidebar' ] . '_sidebar';
    if ($this->lang->checkKeyExists($sideBar)) {
        $sideBar = $this->lang->addToStack($sideBar);
    }

    $this->form->addSidebar($sideBar);
    unset($el[ 'sidebar' ]);
}
eof;

        $this->methods[] = MethodGenerator::fromArray( [
            'name'       => 'extra',
            'parameters' => [
                new ParameterGenerator( 'el', 'array' ),
            ],
            'body'       => $body,
            'docblock'   => $methodDocBlock,
            'static'     => \false,
        ] );
    }
}
