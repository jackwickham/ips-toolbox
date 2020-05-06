<?php

namespace Generator\Builders\Traits;

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
            $this->output( "\n\n" );
            if ( $method[ 'document' ] && is_array( $method[ 'document' ] ) ) {
                $this->output( $this->tab . "/**\n" );
                $last = false;
                $returned = false;

                foreach ( $method[ 'document' ] as $item ) {
                    if ( mb_strpos( $item, '@return' ) === 0 ) {
                        $this->output( "{$this->tab}*\n" );
                        $returned = true;
                    }
                    $this->output( "{$this->tab}* {$item}\n" );

                    if ( $returned === false && mb_strpos( $item, '@' ) === false ) {
                        $this->output( "{$this->tab}*\n" );
                    }
                }
                $this->output( "{$this->tab}*/\n" );

            }

            $final = null;
            $static = null;
            $abstract = null;

            if ( isset( $method[ 'abstract' ] ) && $method[ 'abstract' ] === true ) {
                $abstract = 'abstract ';
            }
            if ( isset( $method[ 'final' ] ) && $method[ 'final' ] === true ) {
                $final = 'final ';
            }

            if ( isset( $method[ 'static' ] ) && $method[ 'static' ] === true ) {
                $static = ' static';
            }

            $visibility = $method[ 'visibility' ];

            if ( $visibility === T_PUBLIC ) {
                $visibility = 'public';
            }
            else if ( $visibility === T_PROTECTED ) {
                $visibility = 'protected';
            }
            else if ( $visibility === T_PRIVATE ) {
                $visibility = 'private';
            }

            $this->output( $this->tab . $abstract . $final . $visibility . $static . ' function ' . $name . '(' );

            if ( empty( $method[ 'params' ] ) !== true && is_array( $method[ 'params' ] ) ) {
                $this->writeParams( $method[ 'params' ] );
            }

            $this->output( ')' );

            if ( isset( $method[ 'returnType' ] ) && $method[ 'returnType' ] ) {
                $this->output( ': ' . $method[ 'returnType' ] );
            }

            $body = $this->replaceMethods[ $name ] ?? trim( $method[ 'body' ] );
            if ( $abstract === null ) {
                $wrap = false;
                if ( mb_strpos( $body, '{' ) !== 0 ) {
                    $wrap = true;
                }

                $this->output( "{\n\n{$this->tab}{$this->tab}" );
                $this->output( '' . $body . '' );
                $this->output( "\n{$this->tab}}" );
            }
            else {
                $this->output( ";" );
            }
            if ( isset( $this->afterMethod[ $name ] ) ) {
                $this->output( "\n" );

                foreach ( $this->afterMethod[ $name ] as $after ) {
                    $this->output( $this->tab . $this->tab2space( $after ) . "\n" );
                }
            }
        }

    }

    protected function writeParams( array $params ): void
    {

        $this->output( ' ' );
        $built = [];

        foreach ( $params as $param ) {
            if ( !isset( $param[ 'name' ] ) ) {
                continue;
            }
            $p = '';
            if ( isset( $param[ 'hint' ] ) && $param[ 'hint' ] ) {
                if ( isset( $param[ 'nullable' ] ) && $param[ 'nullable' ] === true ) {
                    $p .= '?';
                }

                $hint = $param[ 'hint' ];
                if ( method_exists( $this, 'addImport' ) ) {
                    $hint = $this->addImport( $hint );
                }

                $p .= $hint . ' ';
            }

            if ( isset( $param[ 'reference' ] ) && $param[ 'reference' ] === true ) {
                $p .= '&';
            }

            $p .= '$' . $param[ 'name' ];

            if ( array_key_exists( 'value', $param ) ) {
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
                else if ( $param[ 'value' ] === "''" || $param === '""' ) {
                    $val = $param[ 'value' ];
                }
                else if ( is_string( $param[ 'value' ] ) ) {

                    $val = empty($param[ 'value' ]) ? "''" : $param['value'];
                }
                else {
                    $val = empty($param[ 'value' ]) ? "''" : $param['value'];
                }
                $p .= ' = ' . $val;
            }
            $built[] = $p;

        }
        $this->output( implode( ', ', $built ) );
        $this->output( ' ' );
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
            'abstract'   => $extra[ 'abstract' ] ?? false,
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
