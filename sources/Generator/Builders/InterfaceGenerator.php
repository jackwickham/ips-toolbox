<?php

namespace IPS\toolbox\Generator\Builders;

use IPS\toolbox\Generator\Builders\Traits\Constants;
use IPS\toolbox\Generator\Builders\Traits\Properties;

/**
 * Class _ClassGenerator
 *
 * @package IPS\toolbox\Generator\Builders
 * @mixin InterfaceGenerator
 */
class _InterfaceGenerator extends GeneratorAbstract
{

    use Constants;

    /**
     * an array of class method's
     *
     * @var array
     */
    protected $methods = [];

    public function writeSourceType()
    {

        $this->toWrite .= "\ninterface {$this->className}";
        $this->toWrite .= "\n{";
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
        $this->writeConst();
        foreach ( $this->methods as $name => $method ) {
            if ( isset( $this->removeMethods[ $name ] ) ) {
                continue;
            }
            $this->toWrite .= "\n{$tab}";
            if ( $method[ 'document' ] && is_array( $method[ 'document' ] ) ) {
                $this->toWrite .= "\n";
                $this->toWrite .= $tab . "/**\n";
                foreach ( $method[ 'document' ] as $item ) {
                    $this->toWrite .= "{$tab}* {$item}\n";
                }
                $this->toWrite .= "{$tab}*/\n{$tab}";

            }

            $final = null;
            $static = null;

            if ( isset( $method[ 'final' ] ) && $method[ 'final' ] === true ) {
                $final = 'final ';
            }

            if ( isset( $method[ 'static' ] ) && $method[ 'static' ] === true ) {
                $static = ' static';
            }

            $this->toWrite .= $final . $method[ 'visibility' ] . $static . ' function ' . $name . '(';

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
                $this->toWrite .= implode( ', ', $built );
            }
            $this->toWrite .= ')';

            if ( isset( $method[ 'returnType' ] ) && $method[ 'returnType' ] ) {
                $this->toWrite .= ': ' . $method[ 'returnType' ];
            }

            $this->toWrite .= ';';

        }

    }
}
