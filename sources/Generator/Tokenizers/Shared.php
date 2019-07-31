<?php

namespace IPS\toolbox\Generator\Tokenizers;

use InvalidArgumentException;
use IPS\Log;
use function count;
use function file_exists;
use function file_get_contents;
use function in_array;
use function token_get_all;
use const IPS\ROOT_PATH;

trait Shared
{

    public $doMethods = true;

    protected $compiled = false;

    public function __construct( $path = null )
    {

        parent::__construct( $path );
        if ( $path !== null ) {
            $this->compile();
        }
    }

    /**
     * @param $path
     *
     * @return $this
     */
    protected function compile()
    {

        if ( file_exists( $this->path ) ) {
            $this->compiled = true;
            $source = file_get_contents( $this->path );
            $tokens = token_get_all( $source );
            $count = count( $tokens );
            $beforeClass = true;
            $beforeNamespace = true;
            $document = null;
            $visibility = null;
            $static = null;
            $final = false;
            $propName = null;
            $insideMethod = false;
            $type = null;
            for ( $i = 0; $i < $count; $i++ ) {
                $token = $tokens[ $i ][ 0 ] ?? $tokens[ $i ];
                $value = $tokens[ $i ][ 1 ] ?? $tokens[ $i ];
                $start = $tokens[ $i ][ 2 ] ?? $tokens[ $i ];
                switch ( $token ) {
                    case T_CONST:
                        if ( $beforeClass === false && $insideMethod === false ) {
                            $constName = null;
                            $constVal = null;
                            $i++;
                            $first = true;
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                                $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                                if ( $value2 === '=' ) {
                                    continue;
                                }
                                if ( $value2 === ';' ) {
                                    break;
                                }
                                if ( $first === true && $token2 === T_STRING ) {
                                    $constName = trim( $value2 );
                                    $first = false;
                                }
                                else {
                                    $constVal .= $value2;
                                }
                                $i++;
                            }
                            $vis = null;
                            if ( $visibility === T_PRIVATE ) {
                                $vis = 'private';
                            }
                            else if ( $visibility === T_PROTECTED ) {
                                $vis = 'protected';
                            }
                            else if ( $visibility === T_PUBLIC ) {
                                $vis = 'public';
                            }
                            $extra = [
                                'document'   => $document,
                                'visibility' => $vis,
                            ];
                            $this->addConst( $constName, trim( $constVal ), $extra );
                            $visibility = null;
                            $document = null;
                        }
                        break;
                    case T_REQUIRE:
                    case T_REQUIRE_ONCE:
                    case T_INCLUDE:
                    case T_INCLUDE_ONCE:
                        if ( $beforeClass === true ) {
                            $once = false;
                            if ( $token === T_REQUIRE_ONCE || $token === T_INCLUDE_ONCE ) {
                                $once = true;
                            }
                            $require = [];
                            $i++;
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];

                                if ( $value2 === ';' ) {
                                    break;
                                }
                                $require[] = $value2;
                                $i++;
                            }

                            if ( $token === T_REQUIRE_ONCE || $token === T_REQUIRE ) {
                                $this->addRequire( implode( '', $require ), $once, false );
                            }
                            else {
                                $this->addInclude( implode( '', $require ), $once, false );
                            }
                        }
                        break;
                    case T_DOC_COMMENT:
                        if ( $beforeNamespace === true ) {
                            $this->addDocumentComment( $this->prepDocument( $value ) );
                        }
                        else if ( $beforeNamespace === false && $beforeClass === true ) {
                            $this->addDocumentComment( $this->prepDocument( $value ), true );
                        }
                        else {
                            $document = $this->prepDocument( $value );
                        }
                        break;
                    case T_NAMESPACE:
                        $beforeNamespace = false;
                        $nameSpace = [];
                        for ( $ii = $i; $ii < $count; $ii++ ) {
                            $tokenNs = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                            $valueNs = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                            $startNs = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                            if ( $tokenNs === T_STRING ) {
                                $nameSpace[] = $valueNs;
                            }

                            if ( $valueNs === ';' ) {
                                break;
                            }
                            $i++;
                        }
                        $this->addNameSpace( $nameSpace );
                        break;
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_PRIVATE:
                        $visibility = $token;
                        break;
                    case T_STATIC:
                        $static = true;
                        break;
                    case T_ABSTRACT:
                    case T_FINAL:
                        if ( $beforeClass === true ) {
                            $this->addType( $value );
                        }
                        else {
                            $final = true;
                        }
                        break;
                    case T_USE:
                        $uses = [];
                        $type = 'use';
                        for ( $ii = $i; $ii < $count; $ii++ ) {
                            $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                            $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                            $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                            if ( $value2 === ';' || $value2 === ',' ) {
                                $this->prepImport( $uses, $type, !$beforeClass );
                                $uses = [];
                                if ( $value2 === ';' ) {
                                    break;
                                }
                            }
                            if ( $token2 === T_FUNCTION ) {
                                $type = 'function';
                            }
                            else if ( $token2 === T_CONST ) {
                                $type = 'const';
                            }
                            if ( $token2 === T_STRING || $value2 === 'as' ) {
                                $uses[] = $value2;
                            }

                            $i++;
                        }

                        break;
                    case T_VARIABLE:
                        if ( $beforeClass === false && $insideMethod === false ) {
                            $propName = ltrim( trim( $value ), '$' );
                            $propValue = null;
                            $i++;
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                                $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                                if ( $value2 === '=' ) {
                                    continue;
                                }
                                if ( $value2 === ';' ) {
                                    break;
                                }
                                $propValue .= $value2;
                                $i++;
                            }
                            $vis = 'public';
                            if ( $visibility === T_PRIVATE ) {
                                $vis = 'private';
                            }
                            else if ( $visibility === T_PROTECTED ) {
                                $vis = 'protected';
                            }
                            $extra = [
                                'static'     => $static,
                                'document'   => $document,
                                'visibility' => $vis,
                            ];
                            $this->addProperty( $propName, $propValue, $extra );
                            $visibility = null;
                            $static = null;
                            $document = null;
                        }
                        break;
                    case T_CONSTANT_ENCAPSED_STRING:
                        if ( mb_strpos( $value, 'SUITE_UNIQUE_KEY' ) !== false && $beforeClass === true ) {
                            $this->addHeaderCatch();
                        }
                        break;
                    case T_CLASS:

                        if ( $beforeClass === true ) {
                            $beforeClass = false;
                            $class = true;
                            $extends = false;
                            $extendsClass = null;
                            $implements = false;
                            $implementsList = null;
                            $interfaceClass = [];
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                                $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                                if ( $value2 === '{' ) {
                                    if ( $extendsClass !== null ) {
                                        $this->addExtends( $extendsClass, false );
                                    }
                                    if ( empty( $interfaceClass ) !== true ) {
                                        $this->addInterface( $interfaceClass );
                                        $interfaceClass = [];
                                    }
                                    break;
                                }

                                if ( $token2 === T_EXTENDS ) {
                                    $extends = true;
                                    $class = false;
                                    $implements = false;
                                }

                                if ( $token2 === T_IMPLEMENTS ) {
                                    if ( $extends === true ) {
                                        $this->addExtends( $extendsClass, false );
                                        $extendsClass = null;
                                    }
                                    $implements = true;
                                    $extends = false;
                                    $class = false;
                                }

                                if ( $value2 === ',' && empty( $interfaceClass ) !== true && $implements === true ) {
                                    $this->addInterface( $interfaceClass );
                                    $interfaceClass = [];
                                }

                                if ( $token2 === T_STRING ) {
                                    if ( $class === true ) {
                                        $class = false;
                                        $this->addClassName( $value2 );
                                    }
                                    else if ( $extends === true && $implements === false ) {
                                        $extendsClass[] = $value2;
                                    }
                                    else if ( $implements === true ) {
                                        Log::log( $value2 );
                                        $interfaceClass[] = $value2;
                                    }
                                }
                                $i++;
                            }
                        }
                        break;
                    case T_FUNCTION:
                        if ( $this->doMethods === false ) {
                            break 2;
                        }
                        $insideMethod = true;
                        $method = null;
                        $onParams = false;
                        $params = [];
                        $body = [];
                        $returnType = null;
                        $insidebody = false;
                        $last = 0;
                        $breakOn = [
                            T_PUBLIC,
                            T_PROTECTED,
                            T_PRIVATE,
                            T_FINAL,
                            T_DOC_COMMENT,
                            T_FUNCTION,
                        ];
                        $i++;
                        for ( $ii = $i; $ii < $count; $ii++ ) {

                            $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                            $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                            $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];

                            if ( (int)$start2 ) {
                                $last = $start2;
                            }
                            if ( in_array( $token2, $breakOn, true ) ) {
                                if ( in_array( $tokens[ $ii + 1 ][ 0 ], $breakOn, true ) || in_array( $tokens[ $ii + 2 ][ 0 ], $breakOn, true ) ) {

                                    //                                    unset( $body[ $open ], $body[ $end ] );
                                    //                                    $closeTag = false;
                                    $insidebody = false;
                                    break;
                                }
                            }

                            if ( $onParams === true && $value2 === ')' ) {
                                $onParams = false;
                            }

                            if ( $token2 === T_STRING && $method === null ) {
                                $method = $value2;
                                $onParams = true;
                            }
                            else if ( $onParams ) {
                                if ( $value2 === '(' ) {
                                    continue;
                                }
                                if ( $value2 === ')' ) {
                                    $onParams = false;
                                    continue;
                                }
                                $param = '';

                                for ( $iii = $ii; $iii < $count; $iii++ ) {
                                    $value3 = $tokens[ $iii ][ 1 ] ?? $tokens[ $iii ];
                                    $i++;
                                    $ii++;
                                    if ( $value3 === '(' ) {
                                        $i--;
                                        $ii--;
                                        continue;
                                    }
                                    if ( $value3 === ',' || $value3 === ')' ) {
                                        if ( $value3 === ')' ) {
                                            $onParams = false;
                                            $i--;
                                            $ii--;
                                        }
                                        break;
                                    }
                                    $param .= $value3;

                                }
                                $params[] = $param;
                            }
                            else if ( $onParams === false && $insidebody === false && $returnType === null && $value2 === ':' ) {
                                for ( $iii = $ii; $iii < $count; $iii++ ) {
                                    $value3 = $tokens[ $iii ][ 1 ] ?? $tokens[ $iii ];
                                    if ( $value3 === ':' || !trim( $value3 ) ) {
                                        continue;
                                    }
                                    $i++;
                                    $ii++;
                                    if ( $value3 === '{' ) {
                                        $insidebody = true;

                                        break;
                                    }
                                    $returnType[] = $value3;
                                }
                            }
                            else if ( isset( $tokens[ $ii ][ 2 ] ) ) {
                                if ( isset( $body[ $start2 ] ) ) {
                                    $content = $body[ $start2 ];
                                    $body[ $start2 ] = [
                                        'line'    => $start2,
                                        'content' => $content[ 'content' ] . $value2,
                                    ];
                                }
                                else {
                                    $body[ $start2 ] = [
                                        'line'    => $start2,
                                        'content' => $value2,
                                    ];
                                }
                                if ( $value2 === '{' ) {
                                    $insidebody = true;
                                }
                            }
                            else if ( $token2 === $value2 ) {
                                if ( $insidebody === false && $value2 === ')' ) {
                                    continue;
                                }
                                //we assume this value is a special non-token character and gets added the "last line"
                                if ( isset( $body[ $last ] ) ) {
                                    $content = $body[ $last ];
                                    $body[ $last ] = [
                                        'line'    => $last,
                                        'content' => $content[ 'content' ] . $value2,
                                    ];
                                }
                                else {
                                    $body[ $last ] = [
                                        'line'    => $last,
                                        'content' => $value2,
                                    ];
                                }

                                if ( $value2 === '{' ) {
                                    $insidebody = true;
                                }
                            }
                            $i++;
                        }

                        $extra = [
                            'name'       => $method,
                            'static'     => $static,
                            'visibility' => $visibility,
                            'final'      => $final,
                            'document'   => $document,
                            'params'     => $params,
                            'returnType' => $returnType,
                            'body'       => $body,
                        ];
                        $final = null;
                        $static = null;
                        $visibility = null;
                        $document = null;
                        $this->prepMethod( $extra );
                        break;
                }
            }

            return $this;
        }

        throw new InvalidArgumentException( 'Path is invalid: ' . $this->path );

    }

    protected function prepDocument( $document )
    {

        $sliced = explode( "\n", $document );
        array_shift( $sliced );
        array_pop( $sliced );
        $dc = [];
        foreach ( $sliced as $slice ) {
            $slice = trim( str_replace( '*', '', $slice ) );
            if ( $slice ) {
                $dc[] = $slice;
            }
        }

        return $dc;
    }

    public function addPath( $path )
    {

        $this->path = $path;
        if ( $this->compiled === false ) {
            $this->compile();
        }
    }

    public function noMethods()
    {

        $this->doMethods = false;
    }

    public function backup()
    {

        $path = ROOT_PATH . '/' . $this->path;
        if ( $this->path !== null && file_exists( $path ) ) {
            $contents = \file_get_contents( $path );
            \file_put_contents( ROOT_PATH . '/' . $this->backUpName(), $contents );
        }
    }

    protected function backUpName()
    {

        return str_replace( '.php', '.backup.php', $this->path );
    }

    public function hasBackup()
    {

        $path = ROOT_PATH . '/' . $this->backUpName();

        return file_exists( $path );
    }

}
