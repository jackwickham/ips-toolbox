<?php

/**
 * @brief       ClassGenerator Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace Generator\Builders;

use Generator\Builders\Traits\ClassMethods;
use Generator\Builders\Traits\Constants;
use Generator\Builders\Traits\Imports;
use Generator\Builders\Traits\Properties;
use IPS\babble\Profiler\Debug;
use ReflectionClass;
use ReflectionNamedType;

use function count;
use function is_array;
use function is_numeric;

use function array_pop;
use function class_exists;
use function explode;
use function implode;
use function in_array;
use function is_string;
use function ltrim;
use function mb_strpos;
use function mb_strtolower;
use function md5;
use function rand;
use function random_int;
use function str_replace;
use function time;
use function token_get_all;
use function trim;
use function var_export;
use const T_ARRAY;
use const T_CONSTANT_ENCAPSED_STRING;
use const T_LNUMBER;
use const T_OPEN_TAG;
use const T_STRING;
use const T_VARIABLE;


/**
 * Class ClassGenerator
 *
 * @package Generator
 */
class ClassGenerator extends GeneratorAbstract
{

    use Properties, Constants, ClassMethods, Imports;

    /**
     * an array of implements
     *
     * @var array
     */
    protected $interfaces = [];

    /**
     * the parent class
     *
     * @var string
     */
    protected $extends;

    /**
     * class type, final/abstract
     *
     * @var string
     */
    protected $type;

    /**
     * an array of traits class uses
     *
     * @var array
     */
    protected $classUses = [];

    protected $doImports = true;

    /**
     * an array of methods to replace in class
     *
     * @var array
     */
    protected $replaceMethods = [];

    /**
     * an array of methods to remove from class
     *
     * @var array
     */
    protected $removeMethods = [];

    protected $beforeLines = [];

    protected $afterLines = [];

    protected $replaceLines = [];

    protected $startOfMethods = [];

    protected $afterMethod = [];

    protected $endofMethods = [];

    protected $final = false;

    protected $abstract = false;

    public static function convertValue($value)
    {
        if (is_array($value)) {
            $return = var_export($value, true);

            if (\count($value) >= 1) {
                $string = explode("\n", $return);
                $return = '';
                $i = 0;
                foreach ($string as $item) {
                    if ($i !== 0) {
                        $return .= '    ';
                    }

                    $return .= $item . "\n";
                    $i++;
                }
            } else {
                $return = 'array()';
            }

            return $return;
        } else {
            $value = trim($value);
        }

        if ((int)$value || is_numeric($value)) {
            return $value;
        }

        if ($value === false || $value === 'false') {
            return 'false';
        }

        if ($value === true || $value === 'true') {
            return 'true';
        }
        if ($value === null || mb_strtolower($value) === 'null') {
            return 'null';
        }

        if (mb_strpos($value, '"') === 0 || mb_strpos($value, "'") === 0 || mb_strpos($value, '[') === 0 || mb_strpos(
                $value,
                'array'
            ) === 0 || mb_strpos($value, '::') !== false) {
            return $value;
        }

        return "'" . $value . "'";
    }

    public static function paramsFromString($params)
    {
        $continue = true;
        $rand = 'foo' . random_int(1, 20000) . random_int(1, 20000) . random_int(1, 30000) . md5(
                time() + rand(1, 10000)
            );
        $newParams = [];
        $class = <<<EOF
class {$rand} {
    public function foo({$params}){}
}
EOF;
        if (!class_exists($rand) && eval($class) === \false) {
            $continue = \false;
        }

        if ($continue) {
            $reflection = new ReflectionClass($rand);
            $methods = $reflection->getMethods();
            foreach ($methods as $method) {
                $params = $method->getParameters();
                $newParams = [];
                /** @var \ReflectionParameter $param */
                foreach ($params as $param) {
                    $position = $param->getPosition();
                    $newParams[$position]['name'] = $param->getName();
                    $hint = $param->getType();
                    if ($hint instanceof ReflectionNamedType) {
                        $newParams[$position]['hint'] = $hint->getName();
                        $newParams[$position]['nullable'] = (bool)$hint->allowsNull();
                    }

                    if ($param->isPassedByReference()) {
                        $newParams[$position]['reference'] = true;
                    }
                    $value = 'none';
                    if ($param->isDefaultValueAvailable() === true) {
                        if ($param->isDefaultValueConstant()) {
                            $value = $param->getDefaultValueConstantName();
                        } else {
                            $value = $param->getDefaultValue();
                            if (is_string($value) === true) {
                                $value = "'" . $value . "'";
                            }
                            if (is_string($value) && $value === '') {
                                $value = "''";
                            }
                        }
                    }

                    if ($value !== 'none') {
                        $newParams[$position]['value'] = $value;
                    }
                }
            }
        }

        return $newParams;
    }

    public static function paramFromString($param)
    {
        $sliced = <<<EOF
<?php

{$param}
EOF;

        $tokens = token_get_all($sliced);
        $count = count($tokens);
        $p = [];
        $hint = null;
        $in = [
            T_ARRAY,
            T_STRING,
            T_CONSTANT_ENCAPSED_STRING,
            T_LNUMBER,
        ];
        $i = 0;
        foreach ($tokens as $token) {
            if (isset($tokens[0]) && $tokens[0] !== T_OPEN_TAG) {
                $type = $token[0] ?? null;
                $value = $token[1] ?? $token;
                if ($value) {
                    if ($type === '[') {
                        $vv = '';
                        for ($ii = $i; $ii < $count; $ii++) {
                            $vv .= $tokens[$ii][1] ?? $tokens[$ii];
                        }

                        $p['value'] = trim($vv);
                    } elseif ($value === '&') {
                        $p['reference'] = true;
                    } elseif ($value === '?') {
                        $p['nullable'] = true;
                    } elseif (in_array($type, $in, true)) {
                        if ($type === T_ARRAY || (!isset($p['hint']) && !isset($p['value']) && !isset($p['name']))) {
                            $hint[] = $value;
                        } else {
                            if ($hint !== null) {
                                $p['hint'] = implode('\\', $hint);
                                $hint = null;
                            }
                            $p['value'] = trim($value);
                        }
                    } elseif ($type === T_VARIABLE) {
                        if ($hint !== null) {
                            $p['hint'] = implode('\\', $hint);
                            $hint = null;
                        }
                        $p['name'] = ltrim(trim($value), '$');
                    }
                }
            }
            $i++;
        }

        return $p;
    }

    public function addType($type)
    {
        $this->type = $type;
    }

    public function makeFinal()
    {
        $this->final = true;
    }

    public function makeAbstract()
    {
        $this->abstract = true;
    }

    public function disableImports()
    {
        $this->doImports = false;
    }

    public function addUse($class)
    {
        if (is_array($class)) {
            $og = $class;
            $class = implode('\\', $class);
        } else {
            $og = explode('\\', $class);
        }
        if (\count($og) >= 2) {
            $class = $this->addImport($class);
        }
        $hash = $this->hash($class);
        $this->classUses[$hash] = $class;
    }

    public function getClassUses()
    {
        return $this->classUses;
    }

    public function writeSourceType()
    {
        $type = null;

        if ($this->isAbstract() === true) {
            $type = 'abstract ';
        }

        if ($this->isFinal() === true) {
            $type = 'final ';
        }

        $this->output("\n{$type}class {$this->className}");
        if ($this->isHook === true) {
            $this->output(' extends _HOOK_CLASS_');
        }
        if ($this->extends) {
            $this->output(" extends {$this->extends}");
        }

        if (empty($this->interfaces) !== true) {
            $this->output(" implements \n" . implode(",\n", $this->interfaces));
        }
        $this->output("\n{");
    }

    public function isAbstract()
    {
        return $this->abstract;
    }

    public function isFinal()
    {
        return $this->final;
    }

    /**
     * @param $extends
     *
     * @return $this
     */
    public function addExtends($extends, $import = true)
    {
        if (is_array($extends)) {
            $og = $extends;
            $extends = implode('\\', $extends);
        } else {
            $og = explode('\\', $extends);
        }
        if ($import === true && $this->doImports === true && \count($og) >= 2) {
            $this->addImport($extends);
            $extends = array_pop($og);
        }

        $this->extends = $extends;
    }

    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * @param $interface
     *
     * @return $this
     */
    public function addInterface($interface)
    {
        if (is_array($interface)) {
            $og = $interface;
            $interface = implode('\\', $interface);
        } else {
            $og = explode('\\', $interface);
        }

        if ($this->doImports === true && \count($og) >= 2) {
            $this->addImport($interface);
            $interface = array_pop($og);
        }
        $hash = $this->hash($interface);
        $this->interfaces[$hash] = $interface;
    }

    protected function writeBody()
    {
        $tab = $this->tab;
        //psr-12 updates
        if (empty($this->classUses) === false) {
            foreach ($this->classUses as $use) {
                {
                    $this->output("\n\n{$tab}use ");
                    $this->output($use);
                    $this->output(";\n");
                }
            }
        }
        $this->writeConst();
        $this->writeProperties();
        $this->writeMethods();
        $this->output("\n}");
    }

    protected function tab2space($line)
    {
        return str_replace("\t", '    ', $line);
    }
}
