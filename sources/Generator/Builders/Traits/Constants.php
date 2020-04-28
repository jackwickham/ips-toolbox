<?php

namespace Generator\Builders\Traits;

trait Constants
{

    /**
     * an array of class constants
     *
     * @var array
     */
    protected $const = [];

    public function addConst( string $name, $value, array $extra = [] )
    {

        $this->const[ $this->hash( $name ) ] = [
            'name'       => $name,
            'value'      => $value,
            'document'   => $extra[ 'document' ] ?? null,
            'visibility' => $extra[ 'visibility' ] ?? null,
        ];
    }

    public function getConstants()
    {

        return $this->const;
    }

    public function getConstant( $name )
    {

        return $this->const[ $this->hash( $name ) ] ?? null;
    }

    protected function writeConst()
    {

        if ( empty( $this->const ) !== true ) {
            foreach ( $this->const as $const ) {
                $this->output( "\n{$this->tab}" );

                if ( $const[ 'document' ] ) {
                    $this->output( "/**\n" );
                    foreach ( $const[ 'document' ] as $item ) {
                        $this->output( "{$this->tab}* {$item}\n" );
                    }
                    $this->output( $this->tab . "*/\n{$this->tab}" );
                }

                if ( $const[ 'visibility' ] !== null ) {
                    $this->output( $const[ 'visibility' ] . ' ' );
                }

                $this->output( 'CONST ' );

                $this->output( $const[ 'name' ] );
                if ( $const[ 'value' ] ) {
                    $this->output( ' = ' . trim( $const[ 'value' ] ) );
                }
                $this->output( ";\n" );
            }
        }
    }
}
