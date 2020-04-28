<?php

namespace Generator\Builders\Traits;

use Generator\Builders\ClassGenerator;

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
    public function addProperty( string $name, $value, array $extra = [] )
    {

        //        if ( $name === 'multitons' ) {
        //            _d( $value, empty( $value ), trim( ClassGenerator::convertValue( $value ) ) );
        //        }
        $this->properties[ $name ] = [
            'name'       => $name,
            'value'      => empty( $value ) !== true ? trim( ClassGenerator::convertValue( $value ) ) : null,
            'document'   => $extra[ 'document' ] ?? null,
            'static'     => $extra[ 'static' ] ?? false,
            'visibility' => $extra[ 'visibility' ] ?? T_PUBLIC,
            'type'       => $extra[ 'type' ] ?? 'string',
        ];

        //        if ( $name === 'beenPatched' ) {
        //            _p( $this->properties[ $name ] );
        //        }
    }

    public function removeProperty( string $name )
    {

        unset( $this->properties[ $name ] );
    }

    public function getProperties()
    {

        return $this->properties;
    }

    public function getPropertyValue( $property )
    {

        $property = $this->getProperty( $property );
        if ( $property !== null && isset( $property[ 'value' ] ) && $property[ 'value' ] !== null ) {
            $value = trim( $property[ 'value' ], '"' );
            $value = trim( $value, "'" );

            return $value;
        }

        return null;
    }

    public function getProperty( $name )
    {

        return $this->properties[ $name ] ?? null;
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

        $this->classComment[ $name ] = $doc;
    }

    public function getPropertyTag( $name )
    {

        return $this->classComment[ $name ] ?? null;
    }

    protected function writeProperties(): void
    {

        if ( empty( $this->properties ) !== true ) {
            foreach ( $this->properties as $property ) {
                $this->output( "\n{$this->tab}" );

                if ( $property[ 'document' ] ) {
                    $this->output( "/**\n" );
                    foreach ( $property[ 'document' ] as $item ) {
                        $this->output( "{$this->tab}* {$item}\n" );
                    }
                    $this->output( $this->tab . "*/\n{$this->tab}" );
                }
                $visibility = $property[ 'visibility' ];

                if ( $visibility === T_PUBLIC ) {
                    $visibility = 'public ';
                }
                else if ( $visibility === T_PROTECTED ) {
                    $visibility = 'protected ';
                }
                else if ( $visibility === T_PRIVATE ) {
                    $visibility = 'private ';
                }
                else if ( $visibility === null ) {
                    $visibility = 'public ';
                }
                $this->output( $visibility . ' ' );
                if ( isset( $property[ 'static' ] ) && $property[ 'static' ] ) {
                    $this->output( 'static ' );
                }
                $this->output( '$' . $property[ 'name' ] );

                if ( isset( $property[ 'value' ] ) && ( $property[ 'value' ] !== null && $property[ 'value' ] !== 'null' ) ) {
                    $pType = $property[ 'type' ];
                    if ( $pType !== 'string' ) {
                        $this->output( ' = ' . $property[ 'value' ] );
                    }
                    else {
                        $this->output( ' = ' . trim( ClassGenerator::convertValue( $property[ 'value' ] ) ) );
                    }
                }
                $this->output( ";\n" );
            }
        }
    }
}
