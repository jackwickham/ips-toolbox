<?php

namespace Generator\Tokenizers;

class Tokenizer
{

    protected $file;

    protected $tokens;

    protected $content;

    protected $namespace;

    protected $classname;

    protected $extends;

    protected $interfaces;

    protected $properties;

    protected $methods;

    protected $classAbstract;

    protected $abstract = false;

    public function __construct( string $file )
    {

        $this->file = $file;

        $this->compile();
    }

    protected function compile()
    {

        $content = file_get_contents( $this->file );
        $tokens = token_get_all( $content );
        $type = '';
        $static = false;
        $method = false;
        $after = false;
        $abstract = false;
        $max = \count( $tokens );
        $body = false;
        $document = null;
        for ( $id = 0; $id <= $max; $id++ ) {
            if ( isset( $tokens[ $id ] ) ) {
                $token = $tokens[ $id ][ 0 ] ?? $tokens[ $id ];
                $i = $id + 2;

                switch ( $token ) {
                    case T_NAMESPACE:
                        for ( $x = $i; $x <= $max; $x++ ) {
                            if ( !isset( $tokens[ $x ] ) ) {
                                break;
                            }
                            $t = $tokens[ $x ];
                            if ( !\is_array( $t ) && $t === ';' ) {
                                break;
                            }
                            $t = trim( $t[ 1 ] );
                            if ( empty( $t ) !== true ) {
                                $this->namespace[] = $t;
                            }
                        }
                        break;
                    case T_ABSTRACT:
                        $abstract = true;
                        break;
                    case T_CLASS:
                        if ( $after === false ) {
                            for ( $x = $i; $x <= $max; $x++ ) {
                                if ( !isset( $tokens[ $x ] ) ) {
                                    break;
                                }
                                $t = $tokens[ $x ];
                                if ( ( is_array( $t ) && ( $t[ 0 ] === T_EXTENDS || $t[ 0 ] === T_IMPLEMENTS ) ) || ( !is_array( $t ) && ( $t === '{' ) ) ) {
                                    break;
                                }
                                $t = trim( $t[ 1 ] ?? $t );
                                if ( empty( $t ) !== true ) {
                                    $this->classname .= $t;
                                }
                            }
                            $this->classAbstract = $abstract;
                            $abstract = false;
                            $after = true;
                        }
                        break;
                    case T_EXTENDS:
                        for ( $x = $i; $x <= $max; $x++ ) {
                            if ( !isset( $tokens[ $x ] ) ) {
                                break;
                            }
                            $t = $tokens[ $x ];
                            if ( ( is_array( $t ) && ( $t[ 0 ] === T_CURLY_OPEN || $t[ 0 ] === T_IMPLEMENTS ) ) || ( !is_array( $t ) && ( $t === '{' ) ) ) {
                                break;
                            }
                            $t = trim( $t[ 1 ] ?? $t );
                            if ( empty( $t ) !== true ) {
                                $this->extends .= $t;
                            }
                        }
                        $after = true;
                        break;
                    case T_IMPLEMENTS:
                        $interfaces = [];
                        for ( $x = $i; $x <= $max; $x++ ) {
                            if ( !isset( $tokens[ $x ] ) ) {
                                break;
                            }
                            $t = $tokens[ $x ];
                            if ( ( is_array( $t ) && ( $t[ 0 ] === T_CURLY_OPEN ) ) || ( !is_array( $t ) && ( $t === '{' ) ) ) {
                                break;
                            }
                            $t = trim( $t[ 1 ] ?? $t );
                            if ( empty( $t ) !== true ) {
                                if ( $t === ',' ) {
                                    $this->interfaces[] = $interfaces;
                                    $interfaces = [];
                                }
                                else {
                                    $interfaces[] = $t;
                                }
                            }
                        }
                        $after = true;
                        break;
                    case T_STATIC:
                        $static = true;
                        break;
                    case T_PUBLIC:
                        $type = 'public';
                        break;
                    case T_PRIVATE:
                        $type = 'private';
                        break;
                    case T_PROTECTED:
                        $type = 'protected';
                        break;
                    case T_VARIABLE:
                        if ( $after === true ) {
                            $value = '';
                            $name = null;

                            for ( $x = $id; $x <= $max; $x++ ) {
                                if ( !isset( $tokens[ $x ] ) ) {
                                    break;
                                }

                                $t = trim( $tokens[ $x ][ 1 ] ?? $tokens[ $x ] );
                                if ( $t === '=' ) {
                                    $x++;
                                    for ( $xx = $x; $xx <= $max; $xx++ ) {
                                        if ( !isset( $tokens[ $xx ] ) ) {
                                            break;
                                        }
                                        $tt = trim( $tokens[ $xx ][ 1 ] ?? $tokens[ $xx ] );

                                        if ( $tt === ';' ) {
                                            $xx--;
                                            break;
                                        }

                                        $value = ltrim( rtrim( $tt, "'" ), "'" );

                                    }

                                }

                                if ( $t === ';' ) {
                                    if ( !isset( $this->properties[ $name ] ) ) {
                                        $this->properties[ $name ] = [
                                            'name'     => $name,
                                            'value'    => $value,
                                            'abstract' => $abstract,
                                            'type'     => $type,
                                            'static'   => $static,
                                        ];
                                    }
                                    break;
                                }
                                if ( $name === null ) {
                                    $name = $t;
                                }
                            }
                        }
                        break;
                    case T_DOC_COMMENT:
                        if ( $after === true ) {
                            $document = $tokens[ $id ][ 1 ] ?? $tokens[ $id ];
                        }
                        break;
                    case T_FUNCTION:
                        if ( $after === true ) {
                            $method = '';
                            for ( $x = $i; $x <= $max; $x++ ) {
                                if ( !isset( $tokens[ $x ] ) ) {
                                    break;
                                }
                                $t = $tokens[ $x ];
                                if ( ( !is_array( $t ) && ( $t === '(' ) ) ) {
                                    if ( trim( $method ) ) {
                                        $this->methods[ $method ] = [
                                            'name'       => $method,
                                            'type'       => $type,
                                            'static'     => $static,
                                            'abstract'   => $this->abstract,
                                            'returnType' => null,
                                            'document'   => $document,
                                        ];
                                    }
                                    $document = null;
                                    $type = false;
                                    $static = false;
                                    $abstract = false;
                                    break;
                                }

                                $t = trim( $t[ 1 ] ?? $t );
                                if ( empty( $t ) !== true ) {
                                    $method = $t;
                                }
                            }
                        }
                        break;
                }
            }
        }
    }

    public function getNameSpace()
    {

        return \is_array( $this->namespace ) ? implode( '', $this->namespace ) : $this->namespace;
    }

    public function getClassName()
    {

        return $this->classname;
    }

    public function isAbstract()
    {

        return $this->classAbstract;
    }

    public function getPropertyValue( $key )
    {

        return $this->properties[ '$' . $key ][ 'value' ] ?? null;
    }

    public function getMethods()
    {

        return $this->methods;
    }
}
