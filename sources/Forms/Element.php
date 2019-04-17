<?php

namespace IPS\toolbox\Forms;

/**
 * Class _FormAbstract
 *
 * @package IPS\toolbox\Forms
 * @mixin \IPS\toolbox\Forms\Element
 */
class _Element
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $type;

    /**
     * FormAbstract constructor.
     *
     * @param string $name
     * @param Form   $form
     */
    public function __construct( string $name, string $type, Form $form )
    {
        $this->name = $name;
        $this->type = $type;
        $this->form = $form;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function value( $value ): self
    {
        $this->form->updateElement( $this->name, 'default', $value );
        return $this;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function required( bool $required ): self
    {
        $this->form->updateElement( $this->name, 'require', $required );
        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function options( array $options ): self
    {
        $this->form->updateElement( $this->name, 'options', $options );
        return $this;
    }

    /**
     * @param $validation
     *
     * @return $this
     */
    public function validation( $validation ): self
    {
        $this->form->updateElement( $this->name, 'validation', $validation );
        return $this;
    }

    /**
     * @param $prefix
     *
     * @return $this
     */
    public function prefix( $prefix ): self
    {
        $this->form->updateElement( $this->name, 'prefix', $prefix );
        return $this;
    }

    /**
     * @param $suffix
     *
     * @return $this
     */
    public function suffix( $suffix ): self
    {
        $this->form->updateElement( $this->name, 'suffix', $suffix );
        return $this;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function id( $id ): self
    {
        $this->form->updateElement( $this->name, 'id', $id );
        return $this;
    }

    /**
     * @param $tab
     *
     * @return $this
     */
    public function tab( $tab ): self
    {
        $this->form->updateElement( $this->name, 'tab', $tab );
        return $this;
    }

    /**
     * @param $header
     *
     * @return $this
     */
    public function header( $header ): self
    {
        $this->form->updateElement( $this->name, 'header', $header );
        return $this;
    }

    /**
     * @param $ap
     *
     * @return $this
     */
    public function appearRequired( $ap ): self
    {
        $this->form->updateElement( $this->name, 'appearRequired', $ap );
        return $this;
    }

    /**
     * @param $label
     *
     * @return $this
     */
    public function label( $label ): self
    {
        $this->form->updateElement( $this->name, 'label', $label );
        return $this;
    }

    /**
     * @param $description
     *
     * @return $this
     */
    public function description( $description ): self
    {
        $this->form->updateElement( $this->name, 'description', $description );
        return $this;
    }

    /**
     * @param $sprintf
     *
     * @return $this
     */
    public function descriptionSprintf( $sprintf ): self
    {
        $this->form->updateElement( $this->name, 'descriptionSprintf', $sprintf );
        return $this;
    }


    /**
     * @param array $toggles
     * @param bool  $off
     * @param bool  $na
     *
     * @return $this
     */
    public function toggles( array $toggles, bool $off = false, bool $na = false ): self
    {
        $key = 'togglesOff';
        if ( $off === true ) {
            $key = 'toggles';
            $togglesOn = [
                'Checkbox' => 1,
                'YesNo'    => 1,
            ];

            if ( isset( $togglesOn[ $this->type ] ) ) {
                $key = 'togglesOn';
            }
        }

        if ( $na === true ) {
            $key = 'na' . $key;
        }

        $this->form->updateElement( $this->name, $key, $toggles );
        return $this;
    }
}
