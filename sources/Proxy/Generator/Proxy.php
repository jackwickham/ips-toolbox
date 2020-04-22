<?php

/**
 * @brief      Proxy Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */

namespace IPS\toolbox\Proxy\Generator;

use Exception;
use IPS\Data\Store;
use IPS\IPS;
use IPS\Patterns\Bitwise;
use IPS\toolbox\Application;
use IPS\toolbox\Generator\DTClassGenerator;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Proxy\Proxyclass;
use IPS\toolbox\ReservedWords;
use IPS\toolbox\Shared\Write;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

use function array_filter;
use function array_merge;
use function array_shift;
use function class_exists;
use function constant;
use function count;
use function defined;
use function explode;
use function file_exists;
use function file_get_contents;
use function header;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_dir;
use function is_float;
use function is_int;
use function is_numeric;
use function json_decode;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function method_exists;
use function preg_match;
use function preg_match_all;
use function property_exists;
use function str_replace;
use function token_get_all;
use function trim;

use const T_ABSTRACT;
use const T_CLASS;
use const T_FINAL;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_WHITESPACE;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Proxy Class
 *
 * @mixin Proxy
 */
class _Proxy extends GeneratorAbstract
{

    use Write;

    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;

    /**
     * helperClass stores
     *
     * @var array
     */
    protected $helperClasses = [];

    /**
     * if a ar relations.json exist, it will attempt to rebuild the model proxy class if a new field is added.
     *
     * @param $table
     */
    public static function adjustModel($table)
    {
        $apps = Application::applications();
        $relations = [[]];
        foreach ($apps as $app) {
            $dir = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/arRelations.json';
            if (file_exists($dir)) {
                $relations[] = json_decode(file_get_contents($dir), true);
            }
        }

        $relations = array_merge(...$relations);

        if (isset($relations[$table])) {
            $class = \IPS\ROOT_PATH . '/' . $relations[$table];

            if (file_exists($class)) {
                $content = file_get_contents($class);
                static::i()->create($content);
            }
        }
    }

    /**
     * @param $content
     */
    public function create(string $content)
    {
        try {
            $data = $this->tokenize($content);
            if (isset($data['class'], $data['namespace'])) {
                preg_match('#\$bitOptions#', $content, $bitOptions);
                $namespace = $data['namespace'];
                $ns2 = explode('\\', $namespace);
                array_shift($ns2);
                $app = array_shift($ns2);
                $isApp = false;
                $appPath = \IPS\ROOT_PATH . '/applications/' . $app;

                if ($app && is_dir($appPath)) {
                    $isApp = true;
                }

                $ipsClass = $data['class'];

                if (($namespace === 'IPS' && $ipsClass === '_Settings') || mb_strpos(
                    $namespace,
                    'IPS\convert'
                ) !== false) {
                    return;
                }

                $first = mb_substr($ipsClass, 0, 1);
                if ($first === '_') {
                    $class = mb_substr($ipsClass, 1);

                    if (ReservedWords::check($class)) {
                        return;
                    }

                    $type = '';
                    $body = [];
                    $classDefinition = [];
                    $classBlock = null;

                    $extraPath = $isApp ? $app : 'system';
                    $path = $this->save . '/class/' . $extraPath . '/';
                    $alt = str_replace(
                        [
                            "\\",
                            ' ',
                            ';',
                        ],
                        '_',
                        $namespace
                    );
                    $file = $alt . '_' . $class . '.php';

                    if ($data['final']) {
                        $type = 'final ';
                    }

                    if ($data['abstract']) {
                        $type = 'abstract ';
                    }

                    $new = new ClassGenerator();
                    $new->setName($class);
                    $f = explode("\n", $content);

                    foreach ($f as $l) {
                        preg_match('#^use\s(.*?);$#', $l, $match);
                        if (isset($match[1])) {
                            // $new->addUse($match[ 1 ]);
                        }
                    }
                    $new->setNamespaceName($namespace);
                    $new->setExtendedClass($namespace . '\\' . $ipsClass);
                    $this->cache->addClass($namespace . '\\' . $class);
                    $this->cache->addNamespace($namespace);
                    if ($type === 'abstract') {
                        $new->setAbstract(true);
                    }

                    if ($type === 'final') {
                        $new->setFinal(true);
                    }
                    if (isset($bitOptions[0])) {
                        $reflect = new ReflectionClass(
                            $data['namespace'] . '\\' . str_replace('_', '', $data['class'])
                        );
                        $bits = $reflect->getProperty('bitOptions');
                        $bits->setAccessible(true);

                        if ($bits->isStatic()) {
                            $bt = $bits->getValue();

                            if (is_array($bt)) {
                                foreach ($bt as $key => $value) {
                                    foreach ($value as $k => $v) {
                                        $classDefinition[] = [
                                            'pt'   => 'p',
                                            'prop' => $k,
                                            'type' => Bitwise::class,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    if (Proxyclass::i()->doProps) {
                        /* @var ActiveRecord $dbClass */
                        $dbClass = $namespace . '\\' . $class;
                        try {
                            if (property_exists($dbClass, 'databaseTable') && class_exists($dbClass) && method_exists(
                                $dbClass,
                                'db'
                            )) {
                                $table = $dbClass::$databaseTable;
                                if ($table && $dbClass::db()->checkForTable($table)) {
                                    /* @var array $definitions */
                                    $definitions = $dbClass::db()->getTableDefinition($table);

                                    if (isset($definitions['columns'])) {
                                        /* @var array $columns */
                                        $columns = $definitions['columns'];
                                        $len = mb_strlen($dbClass::$databasePrefix);
                                        foreach ($columns as $key => $val) {
                                            if ($len && 0 === mb_strpos($key, $dbClass::$databasePrefix)) {
                                                $key = mb_substr($key, $len);
                                            }
                                            $key = trim($key);
                                            $this->buildHead($key, $val, $classDefinition);
                                        }
                                    }

                                    $this->buildProperty($dbClass, $classDefinition);
                                }
                            }
                        } catch (Exception $e) {
                        }

                        $this->runHelperClasses($dbClass, $classDefinition, $ipsClass, $body);

                        $classBlock = $this->buildClassDoc($classDefinition);
                    }

                    if (is_array($body)) {
                        $newMethods = [];
                        foreach ($body as $method) {
                            if ($method instanceof MethodGenerator) {
                                $newMethods[$method->getName()] = $method;
                            }

                            if ($method instanceof PropertyGenerator) {
                                $new->addPropertyFromGenerator($method);
                            }
                        }

                        if (count($newMethods)) {
                            $new->addMethods($newMethods);
                        }
                    }

                    if ($classBlock instanceof DocBlockGenerator) {
                        $new->setDocBlock($classBlock);
                    }

                    $proxyFile = new DTFileGenerator;
                    $proxyFile->isProxy = true;
                    $proxyFile->setClass($new);
                    $proxyFile->setFilename($path . '/' . $file);
                    $proxyFile->write();
                }
            }
        } catch (Exception $e) {
            //            Debug::add( 'Proxy Create', $e );
        }
    }

    /**
     * returns the class and namespace
     *
     * @param $source
     *
     * @return array|null
     */
    public function tokenize($source)
    {
        $namespace = 0;
        $tokens = token_get_all($source);
        $count = count($tokens);
        $dlm = false;
        $final = false;
        $abstract = false;

        for ($i = 2; $i < $count; $i++) {
            if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] === 'phpnamespace' || $tokens[$i - 2][1] === 'namespace')) || ($dlm && $tokens[$i - 1][0] === T_NS_SEPARATOR && $tokens[$i][0] === T_STRING)) {
                if (!$dlm) {
                    $namespace = 0;
                }
                if (isset($tokens[$i][1])) {
                    $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                    $dlm = true;
                }
            } elseif ($dlm && ($tokens[$i][0] !== T_NS_SEPARATOR) && ($tokens[$i][0] !== T_STRING)) {
                $dlm = false;
            }

            if ($tokens[$i][0] === T_FINAL) {
                $final = true;
            }

            if ($tokens[$i][0] === T_ABSTRACT) {
                $abstract = true;
            }

            if (($tokens[$i - 2][0] === T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] === 'phpclass')) && $tokens[$i - 1][0] === T_WHITESPACE && $tokens[$i][0] === T_STRING) {
                $class = $tokens[$i][1];

                return [
                    'namespace' => $namespace,
                    'class'     => $class,
                    'abstract'  => $abstract,
                    'final'     => $final,
                ];
            }
        }

        return null;
    }

    /**
     * builds the docblock for proxy props
     *
     * @param $name
     * @param $def
     * @param $classDefinition
     *
     * @return void
     */
    protected function buildHead($name, $def, &$classDefinition)
    {
        $ints = [
            'TINYINT',
            'SMALLINT',
            'MEDIUMINT',
            'INT',
            'BIGINT',
            'DECIMAL',
            'FLOAT',
            'BIT',
        ];

        $comment = null;

        if ($def['comment']) {
            $comment = $def['comment'];
        }

        $type = null;

        if (in_array($def['type'], $ints, true)) {
            $type = 'int';
        } else {
            $type = 'string';
        }

        if ($def['allow_null']) {
            $type .= '|null';
        }

        $classDefinition[$name] = ['pt' => 'p', 'prop' => $name, 'type' => $type, 'comment' => $comment];
    }

    /**
     * builds props out of the setters and getters
     *
     * @param $class
     * @param $classDefinition
     */
    public function buildProperty($class, &$classDefinition)
    {
        try {
            $data = [];
            $reflect = new ReflectionClass($class);
            $methods = $reflect->getMethods();
            if (empty($methods) !== true) {
                foreach ($methods as $method) {
                    $type = trim(mb_substr($method->name, 0, 4));
                    $key = trim(mb_substr($method->name, 4, mb_strlen($method->name)));
                    if ($type === 'set_' || $type === 'get_') {
                        $pt = null;
                        if (!isset($data[$key]) && !isset($classDefinition[$key])) {
                            if ($type === 'set_') {
                                $pt = 'w';
                            }

                            if ($type === 'get_') {
                                $pt = 'r';
                            }
                        } else {
                            $pt = 'p';
                        }

                        $comment = null;
                        $return = $type === 'set_' ? 'void' : 'string';
                        if ($method->hasReturnType()) {
                            $return = (string) $method->getReturnType();
                        } else {
                            $doc = $method->getDocComment();
                            preg_match_all('#@return([^\n]+)?#', $doc, $match);

                            if (isset($match[1][0])) {
                                $match = array_filter(explode(' ', $match[1][0]));
                                $mtype = trim(array_shift($match));
                                if (is_array($match) && count($match)) {
                                    $comment = implode(' ', $match);
                                }

                                $return = $mtype;
                            }
                        }

                        if (isset($data[$key])) {
                            if ($return === 'void' || $data[$key]['type'] !== 'void') {
                                $return = $data[$key]['type'];
                            }
                        }

                        $data[$key] = [
                            'prop'    => trim($key),
                            'pt'      => $pt,
                            'type'    => $return,
                            'comment' => $comment,
                        ];
                    }
                }

                foreach ($data as $prop => $value) {
                    $classDefinition[$prop] = $value;
                }
            }
        } catch (Exception $e) {
            //            Debug::add( 'class', $e );
        }
    }

    /**
     * if there is a helper class, will run it here.
     *
     * @param $class
     * @param $classDoc
     * @param $classExtends
     * @param $body
     */
    protected function runHelperClasses($class, &$classDoc, &$classExtends, &$body)
    {
        $helpers = [];

        try {
            if (empty($this->helperClasses) === true) {
                /* @var Application $app */
                foreach (Application::appsWithExtension('toolbox', 'ProxyHelpers') as $app) {
                    $extensions = $app->extensions('toolbox', 'ProxyHelpers', true);
                    foreach ($extensions as $extension) {
                        if (method_exists($extension, 'map')) {
                            $extension->map($helpers);
                        }
                    }
                }

                $this->helperClasses = $helpers;
                //                Debug::add( 'helperClasses', $this->helperClasses, true );
            }
            if (isset($this->helperClasses[$class]) && is_array($this->helperClasses[$class])) {
                /* @var HelpersAbstract $helperClass */
                foreach ($this->helperClasses[$class] as $helper) {
                    $helperClass = new $helper;
                    $helperClass->process($class, $classDoc, $classExtends, $body);
                }
            }
        } catch (Exception $e) {
            //            Debug::add( 'helpers', $e );
        }
    }

    /**
     * @param array $properties
     *
     * @return mixed
     */
    public function buildClassDoc(array $properties)
    {
        $done = [];
        $block = [];
        foreach ($properties as $key => $property) {
            try {
                if (!isset($done[$property['prop']])) {
                    if (class_exists($property['type'])) {
                        $property['type'] = '\\' . $property['type'];
                    }
                    $done[$property['prop']] = 1;
                    $comment = $property['comment'] ?? '';
                    $content = $property['type'] . ' $' . $property['prop'] . ' ' . $comment;
                    $pt = 'property';
                    switch ($property['pt']) {
                        case 'p':
                            $pt = 'property';
                            break;
                        case 'w':
                            $pt = 'property-write';
                            break;
                        case 'r':
                            $pt = 'property-read';
                    }
                    $block[] = new GenericTag($pt, $content);
                }
            } catch (Exception $e) {
            }
        }

        $docBlock = new DocBlockGenerator();
        $docBlock->setTags($block);

        return $docBlock;
    }

    /**
     * takes the settings from store and creates proxy props for them, so they will autocomplete
     */
    public function generateSettings()
    {
        try {
            $classDoc = [];

            /**
             * @var array $load
             */
            $load = Store::i()->settings;
            foreach ($load as $key => $val) {
                if (is_array($val)) {
                    $type = 'array';
                } elseif (is_int($val)) {
                    $type = 'int';
                } elseif (is_float($val)) {
                    $type = 'float';
                } elseif (is_bool($val)) {
                    $type = 'bool';
                } else {
                    $type = 'string';
                }

                $classDoc[] = ['pt' => 'p', 'prop' => $key, 'type' => $type];
            }

            $header = $this->buildClassDoc($classDoc);
            $class = new DTClassGenerator();
            $class->setNamespaceName('IPS');
            $class->setName('Settings');
            $class->setExtendedClass('IPS\_Settings');
            $class->setDocBlock($header);
            $file = new DTFileGenerator;
            $file->setClass($class);
            $file->setFilename($this->save . '/IPS_Settings.php');
            $file->write();
        } catch (Exception $e) {
        }
    }

    /**
     * builds the constants out since they are a mapped array in init.php
     */
    public function buildConstants()
    {
        if (Proxyclass::i()->doConstants) {
            $load = IPS::defaultConstants();
            $extra = "\n";
            foreach ($load as $key => $val) {
                $vals = null;
                if (defined($key)) {
                    $vals = constant($key);
                }

                if (is_bool($val)) {
                    $vals = (int) $vals;
                    $val = $vals === 1 ? 'true' : 'false';
                } elseif (!is_numeric($val)) {
                    $val = "'" . $val . "'";
                }

                $extra .= 'define( "\\IPS\\' . $key . '",' . $val . ");\n";
            }
            $extra .= <<<eof
/**
 * @param string \$text
 * @return string
 */            
function mb_ucfirst(\$text)
{

}
eof;

            $file = new DTFileGenerator;
            $file->setBody($extra);
            $this->_writeFile('IPS_Constants.php', $file->generate(), $this->save, false);
        }
    }
}
