<?php

/**
 * @brief       NewForm Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\DevCenter\Sources\Generator;


use IPS\Helpers\Form;
use IPS\Lang;
use IPS\Member;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyValueGenerator;

class _NewForm extends GeneratorAbstract
{
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

    public function bodyGenerator()
    {
        $this->brief = 'Class';
        $this->props();
        $this->constructor();
    }

    protected function props()
    {
        $config = [
            'name'   => 'classMap',
            'value'  => new PropertyValueGenerator( static::$classMap, PropertyValueGenerator::TYPE_ARRAY, PropertyValueGenerator::OUTPUT_MULTIPLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'array' ],
                ],
            ],
            'static' => \true,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'form',
            'value'  => new PropertyValueGenerator( null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'Form' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'id',
            'value'  => new PropertyValueGenerator( 'defaul', PropertyValueGenerator::TYPE_STRING, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'elementStore',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY_SHORT, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'object',
            'value'  => new PropertyValueGenerator( null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'object' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'bitOptions',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY_SHORT, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'array' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'formPrefix',
            'value'  => new PropertyValueGenerator( null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'lang',
            'value'  => new PropertyValueGenerator( null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => Lang::class ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );

        $config = [
            'name'   => 'header',
            'value'  => new PropertyValueGenerator( null, PropertyValueGenerator::TYPE_NULL, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => [
                'tags' => [
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ],
            'static' => \false,
        ];
        $this->addProperty( $config );
    }

    protected function constructor()
    {
        $methodDocBlock = new DocBlockGenerator( '_Forms constructor', \null, [
            new ParamTag( 'form', 'Form|null', '' ),
            //new ReturnTag(['dataType' => 'array']),
        ] );

        $body = <<<'eof'
$this->lang = Member::loggedIn()->language();
if ( $form === null ) {
    $this->form = new \IPS\Helpers\Form();
}
else if ( $form instanceof \IPS\Helpers\Form ) {
    $this->form = $form;
}
eof;
        $this->generator->addUse( Member::class );
        $this->generator->addUse( Form::class );
        $this->methods[] = MethodGenerator::fromArray( [
            'name'       => '__construct',
            'parameters' => [
                new ParameterGenerator( 'form', 'Form', 'null' ),
            ],
            'body'       => $body,
            'docblock'   => $methodDocBlock,
            'static'     => \false,
        ] );
    }
}
