<?php

namespace Generator\Tokenizers;

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

    /**
     * @var \SplFileInfo
     */
    protected $file;

    public function __construct( $file, $hook = false )
    {

        $this->isHook = $hook;

        if ( !file_exists( $file ) ) {
            $file = ROOT_PATH . '/' . $file;
            if ( !file_exists( $file ) ) {
                throw new InvalidArgumentException( 'Path is invalid: ' . $this->path );
            }
        }
        $this->file = new \SplFileInfo( $file );
        $this->path = $this->file->getPath();
        $this->compile();
    }

    /**
     * @param $path
     */
    protected function compile()
    {

        $this->compiled = true;
        $source = file_get_contents( $this->file->getRealPath() );
        $tokens = token_get_all( $source );
        $count = count( $tokens );
        $beforeClass = true;
        $beforeNamespace = true;
        $document = null;
        $visibility = null;
        $static = null;
        $final = false;
        $abstract = false;
        $propName = null;
        $insideMethod = false;
        $type = null;
        $classStart = 0;
        $classEnd = 0;
        $classEnded = false;
        $firstExtra = true;
        $lastMethod = null;
        for ( $i = 0; $i < $count; $i++ ) {
            $token = $tokens[ $i ][ 0 ] ?? $tokens[ $i ];
            $value = $tokens[ $i ][ 1 ] ?? $tokens[ $i ];
            $start = $tokens[ $i ][ 2 ] ?? $tokens[ $i ];
            if ( $beforeClass === false ) {
                if ( $value === '{' ) {
                    $classStart++;
                }
                if ( $value === '}' ) {
                    $classEnd++;
                    if ( $classEnd === $classStart ) {
                        $classEnded = true;
                        continue;
                    }
                }

            }

            if ( $classEnded === true ) {
                $this->addToExtra( $value );
            }
            else {
                switch ( $token ) {

                    case T_COMMENT:
                        $beforeNamespace = false;

                        if ( $beforeClass === false && $insideMethod === false && $lastMethod !== null ) {
                            $this->afterMethod( $lastMethod, $value );
                        }
                        break;
                    case T_CONST:
                        $beforeNamespace = false;

                        if ( $beforeClass === false && $insideMethod === false ) {
                            $constName = null;
                            $constVal = null;
                            $first = true;
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                                $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                                if ( $value2 === '=' || $token2 === T_CONST ) {
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
                        $beforeNamespace = false;

                        if ( $beforeClass === true ) {
                            $once = false;
                            if ( $token === T_REQUIRE_ONCE || $token === T_INCLUDE_ONCE ) {
                                $once = true;
                            }
                            $require = [];
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];

                                if ( $token2 === T_REQUIRE || $token2 === T_REQUIRE_ONCE || $token2 === T_INCLUDE || $token2 === T_INCLUDE_ONCE ) {
                                    continue;
                                }

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
                        if ( $beforeClass === true ) {
                            $this->makeAbstract();
                        }
                        else {
                            $abstract = true;
                        }
                        break;
                    case T_FINAL:
                        if ( $beforeClass === true ) {
                            $this->makeFinal();
                        }
                        else {
                            $final = true;
                        }
                        break;
                    case T_USE:
                        $beforeNamespace = false;

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
                        $beforeNamespace = false;

                        if ( $beforeClass === false && $insideMethod === false ) {
                            $propName = ltrim( trim( $value ), '$' );
                            $propValue = null;
                            for ( $ii = $i; $ii < $count; $ii++ ) {
                                $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                                $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                                $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                                if ( $token2 === T_VARIABLE || $value2 === '=' || $value2 === '"' || $value2 === "'" ) {
                                    $i++;
                                    continue;
                                }
                                if ( $value2 === ';' ) {
                                    break;
                                }
                                if ( $value2 === ')' && ( $tokens[ $ii + 1 ] === '{' || $tokens[ $ii + 2 ] === '{' ) ) {
                                    break;
                                }

                                $propValue .= $value2;
                                $i++;
                            }

                            //                        if ( ( $propValue !== null || $propName !== T_CONST || mb_strpos( $propValue, '::' ) === false || mb_strpos( $propValue, 'self::' ) === false || mb_strpos( $propValue, 'static::' ) === false ) && ( mb_strtolower( $propValue ) !== 'null' && ( mb_strpos( $propValue, '"' ) !== 0 || mb_strpos( $propValue, "'" ) !== 0 ) ) ) {
                            //                            $toEval = 'return ' . $propValue . ';';
                            //                            try {
                            //                                $propValue = eval( $toEval );
                            //                            } catch ( \Exception $e ) {
                            //                                _p( $propValue, $tokens );
                            //                                $propValue = null;
                            //                            }
                            //                        }

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
                            $this->addProperty( $propName, trim( $propValue ), $extra );
                            $visibility = null;
                            $static = null;
                            $document = null;
                        }
                        break;
                    case T_CONSTANT_ENCAPSED_STRING:
                        if ( mb_strpos( $value, 'SUITE_UNIQUE_KEY' ) !== false && $beforeClass === true ) {
                            $beforeNamespace = false;
                            $this->addHeaderCatch();
                        }
                        break;
                    case T_CLASS:
                        $beforeNamespace = false;
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
                                    if ( count( $extendsClass ) >= 2 ) {
                                        $extendsClass = '\\' . implode( '\\', $extendsClass );
                                    }
                                    $this->addExtends( $extendsClass, false );
                                }
                                if ( empty( $interfaceClass ) !== true ) {
                                    $this->addInterface( $interfaceClass );
                                    $interfaceClass = [];
                                }
                                $classStart++;
                                break;
                            }

                            if ( $token2 === T_EXTENDS ) {
                                $extends = true;
                                $class = false;
                                $implements = false;
                            }

                            if ( $token2 === T_IMPLEMENTS ) {
                                if ( $extends === true ) {
                                    if ( count( $extendsClass ) >= 2 ) {
                                        $extendsClass = '\\' . implode( '\\', $extendsClass );
                                    }
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
                                    $interfaceClass[] = $value2;
                                }
                            }
                            $i++;
                        }
                        break;
                    case T_FUNCTION:
                        $insideMethod = true;
                        $method = null;
                        $params = null;
                        $body = [];
                        $returnType = null;
                        $first = true;
                        $startTags = 0;
                        $closeTags = 0;
                        $methodEnd = false;
                        $params = null;
                        $onParams = false;
                        $onReturn = false;
                        $onMethodName = false;
                        $onBody = false;
                        $breakOn = [
                            T_PUBLIC,
                            T_PROTECTED,
                            T_PRIVATE,
                            T_FINAL,
                            T_DOC_COMMENT,
                            T_FUNCTION,
                            T_STATIC,
                            T_ABSTRACT,
                        ];
                        for ( $ii = $i; $ii < $count; $ii++ ) {
                            $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                            $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                            $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                            if ( (int)$start2 ) {
                                $last = $start2;
                            }
                            if ( $value2 === '{' ) {
                                $startTags++;

                            }

                            if ( $value2 === '}' ) {
                                $closeTags++;

                                if ( $closeTags === $startTags ) {
                                    $classEnd--;

                                    $methodEnd = true;
                                }
                            }

                            if ( $methodEnd === true ) {
                                $insideMethod = false;
                                break;
                            }

                            if ( $token2 === T_FUNCTION && $first === true ) {
                                $i++;
                                $first = false;
                                $onMethodName = true;
                                continue;
                            }

                            if ( $onMethodName === true ) {
                                if ( $value2 === '(' ) {
                                    $onParams = true;
                                    $onMethodName = false;
                                    continue;
                                }
                                $method = $value2;

                            }

                            else if ( $onParams === true ) {

                                if ( $params === null && $value2 === '(' ) {
                                    continue;
                                }

                                if ( $value2 === ')' && ( $tokens[ $ii + 1 ] === ':' || $tokens[ $ii + 2 ] === ':' ) ) {
                                    $onParams = false;
                                    $onReturn = true;
                                }

                                if ( $value2 === ')' && ( $tokens[ $ii + 1 ] === '{' || $tokens[ $ii + 2 ] === '{' ) ) {
                                    $onParams = false;
                                }

                                if ( $value2 === ')' && ( $tokens[ $ii + 1 ] === ';' || $tokens[ $ii + 2 ] === ';' ) ) {
                                    $methodEnd = true;
                                    continue;
                                }

                                if ( $onParams === true ) {
                                    $params .= $value2;
                                }
                            }

                            else if ( $onReturn === true ) {
                                if ( $value2 === '{' ) {
                                    $onReturn = false;
                                    continue;
                                }

                                if ( $value2 === ':' ) {
                                    continue;
                                }
                                $returnType .= $value2;
                            }

                            else if ( $method !== null && $onReturn === false && $onParams === false ) {

                                if ( isset( $tokens[ $ii ][ 2 ] ) ) {
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
                                }
                                else {
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
                                }
                            }
                            $i++;

                        }

                        $extra = [
                            'name'       => trim( $method ),
                            'static'     => $static,
                            'visibility' => $visibility,
                            'final'      => $final,
                            'abstract'   => $abstract,
                            'document'   => $document,
                            'params'     => $params,
                            'returnType' => trim( $returnType ),
                            'body'       => $body,
                        ];
                        $abstract = null;
                        $final = null;
                        $static = null;
                        $visibility = null;
                        $document = null;
                        $lastMethod = trim( $method );
                        try {
                            $this->prepMethod( $extra );
                        } catch ( \Exception $e ) {
                        }
                        break;
                    default:
                        $this->extra( [ $value ] );
                        break;

                }
            }
        }
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

    protected function reflection()
    {

        $source = file_get_contents( $this->file->getRealPath() );
        $tokens = token_get_all( $source );
        $count = count( $tokens );
        $setNamespace = false;
        $i = 0;
        for ( $i = 0; $i < $count; $i++ ) {
            $token = $tokens[ $i ][ 0 ] ?? $tokens[ $i ];
            $value = $tokens[ $i ][ 1 ] ?? $tokens[ $i ];
            $start = $tokens[ $i ][ 2 ] ?? $tokens[ $i ];
            if ( $token === T_NAMESPACE ) {
                $setNamespace = true;
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
            }

            $i++;
        }
    }

}
