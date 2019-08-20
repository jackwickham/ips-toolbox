<?php

namespace IPS\toolbox\Form;

use InvalidArgumentException;
use IPS\Helpers\Form\FormAbstract;
use function array_merge;
use function array_pop;
use function explode;
use function is_array;
use function mb_strtolower;
use function property_exists;

/**
 * Class _FormAbstract
 *
 * @package Forms
 * @mixin  Element
 * @property-read string                   $name
 * @property-read string                   $type
 * @property-read string|int|array         $value
 * @property-read bool                     $required
 * @property-read array                    $options
 * @property-read callable                 $validationCallback
 * @property-read string                   $prefix
 * @property-read string                   $suffix
 * @property-read string                   $id
 * @property-read string                   $tab
 * @property-read bool                     $skip
 * @property-read string                   $header
 * @property-read bool                     $appearRequired
 * @property-read array                    $toggles
 * @property-read array                    $label
 * @property-read array                    $description
 * @property-read array                    $extra
 * @property-read string                   $sidebar
 * @property-read FormAbstract|string|null $class
 * @property-read bool                     $custom
 *
 */
class _Element
{

    /**
     * @var array
     */
    public static $helpers = [
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
        'member'       => 'Member',
        'node'         => 'Node',
        'number'       => 'Number',
        'num'          => 'Number',
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

    public static $nonHelpers = [
        'sidebar'   => 1,
        'header'    => 1,
        'separator' => 1,
        'message'   => 1,
        'tab'       => 1,
        'dummy'     => 1,
        'html'      => 1,
        'hidden'    => 1,
        'custom'    => 1,
    ];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|int|array
     */
    protected $value;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var callable
     */
    protected $validationCallback;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $suffix;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $tab;

    /**
     * @var bool
     */
    protected $skip = false;

    /**
     * @var string
     */
    protected $header;

    /**
     * @var bool
     */
    protected $appearRequired;

    /**
     * @var array
     */
    protected $label;

    /**
     * @var array
     */
    protected $description;

    /**
     * @var array
     */
    protected $toggles = [];

    /**
     * @var array
     */
    protected $extra = [];

    /**
     * @var string
     */
    protected $sidebar;

    /**
     * @var FormAbstract|string|null
     */
    protected $class;

    /**
     * @var bool
     */
    protected $custom = false;

    /**
     * @var null|string
     */
    protected $empty;

    /**
     * FormAbstract constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct( string $name, string $type, string $custom = '' )
    {

        $class = null;
        $type = mb_strtolower( $type );
        if ( !isset( static::$nonHelpers[ $type ] ) ) {
            if ( !( $name instanceof FormAbstract ) && isset( static::$helpers[ $type ] ) ) {
                $class = '\\IPS\\Helpers\\Form\\' . static::$helpers[ $type ] ?? 'Text';
                $type = 'helper';
            }
            else if ( $name instanceof FormAbstract ) {
                $class = $name;
                $type = 'helper';
            }
        }
        else if ( $type === 'custom' ) {
            $class = $custom;
            $type = 'helper';
            $this->custom = true;
        }

        $this->name = $name;
        $this->type = $type;
        $this->class = $class;
    }

    public function __get( $name )
    {

        if ( property_exists( $this, $name ) ) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * @param $value
     *
     * @return Element
     */
    public function value( $value ): self
    {

        $this->value = $value;

        return $this;
    }

    /**
     * @return self
     */
    public function required(): self
    {

        $this->required = true;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function options( array $options ): self
    {

        if ( isset( $options[ 'toggles' ], $options[ 'togglesOff' ], $options[ 'togglesOn' ] ) ) {
            throw new InvalidArgumentException( 'Your options array contains toggles/togglesOn/togglesOff, use the toggles() method instead' );
        }
        $this->options = array_merge( $this->options, $options );

        return $this;
    }

    public function disabled( bool $disabled )
    {

        $this->options = array_merge( $this->options, [ 'disabled' => $disabled ] );

        return $this;
    }

    /**
     * @param $validation
     *
     * @return self
     */
    public function validation( callable $validation ): self
    {

        $this->validationCallback = $validation;

        return $this;
    }

    /**
     * @param $prefix
     *
     * @return self
     */
    public function prefix( string $prefix ): self
    {

        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param $suffix
     *
     * @return self
     */
    public function suffix( string $suffix ): self
    {

        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @param $id
     *
     * @return self
     */
    public function id( string $id ): self
    {

        $this->id = $id;

        return $this;
    }

    /**
     * @param $tab
     *
     * @return self
     */
    public function tab( string $tab ): self
    {

        $this->tab = $tab;

        return $this;
    }

    /**
     * @return self
     */
    public function skip(): self
    {

        $this->skip = true;

        return $this;
    }

    /**
     * @param $header
     *
     * @return self
     */
    public function header( string $header ): self
    {

        $this->header = $header;

        return $this;
    }

    /**
     * @param bool $off
     *
     * @return self
     */
    public function appearRequired( bool $off = false ): self
    {

        $this->appearRequired = $off ? false : true;

        return $this;
    }

    /**
     * @param string $label
     *
     * @param array  $sprintf
     *
     * @return self
     */
    public function label( string $label, array $sprintf = [] ): self
    {

        $this->label = [
            'key'     => $label,
            'sprintf' => $sprintf,
        ];

        return $this;
    }

    /**
     * @param string $description
     *
     * @param array  $sprintf
     *
     * @return self
     */
    public function description( string $description, array $sprintf = [] ): self
    {

        $this->description = [
            'key'     => $description,
            'sprintf' => $sprintf,
        ];

        return $this;
    }

    /**
     * @param array $toggles
     * @param bool  $off
     * @param bool  $na
     *
     * @return self
     */
    public function toggles( array $toggles, bool $off = false, bool $na = false ): self
    {

        $key = 'togglesOff';
        if ( $off === false ) {
            $key = 'toggles';
            $togglesOn = [
                'Checkbox' => 1,
                'YesNo'    => 1,
            ];

            $class = explode( '\\', $this->class );
            $class = is_array( $class ) ? array_pop( $class ) : null;
            if ( isset( $togglesOn[ $class ] ) ) {
                $key = 'togglesOn';
            }
        }

        if ( $na === true ) {
            $key = 'na' . $key;
        }

        $this->toggles[] = [
            'key'      => $key,
            'elements' => $toggles,
        ];

        return $this;
    }

    /**
     * @param array $extra
     *
     * @return self
     */
    public function extra( array $extra ): self
    {

        $this->extra = $extra;

        return $this;
    }

    /**
     * @param string $sidebar
     *
     * @return self
     */
    public function sidebar( string $sidebar ): self
    {

        $this->sidebar = $sidebar;

        return $this;
    }

    /**
     * @param $empty
     *
     * @return self
     */
    public function empty( $empty ): self
    {

        $this->empty = $empty;

        return $this;
    }
}
