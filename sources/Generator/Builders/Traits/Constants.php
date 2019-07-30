<?php

namespace IPS\toolbox\Generator\Builders\Traits;

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
                $this->toWrite .= "\n{$this->tab}";

                if ( $const[ 'document' ] ) {
                    $this->toWrite .= "/**\n";
                    foreach ( $const[ 'document' ] as $item ) {
                        $this->toWrite .= "{$this->tab}* {$item}\n";
                    }
                    $this->toWrite .= $this->tab . "*/\n{$this->tab}";
                }

                if ( $const[ 'visibility' ] !== null ) {
                    $this->toWrite .= $const[ 'visibility' ] . ' ';
                }

                $this->toWrite .= 'CONST ';

                $this->toWrite .= $const[ 'name' ];
                if ( $const[ 'value' ] ) {
                    $this->toWrite .= ' = ' . trim( $const[ 'value' ] );
                }
                $this->toWrite .= ";\n";
            }
        }
    }
}
