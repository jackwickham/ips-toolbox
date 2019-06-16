<?php

/**
 * @brief       Slasher Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.4.0
 * @version     -storm_version-
 */


namespace Slasher;

use Exception;
use InvalidArgumentException;
use const T_DOUBLE_COLON;

class SlasherClass
{

    /**
     * @var array
     */
    protected static $constants = [];

    /**
     * @var array
     */
    protected static $backslashable = [];

    /**
     * @var array
     */
    protected static $functions = [];

    protected static $addedFunctions = [];

    public $makeUse = false;

    protected $all = false;

    protected $path;

    /**
     * FileEditor constructor.
     * @param bool $all
     * @param bool $makeUse
     */
    public function __construct($all = false, $makeUse = true)
    {
        $this->all = $all;
        $this->makeUse = $makeUse;
    }

    /**
     * @param string $func add a function name to backslash
     */
    public function addFunction(string $func)
    {
        static::$addedFunctions[ $func ] = $func;
    }

    /**
     * @param      $path
     * @param bool $return
     * @param bool $template
     * @return null|string
     */
    public function addBackslashes($path, $return=false, $template=false)
    {
        try {
            $content = \file_get_contents($path);
            $functions = $this->getReplaceableFunctions($content);
            $constants = $this->getDefinedConstants();
            if( $template === true ){
                $content = "<?php\n".$content;
            }
            $source = \explode("\n", $content);
            $tokens = \token_get_all($content);
            $previousToken = \null;
            $uses = [];

            foreach ($tokens as $key => $token) {
                if (!\is_array($token)) {
                    $tempToken = $token;
                    $token = [0 => 0, 1 => $tempToken, 2 => 0];
                }

                if ($token[ 0 ] === \T_STRING || $token[ 0 ] === \T_ENCAPSED_AND_WHITESPACE) {
                    $line = $token[ 2 ];

                    $t = trim($token[1]);
                    if( $template === \true ) {
                        $t = ltrim($t, '=');
                        $t = rtrim($t, ',');
                        $t = trim($t);
                    }

                    $token[1] = $t;

                    if ($this->isBackslashable($functions, $token, $previousToken, $constants)) {

                        if ($this->makeUse && isset(static::$functions[ $t ]) && false === \mb_strpos($path,
                                DIRECTORY_SEPARATOR . 'hooks' . DIRECTORY_SEPARATOR)) {
                            if (!isset($tokens[ $key - 2 ][ 0 ]) || $tokens[ $key - 2 ][ 0 ] !== \T_FUNCTION) {
                                $uses[ $t ] = $t;
                                $source[ $line - 1 ] = \preg_replace("#\\\\" . $t . '#u', $t, $source[ $line - 1 ]);
                            }
                        } else {

                            $source[ $line - 1 ] = \preg_replace("#(?<!\\\\)\b" . $t . '\b#u',"\\" . $t , $source[ $line - 1 ]);
                        }
                    }
                }

                $previousToken = $token;
            }

            if( $template === true){
                unset( $source[0] );
            }

            $source = \implode("\n", $source);

            if ($this->makeUse) {
                try {
                    $add = [];
                    foreach ($uses as $use) {
                        $add[] = 'use function ' . $use . ';';
                    }

                    if (\count($add)) {
                        $toUse = PHP_EOL . \implode(PHP_EOL, $add);
                        if (false === \mb_strpos($path, DIRECTORY_SEPARATOR . 'hooks' . DIRECTORY_SEPARATOR)) {
                            $source = \preg_replace('/namespace(.+?)([^\n]+)/', 'namespace $2' . $toUse, $source, 1);
                        }
                    }
                } catch (Exception $e) {
                }

            }

            $source = $this->applyFinalFixes($source);

            if( !$return ) {
                file_put_contents($path, $source);
            }
            else{
                return $source;
            }
        } catch (Exception $e) {
        }
    }


    /**
     * @param $content
     * @return array
     */
    protected function getReplaceableFunctions($content): array
    {
        $functions = static::getBackslashableFunctions($this->all);
        $functions = $this->removeUseFunctionsFromBackslashing($content, $functions);

        return $functions;
    }

    /**
     * @param bool $all
     * @return array
     */
    public static function getBackslashableFunctions($all = false): array
    {
        if (!empty(static::$backslashable)) {
            return static::$backslashable;
        }
        static::$functions = \array_map('strtolower', \get_defined_functions()[ 'internal' ]);
        static::$functions = \array_combine(\array_values(static::$functions), static::$functions);
        ksort(static::$functions, \SORT_REGULAR);
        static::$backslashable = [];

        if ($all === false) {
            foreach (static::$functions as $name => $value) {
                if (\true === static::startsWith($value, 'is_')) {
                    static::$backslashable[ $name ] = $value;
                }
            }

            static::$backslashable[ 'array_slice' ] = 'array_slice';
            static::$backslashable[ 'assert' ] = 'assert';
            static::$backslashable[ 'chr' ] = 'chr';
            static::$backslashable[ 'doubleval' ] = 'doubleval';
            static::$backslashable[ 'floatval' ] = 'floatval';
            static::$backslashable[ 'func_get_args' ] = 'func_get_args';
            static::$backslashable[ 'func_num_args' ] = 'func_num_args';
            static::$backslashable[ 'get_called_class' ] = 'get_called_class';
            static::$backslashable[ 'get_class' ] = 'get_class';
            static::$backslashable[ 'gettype' ] = 'gettype';
            static::$backslashable[ 'intval' ] = 'intval';
            static::$backslashable[ 'ord' ] = 'ord';
            static::$backslashable[ 'strval' ] = 'strval';
            static::$backslashable[ 'count' ] = 'count';
            static::$backslashable[ 'in_array' ] = 'in_array';
            static::$backslashable[ 'strlen' ] = 'strlen';
            static::$backslashable[ 'defined' ] = 'defined';
            static::$backslashable[ 'call_user_func' ] = 'call_user_func';
            static::$backslashable[ 'call_user_func_array' ] = 'call_user_func_array';
        } else {
            foreach (static::$functions as $name => $value) {
                static::$backslashable[ $name ] = $value;
            }
        }

        static::$backslashable = \array_merge(static::$backslashable, static::$addedFunctions);
        return static::$backslashable;
    }

    /**
     * Search backwards starting from haystack length characters from the end
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle): bool
    {
        $haystack = trim($haystack);
        $needle = trim($needle);
        return 0 === mb_strpos($haystack, $needle);
    }

    /**
     * If a method exists under a namespace and has been aliased, or has been imported, don't replace.
     *
     * @param       $content
     * @param array $functions
     *
     * @return array
     */
    protected function removeUseFunctionsFromBackslashing($content, array $functions): array
    {
        \preg_match_all('#use(.*?)function([^;]+)#', $content, $matches);
        $funcs = [];
        if (isset($matches[ 2 ])) {
            /* @var array $m */
            $m = $matches[2];
            foreach ($m as $func) {
                $func = trim($func);
                $funcs[ $func ] = $func;
                if (!empty($functions[ $func ]) && \function_exists($func)) {
                        unset($functions[ $func ]);
                    }
                }

        }

        return $functions;
    }

    /**
     * @return array
     */
    protected function getDefinedConstants(): array
    {
        if (empty(self::$constants)) {
            self::$constants = \array_keys(\get_defined_constants(false));
            $c = \array_values(self::$constants);
            self::$constants = \array_combine($c, $c);
        }
        return self::$constants;
    }
    protected $templateHeaders = [];
    protected function templatesHeaders(): array
    {
        if( empty( $this->templateHeaders ) === true ){
            $c = $this->getDefinedConstants();
            foreach( $c as $k => $v ){
                 $this->templateHeaders['raw'][$v] = $v;
                 $this->templateHeaders['l_raw'][\mb_strtolower($v)] = \mb_strtolower($v);
            }
        }
        return $this->templateHeaders;

    }

    /**
     * @param array $functions
     * @param array $token
     * @param array $previousToken
     * @param array $constants
     *
     * @return bool
     */
    protected function isBackslashable(array &$functions, array &$token, array &$previousToken, array &$constants): bool
    {
        if( $previousToken[0] === \T_DOUBLE_COLON || $previousToken[0] === \T_OBJECT_OPERATOR || $previousToken[0] === \T_NS_SEPARATOR ){
            return false;
        }

        return $this->isFunction($functions, $token, $previousToken)
            || $this->isConstant($constants, $token, $previousToken);
    }

    /**
     * @param array $functions
     * @param array $token
     * @param array $previousToken
     *
     * @return bool
     */
    protected function isFunction(array &$functions, array &$token, array &$previousToken): bool
    {
        return !empty($functions[ $token[ 1 ] ]) && $previousToken[ 0 ] !== \T_NAMESPACE && $previousToken[ 0 ] !== \T_OBJECT_OPERATOR;
    }

    /**
     * @param $constants
     * @param $token
     * @param $previousToken
     *
     * @return bool
     */
    protected function isConstant(array &$constants, array &$token, array &$previousToken): bool
    {
//        $t = trim($token[1]);
//        $t = ltrim( $t, '=');
//        $t = rtrim( $t,',');
//        $t = trim( $t );

        return !empty($constants[ \mb_strtoupper($token[1]) ]) && $previousToken[ 0 ] !== \T_NAMESPACE;
    }

    /**
     * @param string $source
     *
     * @return string
     */
    protected function applyFinalFixes($source): string
    {
        $source = \str_replace(['function \\', 'const \\', "::\\", "$\\"], ['function ', 'const ', '::', '$'], $source);
        return (string)$source;
    }
}

class Slasher
{
    protected static $errorMessages = [
        'noFile' => 'The file or app directoy, %s, can\'t be found.',
        'noArgs' => 'No args were passed!',
        'type' => 'Type needs to be set, choices are type=app or type=file',
        'app' => 'App needs to be set, app=myapp',
        'app2' => 'The app, %s, doesn\'t appear to exits.',
        'appRun' => 'Beginning to run Slasher on %s',
        'processing' => 'Processing file: %s',
        'done' => 'Processing done on: %s',
        'appDone' => 'Slasher done on %s',
    ];

    /**
     * @var Slasher
     */
    protected $slasher;

    protected $type;

    protected $app;

    protected $file;

    protected $dir;

    protected $skip;

    protected $key;

    protected $suppressMessages;

    /**
     * slasher constructor.
     * @param array $args
     * @param bool  $suppressMessages
     * @throws Exception
     */
    public function __construct(array $args, bool $suppressMessages = null)
    {
        $this->suppressMessages = $suppressMessages ?? null;
        $all = false;
        $makeUse = false;
        $file = null;
        $skip = [];
        $key = null;
        unset($args[ 0 ]);

        if (!\count($args)) {
            $this->message('noArgs');
        }

        foreach ($args as $k => $val) {
            if ($val === '-all') {
                $all = true;
                unset($args[ $k ]);
                continue;
            }

            if ($val === '-use') {
                $makeUse = true;
                unset($args[ $k ]);
                continue;
            }

            if (false !== mb_stripos($val, '-skip')) {
                list($key, $skip) = explode('=', $val);
                $this->key = $key;
                $skip = explode(',', $skip);
                unset($args[ $k ]);
                continue;
            }
            $file = $val;
        }

        if (!empty($skip)) {
            $this->skip = $skip;
        }

        $this->slasher = new SlasherClass($all, $makeUse);

        try {
            $this->type = 'app';
            $this->app = $file;
            $path = $file;

            if (!is_dir($path)) {
                if (!file_exists($path)) {
                    throw new InvalidArgumentException;
                }
                $this->type = 'file';
                $this->file = $path;
            }
        } catch (InvalidArgumentException $e) {
            $this->message('noFile', true, $file);
        }
    }

    /**
     * @param      $type
     * @param bool $exit
     * @param null $sprint
     */
    protected function message($type, $exit = true, $sprint = null)
    {
        if ($this->suppressMessages === false) {
            $msg = static::$errorMessages[ $type ];
            if ($sprint) {
                $msg = sprintf($msg, $sprint);
            }
            echo $msg . PHP_EOL;

            if ($exit === true) {
                exit;
            }
        }
    }

    /**
     * executes
     */
    public function execute()
    {
        if ($this->type === 'app') {
            $this->parseDirectory();
        } else if ($this->type === 'file') {
            $this->parseFile();
        }
    }

    /**
     * parse all the files in a application
     */
    protected function parseDirectory()
    {
        $dir = $this->app;
        $this->message('appRun', false, $dir);

        $filter = function () {
            return true;
        };

        if (!empty($this->skip)) {
            $filter = function (\SplFileInfo $file) {
                if (\in_array($file->getFilename(), $this->skip, true)) {
                    return false;
                }

                return true;
            };
        }

        $dirIterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator($dirIterator, $filter),
            \RecursiveIteratorIterator::SELF_FIRST);
        $iterator = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($iterator as $file) {
            try {
                if (isset($file[ 0 ])) {
                    $file = $file[ 0 ];
                    $this->message('processing', false, $file);
                    $this->slasher->addBackslashes($file);
                    $this->message('done', $file);
                } else {
                    echo $file;
                    exit;
                }

            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        }

        $this->message('appDone', true, $this->app);
    }

    /**
     * parses a file
     */
    protected function parseFile()
    {
        $file = $this->file;
        $this->message('processing', false, $file);

        try {
            $this->slasher->addBackslashes($file);
        } catch (Exception $e) {
        }
        $this->message('done', true, $file);
    }
}

if (!empty($argc)) {
    //php slasher.php file/dir(the filename or directory you want to parse) -all(slashes all internal php functions)
    // -use(makes them into use function instead of slashing methods, but constants still get slashed).
    try {
        $slasher = new Slasher($argv);
        $slasher->execute();
    } catch (Exception $e) {
        echo 'An exception has occurred!';
        echo PHP_EOL;
        echo $e->getMessage();
        echo PHP_EOL;
        exit;
    }
}
