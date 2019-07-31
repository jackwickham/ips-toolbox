<?php

namespace IPS\toolbox\Generator\Builders;

use IPS\toolbox\Proxy\Proxyclass;
use function file_put_contents;
use function is_array;
use const IPS\ROOT_PATH;

abstract class _GeneratorAbstract
{

    /**
     * read/write path of class file
     *
     * @var null
     */
    public $path;

    public $isProxy = false;

    /**
     * the file document
     *
     * @var array
     */
    protected $docComment;

    /**
     * class name space
     *
     * @var string
     */
    protected $nameSpace;

    /**
     * inlucde the IPS system check header
     *
     * @var bool
     */
    protected $headerCatch = false;

    /**
     * class comment
     *
     * @var array
     */
    protected $classComment = [];

    /**
     * class name
     *
     * @var string
     */
    protected $className;

    /**
     * class contents to write to file
     *
     * @var string
     */
    protected $toWrite;

    /**
     * this gets added after the class body
     *
     * @var string
     */
    protected $extra;

    /**
     * an array of required files
     *
     * @var array
     */
    protected $required = [];

    /**
     * an array of included files
     *
     * @var array
     */
    protected $included = [];

    protected $tab = '    ';

    /**
     * ClassAbstract constructor.
     *
     * @param null $path
     */
    public function __construct( $path = null )
    {

        $this->path = ROOT_PATH . '/' . $path;
    }

    public function addPath( $path )
    {

        $this->path = $path;

    }

    /**
     * @param array $comment
     * @param bool  $class
     *
     * @return $this
     */
    public function addDocumentComment( array $comment, bool $class = false )
    {

        if ( $class === false ) {
            $this->docComment = $comment;
        }
        else {
            $this->classComment = $comment;
        }
    }

    public function getDocumentComment()
    {

        return $this->docComment;
    }

    public function getClassComment()
    {

        return $this->classComment;
    }

    /**
     * @param $namespace
     *
     * @return $this
     */
    public function addNameSpace( $namespace )
    {

        if ( is_array( $namespace ) ) {
            $namespace = implode( '\\', $namespace );
        }
        $this->nameSpace = $namespace;

    }

    public function getNameSpace()
    {

        return $this->nameSpace;
    }

    /**
     * @return $this
     */
    public function addHeaderCatch()
    {

        $this->headerCatch = true;

    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function addClassName( string $class )
    {

        $this->className = $class;
    }

    public function getClassName()
    {

        return $this->className;
    }

    /**
     * @param null $path
     */
    public function write( $path = null )
    {

        $this->writeHead();

        if ( $this->classComment ) {
            $this->toWrite .= "\n";
            $this->toWrite .= "/**\n";
            foreach ( $this->classComment as $item ) {
                $this->toWrite .= '* ' . $item . "\n";
            }
            $this->toWrite .= '*/';
        }

        $this->writeSourceType();
        $this->writeBody();

        if ( $this->extra !== null ) {
            if ( mb_strpos( trim( $this->extra ), '}' ) !== 0 ) {
                $this->toWrite .= "}\n";
            }
            $this->toWrite .= "\n" . $this->extra;
        }
        else {
            $this->toWrite .= '}';
        }

        if ( $path === null ) {
            $path = $this->path;
        }
        //        file_put_contents( ROOT_PATH . '/foo.php', $this->toWrite );

        if ( $this->isProxy === false ) {
            Proxyclass::i()->buildAndMake( $path );
        }
        $fileInfo = pathinfo( $path );
        $dir = $fileInfo[ 'dirname' ];
        if ( !is_dir( $dir ) ) {
            \mkdir( $dir, 0777, true );
        }
        //        \file_put_contents( ROOT_PATH . '/foo.php', $this->toWrite );
        file_put_contents( $path, $this->toWrite );
    }

    protected function writeHead()
    {

        $this->toWrite = <<<'EOF'
<?php

EOF;

        if ( $this->docComment ) {
            $this->toWrite .= "\n";
            $this->toWrite .= "/**\n";
            foreach ( $this->docComment as $item ) {
                $this->toWrite .= '* ' . $item . "\n";
            }
            $this->toWrite .= '*/';
            $this->toWrite .= "\n";
        }

        if ( $this->nameSpace ) {
            $this->toWrite .= <<<EOF

namespace {$this->nameSpace};

EOF;
        }

        $this->afterNameSpace();

        if ( $this->headerCatch === true ) {
            $this->toWrite .= <<<'EOF'


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

EOF;

        }

        if ( empty( $this->required ) !== true ) {
            $this->toWrite .= "\n";
            foreach ( $this->required as $required ) {
                $escaped = null;
                if ( $required[ 'escape' ] === true ) {
                    $escaped = '"';
                }
                if ( $required[ 'once' ] === true ) {

                    $this->toWrite .= 'require_once ' . $escaped . $required[ 'path' ] . $escaped . ";\n";
                }
                else {
                    $this->toWrite .= 'require ' . $escaped . $required[ 'path' ] . $escaped . ";\n";
                }
            }
        }

        if ( empty( $this->included ) !== true ) {
            $this->toWrite .= "\n";
            foreach ( $this->included as $included ) {
                $escaped = null;
                if ( $included[ 'escape' ] === true ) {
                    $escaped = '"';
                }
                if ( $included[ 'once' ] === true ) {
                    $this->toWrite .= 'include_once ' . $escaped . $included[ 'path' ] . $escaped . ";\n";
                }
                else {
                    $this->toWrite .= 'include ' . $escaped . $included[ 'path' ] . $escaped . ";\n";
                }
            }
        }
    }

    protected function afterNameSpace()
    {

    }

    abstract protected function writeSourceType();

    abstract protected function writeBody();

    /**
     * @param array $extra
     *
     * @return $this
     */
    public function extra( $extra )
    {

        $this->extra = $extra;
    }

    public function getExtra()
    {

        return $this->extra;
    }

    /**
     * @param      $path
     * @param bool $once
     * @param bool $escape
     */
    public function addRequire( $path, $once = false, $escape = true )
    {

        $hash = $this->hash( $path );

        $this->required[ $hash ] = [ 'path' => $path, 'once' => $once, 'escape' => $escape ];
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function hash( $value )
    {

        return md5( trim( $value ) );
    }

    public function getRequired()
    {

        return $this->required;
    }

    public function getIncluded()
    {

        return $this->included;
    }

    /**
     * @param      $path
     * @param bool $once
     * @param bool $escape
     */
    public function addInclude( $path, $once = false, $escape = true )
    {

        $hash = $this->hash( $path );
        $this->included[ $hash ] = [ 'path' => $path, 'once' => $once, 'escape' => $escape ];
    }

}
