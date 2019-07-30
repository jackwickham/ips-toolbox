<?php

namespace IPS\toolbox\Generator\Builders\Traits;

trait ClassMethods
{

    /**
     * an array of class method's
     *
     * @var array
     */
    protected $methods = [];

    public function writeMethods()
    {

        foreach ( $this->methods as $name => $method ) {
            if ( isset( $this->removeMethods[ $name ] ) ) {
                continue;
            }

            $this->toWrite .= "\n{$this->tab}";
            if ( $method[ 'document' ] && is_array( $method[ 'document' ] ) ) {
                $this->toWrite .= "\n";
                $this->toWrite .= $this->tab . "/**\n";
                foreach ( $method[ 'document' ] as $item ) {
                    $this->toWrite .= "{$this->tab}* {$item}\n";
                }
                $this->toWrite .= "{$this->tab}*/\n{$this->tab}";

            }

            $final = null;
            $static = null;

            if ( isset( $method[ 'final' ] ) && $method[ 'final' ] === true ) {
                $final = 'final ';
            }

            if ( isset( $method[ 'static' ] ) && $method[ 'static' ] === true ) {
                $static = ' static';
            }

            $this->toWrite .= $final . $method[ 'visibility' ] . $static . ' function ' . $name . '( ';

            if ( empty( $method[ 'params' ] ) !== true && is_array( $method[ 'params' ] ) ) {
                $built = [];

                foreach ( $method[ 'params' ] as $param ) {
                    if ( !isset( $param[ 'name' ] ) ) {
                        continue;
                    }
                    $p = '';
                    if ( isset( $param[ 'hint' ] ) && $param[ 'hint' ] ) {
                        $p .= $param[ 'hint' ] . ' ';
                    }

                    $p .= '$' . $param[ 'name' ];

                    if ( isset( $param[ 'value' ] ) ) {
                        $val = '';
                        if ( $param[ 'value' ] === '[]' || $param[ 'value' ] === 'array()' || is_array( $param[ 'value' ] ) ) {
                            $val = '[]';
                        }
                        else if ( mb_strtolower( $param[ 'value' ] ) === 'true' || mb_strtolower( $param[ 'value' ] ) === 'false' ) {
                            $val = mb_strtolower( $param[ 'value' ] );
                        }
                        else if ( $param[ 'value' ] === false ) {
                            $val = 'false';
                        }
                        else if ( $param[ 'value' ] === true ) {
                            $val = 'true';
                        }
                        else if ( $param[ 'value' ] === null || mb_strtolower( $param[ 'value' ] ) === 'null' ) {
                            $val = 'null';
                        }
                        else if ( is_string( $param[ 'value' ] ) ) {
                            $val = "'" . $param[ 'value' ] . "'";
                        }
                        else {
                            $val = $param[ 'value' ];
                        }
                        $p .= ' = ' . $val;
                    }
                    $built[] = $p;

                }
                $this->toWrite .= implode( ', ', $built );
            }
            $this->toWrite .= ' )';

            if ( isset( $method[ 'returnType' ] ) && $method[ 'returnType' ] ) {
                $this->toWrite .= ': ' . $method[ 'returnType' ];
            }

            $this->toWrite .= "\n{$this->tab}";

            $body = $this->replaceMethods[ $name ] ?? trim( $method[ 'body' ] );
            $wrap = false;
            if ( mb_substr( $body, 0, 1 ) !== '{' ) {
                $wrap = true;
            }

            if ( $wrap === true ) {
                $this->toWrite .= "{\n{$this->tab}{$this->tab}";
            }
            $this->toWrite .= '' . $body . '';
            if ( $wrap === true ) {
                $this->toWrite .= "\n{$this->tab}}\n";
            }
            else {
                $this->toWrite .= "\n";
            }
        }

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
            'static'     => $extra[ 'static' ] ?? false,
            'visibility' => $extra[ 'visibility' ] ?? 'public',
            'final'      => $extra[ 'final' ] ?? false,
            'document'   => $extra[ 'document' ] ?? null,
            'params'     => $params,
            'returnType' => $extra[ 'returnType' ] ?? '',
            'body'       => $body,
        ];
    }

    public function getMethods()
    {

        return $this->methods;
    }

    public function getMethod( $name )
    {

        return $this->methods[ $name ] ?? null;
    }

    public function addMixin( $class )
    {

        $og = explode( '\\', $class );
        if ( $this->doImports === true && \count( $og ) >= 2 ) {
            $this->addImport( $class );
            $class = array_pop( $og );
        }
        $this->classComment[] = '@mixin ' . $class;
    }
}
