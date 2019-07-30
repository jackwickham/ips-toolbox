<?php

namespace IPS\toolbox\Generator\Builders\Traits;

trait Properties
{

    /**
     * an array of class properties
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @param string $name
     * @param        $value
     * @param array  $extra
     *
     * @return $this
     */
    public function addProperty( string $name, $value, array $extra )
    {

        $hash = $this->hash( $name );
        $this->properties[ $hash ] = [
            'name'       => $name,
            'value'      => $value,
            'document'   => $extra[ 'document' ] ?? null,
            'static'     => $extra[ 'static' ] ?? false,
            'visibility' => $extra[ 'visibility' ] ?? T_PUBLIC,
        ];
    }

    public function removeProperty( string $name )
    {

        $hash = $this->hash( $name );
        unset( $this->properties[ $hash ] );
    }

    public function getProperties()
    {

        return $this->properties;
    }

    public function getPropertyValue( $property )
    {

        $property = $this->getProperty( $property );
        if ( $property !== null && isset( $property[ 'value' ] ) && $property[ 'value' ] !== null ) {
            $value = <<<EOF
return {$property['value']};
EOF;

            return eval( $value );
        }

        return null;
    }

    public function getProperty( $name )
    {

        return $this->properties[ $this->hash( $name ) ] ?? null;
    }

    /**
     * @param                 $name
     * @param array           $extra
     */
    public function addPropertyTag( $name, array $extra = [] ): void
    {

        $doc = '';
        $type = $extra[ 'type' ] ?? null;
        if ( $type === 'write' ) {
            $doc .= '@property-write';
        }
        else if ( $type === 'read' ) {
            $doc .= '@property-read';
        }
        else {
            $doc .= '@property';
        }

        if ( isset( $extra[ 'hint' ] ) && $extra[ 'hint' ] ) {
            $doc .= ' ' . $extra[ 'hint' ];
        }

        $doc .= ' $' . $name;

        if ( isset( $extra[ 'comment' ] ) && $extra[ 'comment' ] ) {
            $doc .= ' ' . $extra[ 'comment' ];
        }

        $this->classComment[ $this->hash( $name ) ] = $doc;
    }

    public function getPropertyTag( $name )
    {

        return $this->classComment[ $this->hash( $name ) ] ?? null;
    }

    protected function writeProperties(): void
    {

        if ( empty( $this->properties ) !== true ) {
            foreach ( $this->properties as $property ) {
                $this->toWrite .= "\n{$this->tab}";

                if ( $property[ 'document' ] ) {
                    $this->toWrite .= "/**\n";
                    foreach ( $property[ 'document' ] as $item ) {
                        $this->toWrite .= "{$this->tab}* {$item}\n";
                    }
                    $this->toWrite .= $this->tab . "*/\n{$this->tab}";
                }

                $this->toWrite .= $property[ 'visibility' ] . ' ';
                if ( isset( $property[ 'static' ] ) && $property[ 'static' ] ) {
                    $this->toWrite .= 'static ';
                }
                $this->toWrite .= ' $' . $property[ 'name' ];
                if ( $property[ 'value' ] ) {
                    $this->toWrite .= ' = ' . trim( $property[ 'value' ] );
                }
                $this->toWrite .= ";\n";
            }
        }
    }
}
