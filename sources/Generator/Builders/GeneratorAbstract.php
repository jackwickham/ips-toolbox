<?php

namespace Generator\Builders;

use InvalidArgumentException;
use RuntimeException;

use function count;
use function file_put_contents;
use function is_array;

/**
 * Class _GeneratorAbstract
 *
 * @package IPS\toolbox\Generator\Builders
 */
abstract class GeneratorAbstract
{

    protected const HASCLASS = true;

    /**
     * read/write path of class file
     *
     * @var null
     */
    public $path;

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

    public $hookClass;

    public $hookNamespace;
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

    /**
     * this should be the FULL PATH
     *
     * @param $path
     */
    public function addPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param array $comment
     * @param bool  $class
     *
     * @return $this
     */
    public function addDocumentComment(array $comment, bool $class = false)
    {
        if ($class === false) {
            $this->docComment = $comment;
        } else {
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
    public function addNameSpace($namespace)
    {
        if (is_array($namespace)) {
            $namespace = implode('\\', $namespace);
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
    public function addClassName(string $class)
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
        if (static::HASCLASS === true && $this->className === null) {
            throw new InvalidArgumentException('Classname is not set!');
        }

        if (!is_dir($this->path) && !mkdir($this->path, 0777, true) && !is_dir($this->path)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->path));
        }
        $this->writeHead();

        if ($this->classComment) {
            $this->output("\n\n");
            $this->output("/**\n");
            foreach ($this->classComment as $item) {
                $this->output('* ' . $item . "\n");
            }
            $this->output('*/');
        }

        $this->writeSourceType();
        $this->writeBody();
        $this->toWrite = trim($this->toWrite);
        $this->writeExtra();
        $this->toWrite = trim($this->toWrite);
        $this->wrapUp();
        //file_put_contents( ROOT_PATH . '/foo.php', $this->toWrite );

        file_put_contents($this->saveFileName(), $this->toWrite);
    }

    protected function writeHead()
    {
        if ($this->isHook === true) {
            $ns = '';
            // if( $this->hookNamespace !== null){
            //     $ns = ' namespace '.$this->hookNamespace.';';
            // }
            $openTag = <<<EOF
//<?php {$ns}

EOF;
        } else {
            $openTag = <<<'EOF'
<?php

EOF;
        }
        $this->output($openTag);
        if ($this->docComment) {
            $this->output("\n");
            $this->output("/**\n");
            foreach ($this->docComment as $item) {
                $this->output('* ' . $item . "\n");
            }
            $this->output('*/');
            $this->output("\n");
        }

        if ($this->nameSpace) {
            $ns = <<<EOF

namespace {$this->nameSpace};

EOF;
            $this->output($ns);
        }

        $this->afterNameSpace();
        $this->toWrite .= '#generator_token_includes#';
        $this->toWrite .= '#generator_token_imports#';
        if ($this->headerCatch === true) {
            $headerCatch = <<<'EOF'

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

EOF;

            $this->output($headerCatch);
        }
    }

    public function output(string $output)
    {
        $this->toWrite .= $output;
    }

    protected function afterNameSpace()
    {
    }

    abstract protected function writeSourceType();

    abstract protected function writeBody();

    protected function writeExtra()
    {
        if ($this->extra !== null) {
            $this->output("\n");
            if (is_array($this->extra) && count($this->extra)) {
                foreach ($this->extra as $extra) {
                    $this->output($extra);
                }
            } else {
                $this->output($this->extra);
            }
        }
    }

    protected function wrapUp()
    {
        $replacement = '';
        if (empty($this->required) !== true) {
            $replacement .= "\n";

            foreach ($this->required as $required) {
                $escaped = null;
                if ($required[ 'escape' ] === true) {
                    $escaped = '"';
                }
                if ($required[ 'once' ] === true) {
                    $replacement .= 'require_once ' . $escaped . $required[ 'path' ] . $escaped . ";\n";
                } else {
                    $replacement .= 'require ' . $escaped . $required[ 'path' ] . $escaped . ";\n";
                }
            }
        }

        if (empty($this->included) !== true) {
            $replacement .= "\n";
            foreach ($this->included as $included) {
                $escaped = null;
                if ($included[ 'escape' ] === true) {
                    $escaped = '"';
                }
                if ($included[ 'once' ] === true) {
                    $replacement .= 'include_once ' . $escaped . $included[ 'path' ] . $escaped . ";\n";
                } else {
                    $replacement .= 'include ' . $escaped . $included[ 'path' ] . $escaped . ";\n";
                }
            }
        }

        $this->toWrite = str_replace('#generator_token_includes#', $replacement, $this->toWrite);
    }

    protected function saveFileName()
    {
        $name = $this->fileName;
        if ($name === null) {
            $name = $this->className;
        }

        return $this->path . '/' . $name . '.php';
    }

    public function addFileName(string $name)
    {
        $info = pathinfo($name);
        $this->fileName = $info[ 'filename' ] ?? null;
    }

    /**
     * @param array $extra
     *
     * @return $this
     */
    public function extra(array $extra)
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
    public function addRequire($path, $once = false, $escape = true)
    {
        $hash = $this->hash($path);

        $this->required[ $hash ] = ['path' => $path, 'once' => $once, 'escape' => $escape];
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function hash($value)
    {
        return md5(trim($value));
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
    public function addInclude($path, $once = false, $escape = true)
    {
        $hash = $this->hash($path);
        $this->included[ $hash ] = ['path' => $path, 'once' => $once, 'escape' => $escape];
    }
}
