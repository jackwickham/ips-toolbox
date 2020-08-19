<?php

/**
 * @brief       ClassBuilder Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace IPS\toolbox;

use InvalidArgumentException;
use IPS\Log;
use function count;
use function file_exists;
use function file_get_contents;
use function in_array;
use function is_array;
use function token_get_all;
use const IPS\ROOT_PATH;

use function hash;
use function implode;
use function mb_strpos;
use function md5;
use function trim;
use const T_ABSTRACT;
use const T_CLASS;
use const T_CONSTANT_ENCAPSED_STRING;
use const T_DOC_COMMENT;
use const T_EXTENDS;
use const T_FINAL;
use const T_FUNCTION;
use const T_IMPLEMENTS;
use const T_NAMESPACE;
use const T_PRIVATE;
use const T_PROTECTED;
use const T_PUBLIC;
use const T_STATIC;
use const T_STRING;
use const T_USE;
use const T_VARIABLE;


class _ClassBuilder
{

    protected $docComment;

    protected $nameSpace = [];

    protected $imports = [];

    protected $importsFunctions = [];

    protected $headerCatch = false;

    protected $classComment;

    protected $className;

    protected $interfaces = [];

    protected $extends;

    protected $properties;

    protected $methods = [];

    protected $classUses = [];

    protected $type;

    protected $toWrite;

    protected $removeMethods = [];

    public function __construct()
    {

    }

    public static function tokenize( $path )
    {

        $fullPath = ROOT_PATH . '/' . $path;
        if ( file_exists( $fullPath ) ) {
            $source = file_get_contents( $fullPath );
            $tokens = token_get_all( $source );
            $count = count( $tokens );
            $beforeClass = true;
            $beforeNamespace = true;
            $document = null;
            $visibility = null;
            $static = null;
            $propName = null;
            $insideMethod = false;
            $type = null;
            $newClass = new static;
            for ( $i = 0; $i < $count; $i++ ) {
                $token = $tokens[ $i ][ 0 ] ?? $tokens[ $i ];
                $value = $tokens[ $i ][ 1 ] ?? $tokens[ $i ];
                $start = $tokens[ $i ][ 2 ] ?? $tokens[ $i ];
                switch ( $token ) {
                    case T_ABSTRACT:
                    case T_FINAL:
                        if ( $beforeClass === true ) {

                            $newClass->type = $value;
                        }
                        break;
                    case T_DOC_COMMENT:
                        if ( $beforeNamespace === true ) {
                            $newClass->addDocumentComment( $value );
                        }
                        else if ( $beforeNamespace === false && $beforeClass === true ) {
                            $newClass->addDocumentComment( $value, true );
                        }
                        else {
                            $document = $value;
                        }
                        break;
                    case T_PUBLIC:
                    case T_PROTECTED:
                    case T_PRIVATE:
                        $visibility = $token;
                        break;
                    case T_STATIC:
                        $static = true;
                        break;
                    case T_VARIABLE:
                        if ( $beforeClass === false && $insideMethod === false ) {
                            $propName = $value;
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

                            $extra = [
                                'static'     => $static,
                                'document'   => $document,
                                'visibility' => $visibility,
                            ];
                            $newClass->addProperty( $propName, $propValue, $extra );
                            $visibility = null;
                            $static = null;
                            $document = null;
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
                        $newClass->addNameSpace( $nameSpace );
                        break;
                    case T_USE:
                        $uses = [];
                        $alt = false;
                        for ( $ii = $i; $ii < $count; $ii++ ) {
                            $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                            $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                            $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                            if ( $token2 === T_FUNCTION ) {
                                $alt = true;
                            }
                            if ( $token2 === T_STRING ) {
                                $uses[] = $value2;
                            }

                            if ( $value2 === ';' ) {
                                $newClass->addImport( $uses, $alt, !$beforeClass );
                                break;
                            }
                            $i++;
                        }

                        break;
                    case T_CONSTANT_ENCAPSED_STRING:
                        if ( mb_strpos( $value, 'SUITE_UNIQUE_KEY' ) !== false && $beforeClass === true ) {
                            $newClass->addHeaderCatch();
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
                                        $newClass->addExtends( $extendsClass );
                                    }
                                    if ( empty( $interfaceClass ) !== true ) {
                                        $newClass->addInterface( $interfaceClass );
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
                                        $newClass->addExtends( $extendsClass );
                                        $extendsClass = null;
                                    }
                                    $implements = true;
                                    $extends = false;
                                    $class = false;
                                }

                                if ( $value2 === ',' && empty( $interfaceClass ) !== true && $implements === true ) {
                                    $newClass->addInterface( $interfaceClass );
                                    $interfaceClass = [];
                                }

                                if ( $token2 === T_STRING ) {
                                    if ( $class === true ) {
                                        $class = false;
                                        $newClass->addClassName( $value2 );
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
                        $insideMethod = true;
                        $method = null;
                        $onParams = false;
                        $params = [];
                        $body = null;
                        $last = 0;
                        $breakOn = [
                            T_PUBLIC,
                            T_PROTECTED,
                            T_PRIVATE,
                            T_FINAL,
                            T_DOC_COMMENT,
                        ];
                        $i++;
                        for ( $ii = $i; $ii < $count; $ii++ ) {
                            $token2 = $tokens[ $ii ][ 0 ] ?? $tokens[ $ii ];
                            $value2 = $tokens[ $ii ][ 1 ] ?? $tokens[ $ii ];
                            $start2 = $tokens[ $ii ][ 2 ] ?? $tokens[ $ii ];
                            $line = $start2;
                            if ( in_array( $token2, $breakOn, true ) ) {
                                if ( in_array( $tokens[ $ii + 1 ][ 0 ], $breakOn, true ) || in_array( $tokens[ $ii + 2 ][ 0 ], $breakOn, true ) ) {
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
                                $param = $value2;

                                for ( $iii = $ii; $iii < $count; $iii++ ) {
                                    $value3 = $tokens[ $iii ][ 1 ] ?? $tokens[ $iii ];
                                    $i++;
                                    $ii++;
                                    if ( $value3 === '(' ) {
                                        continue;
                                    }
                                    if ( $value3 === ',' || $value3 === ')' ) {
                                        if ( $value3 === ')' ) {
                                            $onParams = false;
                                        }
                                        $i--;
                                        $ii--;
                                        break;
                                    }
                                    $param .= $value3;

                                }
                                $params[] = $param;
                            }
                            else if ( isset( $tokens[ $ii ][ 2 ] ) ) {
                                $body[] = [ 'line' => $start2, 'content' => $value2, 'token' => $token2 ];
                            }
                            else if ( $token2 === $value2 ) {
                                $body[] = [ 'content' => $value2, 'line' => $last++ ];
                            }
                            $i++;
                        }
                        $extra = [
                            'static'     => $static,
                            'visibility' => $visibility,
                            'params'     => $params,
                            'body'       => $body,
                            'document'   => $document,
                        ];
                        $static = null;
                        $visibility = null;
                        $document = null;
                        $newClass->addMethod( $method, $extra );
                        break;
                }
            }

            return $newClass;
        }

        throw new InvalidArgumentException( 'Path is invalid: ' . $fullPath );

    }

    public function addDocumentComment( string $comment, bool $class = false )
    {

        if ( $class === false ) {
            $this->docComment = $comment;
        }
        else {
            $this->classComment = $comment;
        }

        return $this;
    }

    public function addProperty( string $name, $value, array $extra )
    {

        $hash = md5( $name );
        $this->properties[ $hash ] = [
            'name'       => $name,
            'value'      => $value,
            'document'   => $extra[ 'document' ] ?? null,
            'static'     => $extra[ 'static' ] ?? false,
            'visibility' => $extra[ 'visibility' ] ?? T_PUBLIC,
        ];

        return $this;
    }

    public function addNameSpace( $namespace )
    {

        if ( is_array( $namespace ) ) {
            $namespace = implode( '\\', $namespace );
        }
        $this->nameSpace = $namespace;

        return $this;

    }

    public function addImport( $import, $function = false, $use = false )
    {

        if ( is_array( $import ) ) {

            $import = implode( '\\', $import );

        }
        $hash = md5( $import );
        if ( $use === false ) {
            if ( $function === true ) {
                $this->importsFunctions[ $hash ] = $import;
            }
            else {
                $this->imports[ $hash ] = $import;
            }
        }
        else {
            $this->classUses[ $hash ] = $import;
        }

        return $this;

    }

    public function addHeaderCatch()
    {

        $this->headerCatch = true;

        return $this;

    }

    public function addExtends( $extends )
    {

        if ( is_array( $extends ) ) {
            $extends = implode( '\\', $extends );
        }
        $this->extends = $extends;

        return $this;
    }

    public function addInterface( $interface )
    {

        if ( is_array( $interface ) ) {
            $prefix = '';
            if ( count( $interface ) >= 2 ) {
                $prefix = '\\';
            }
            $interface = $prefix . implode( '\\', $interface );
        }
        $hash = md5( $interface );
        $this->interfaces[ $hash ] = $interface;

        return $this;
    }

    public function addClassName( string $class )
    {

        $this->className = $class;

        return $this;
    }

    public function addMethod( $name, $extra )
    {

        $this->methods[ trim( $name ) ] = $extra;

        return $this;
    }

    public function removemethod( $name )
    {

        $this->removeMethods[ trim( $name ) ] = 1;

        return $this;
    }

    public function write()
    {

        $tab = '    ';
        $this->toWrite = <<<'EOF'
<?php

EOF;

        if ( $this->docComment ) {
            $this->toWrite .= <<<EOF

{$this->docComment}

EOF;
        }

        if ( $this->nameSpace ) {
            $this->toWrite .= <<<EOF

namespace {$this->nameSpace};

EOF;
        }

        if ( empty( $this->imports ) !== true ) {
            foreach ( $this->imports as $import ) {
                $this->toWrite .= "\nuse {$import};";
            }
        }

        if ( empty( $this->importsFunctions ) !== true ) {
            foreach ( $this->importsFunctions as $import ) {
                $this->toWrite .= "\nuse function {$import};";
            }
        }

        if ( $this->headerCatch === true ) {
            $this->toWrite .= <<<'EOF'


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

EOF;

        }

        if ( $this->classComment ) {
            $this->toWrite .= <<<EOF

{$this->classComment}
EOF;

        }

        $type = null;

        if ( $this->type === T_ABSTRACT ) {
            $type = 'Abstract ';
        }
        else if ( $this->type === T_FINAL ) {
            $type = 'Final ';
        }

        $this->toWrite .= "\n{$type}class {$this->className}";

        if ( $this->extends ) {
            $this->toWrite .= " extends {$this->extends}";
        }

        if ( empty( $this->interfaces ) !== true ) {
            $this->toWrite .= " implements \n" . implode( ",\n", $this->interfaces ) . "\n";
        }

        $this->toWrite .= "{\n";

        if ( empty( $this->properties ) !== true ) {
            foreach ( $this->properties as $property ) {
                $prop = "\n{$tab}";

                if ( $property[ 'document' ] ) {
                    $prop .= <<<EOF

{$tab}{$property['document']}
{$tab}
EOF;

                }

                if ( $property[ 'visibility' ] === T_PRIVATE ) {
                    $prop .= ' private ';
                }
                else if ( $property[ 'visibility' ] === T_PROTECTED ) {
                    $prop .= ' protected ';
                }
                else {
                    $prop .= ' public';
                }

                if ( $property[ 'static' ] === true ) {
                    $prop .= ' static';
                }

                $prop .= ' ' . $property[ 'name' ];

                if ( $property[ 'value' ] ) {
                    $prop .= ' = ' . trim( $property[ 'value' ] );
                }

                $prop .= ";\n";

                $this->toWrite .= $prop;

            }
        }

        if ( $this->methods ) {
            foreach ( $this->methods as $name => $method ) {
                if ( isset( $this->removeMethods[ $name ] ) ) {
                    continue;
                }
                $meth = "\n{$tab}";

                if ( $method[ 'document' ] ) {
                    $meth .= <<<EOF


{$tab}{$method['document']}
{$tab}
EOF;

                }

                if ( $method[ 'visibility' ] === T_PRIVATE ) {
                    $meth .= ' private ';
                }
                else if ( $method[ 'visibility' ] === T_PROTECTED ) {
                    $meth .= ' protected ';
                }
                else {
                    $meth .= ' public';
                }

                if ( $method[ 'static' ] === true ) {
                    $meth .= ' static';
                }

                $meth .= ' function ' . $name . ' ( ';

                if ( empty( $method[ 'params' ] ) !== true ) {
                    $meth .= implode( ', ', $method[ 'params' ] );
                    $meth .= ' )';
                }

                if ( $method[ 'body' ] ) {
                    foreach ( $method[ 'body' ] as $body ) {
                        $meth .= $body[ 'content' ];
                    }
                }

                $this->toWrite .= $meth;
            }
        }
        \file_put_contents( \IPS\ROOT_PATH . '/foo.php', $this->toWrite );
        _p( $this->toWrite );
    }

    protected function hash( $value )
    {

        return md5( trim( $value ) );
    }
}
