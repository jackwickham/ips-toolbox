<?php

namespace Generator\Builders;

use Generator\Builders\Traits\Constants;
use Generator\Builders\Traits\Properties;

/**
 * Class _ClassGenerator
 *
 * @package  Builders
 */
class InterfaceGenerator extends GeneratorAbstract
{

    use Properties, Constants;

    public function writeSourceType()
    {

        $this->output( "\ninterface {$this->className}" );
        $this->output( "\n{" );
    }

    /**
     * @param        $name
     * @param string $body
     * @param array  $params
     * @param array  $extra
     *
     * @return $this
     */
    public function addMethod( $name, string $body, array $params = [], array $extra = [] )
    {

        $this->methods[ trim( $name ) ] = [
            'name'       => $name,
            'static'     => $extra[ 'static' ],
            'visibility' => $extra[ 'visibility' ],
            'final'      => $extra[ 'final' ],
            'document'   => $extra[ 'document' ],
            'params'     => $params,
            'returnType' => $extra[ 'returnType' ],
        ];
    }

    protected function writeBody()
    {

        $tab = $this->tab;

        foreach ( $this->methods as $name => $method ) {
            if ( isset( $this->removeMethods[ $name ] ) ) {
                continue;
            }
            $this->output( "\n{$tab}" );
            if ( $method[ 'document' ] && is_array( $method[ 'document' ] ) ) {
                $this->output( "\n" );
                $this->output( $tab . "/**\n" );
                foreach ( $method[ 'document' ] as $item ) {
                    $this->output( "{$tab}* {$item}\n" );
                }
                $this->output( "{$tab}*/\n{$tab}" );

            }

            $final = null;
            $static = null;

            if ( isset( $method[ 'final' ] ) && $method[ 'final' ] === true ) {
                $final = 'final ';
            }

            if ( isset( $method[ 'static' ] ) && $method[ 'static' ] === true ) {
                $static = ' static';
            }

            $this->output( $final . $method[ 'visibility' ] . $static . ' function ' . $name . '(' );

            if ( empty( $method[ 'params' ] ) !== true && is_array( $method[ 'params' ] ) ) {
                $built = [];

                foreach ( $method[ 'params' ] as $param ) {
                    if ( !isset( $param[ 'name' ] ) ) {
                        continue;
                    }
                    $p = ' ';
                    if ( isset( $param[ 'hint' ] ) ) {
                        $p .= $param[ 'hint' ] . ' ';
                    }

                    $p .= '$' . $param[ 'name' ];

                    if ( isset( $param[ 'value' ] ) ) {
                        $val = '';
                        if ( $param[ 'value' ] === '[]' ) {
                            $val = '[]';
                        }
                        else if ( $param[ 'value' ] === 'true' || $param[ 'value' ] === 'false' ) {
                            $val = $param[ 'value' ];
                        }
                        else if ( $param[ 'value' ] === 'null' ) {
                            $val = 'null';
                        }
                        else if ( is_string( $param[ 'value' ] ) ) {
                            $val = " '" . $param[ 'value' ] . "'";
                        }
                        $p .= ' = ' . $val;
                    }
                    $built[] = $p;

                }
                $this->output( implode( ', ', $built ) );
            }
            $this->output( ')' );

            if ( isset( $method[ 'returnType' ] ) && $method[ 'returnType' ] ) {
                $this->output( ': ' . $method[ 'returnType' ] );
            }

            $this->output( ';' );

        }

    }
}
