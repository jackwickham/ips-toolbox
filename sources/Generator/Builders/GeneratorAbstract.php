<?php

namespace IPS\toolbox\Generator\Builders;

use InvalidArgumentException;
use IPS\toolbox\Proxy\Proxyclass;
use RuntimeException;
use function count;
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

    public $isHook = false;

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

    protected $fileName;

    public function addPath( $path )
    {

        $this->path = $path;
        if ( !is_dir( $this->path ) ) {
            $this->path = ROOT_PATH . '/' . $path;
        }
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
     * @deprecated use static::save();
     */
    public function write()
    {

        $this->save();
    }

    /**
     * @param null $path
     */
    public function save()
    {

        if ( $this->className === null ) {
            throw new InvalidArgumentException( 'Classname is not set!' );
        }

        if ( !is_dir( $this->path ) && !mkdir( $this->path, 0777, true ) && !is_dir( $this->path ) ) {
            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $this->path ) );
        }

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
        $this->toWrite .= "\n{";
        $this->writeBody();
        $this->toWrite .= "\n}";
        $this->writeExtra();

        if ( $this->isProxy === false ) {
            Proxyclass::i()->buildAndMake( $this->saveFileName() );
        }

        //file_put_contents( ROOT_PATH . '/foo.php', $this->toWrite );
        file_put_contents( $this->saveFileName(), $this->toWrite );
    }

    protected function writeHead()
    {

        if ( $this->isHook === true ) {
            $this->toWrite = <<<'EOF'
//<?php

EOF;
        }
        else {
            $this->toWrite = <<<'EOF'
<?php

EOF;
        }

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

    protected function writeExtra()
    {

        if ( $this->extra !== null ) {
            $this->toWrite .= "\n";
            if ( is_array( $this->extra ) && count( $this->extra ) ) {
                foreach ( $this->extra as $extra ) {
                    $this->toWrite .= $extra;
                }
            }
            else {
                $this->toWrite .= $this->extra;
            }
        }
    }

    protected function saveFileName()
    {

        $name = $this->fileName;
        if ( $name === null ) {
            $name = $this->className;
        }

        return $this->path . '/' . $name . '.php';
    }

    public function addFileName( string $name )
    {

        $info = pathinfo( $name );
        $this->fileName = $info[ 'filename' ] ?? null;
    }

    /**
     * @param array $extra
     *
     * @return $this
     */
    public function extra( array $extra )
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
