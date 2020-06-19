<?php

/**
 * @brief       GeneratorAbstract Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.2.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Sources\Generator;

use Exception;
use Generator\Builders\ClassGenerator;
use Generator\Builders\InterfaceGenerator;
use Generator\Builders\TraitGenerator;
use IPS\Application;
use IPS\Log;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Shared\LanguageBuilder;
use IPS\toolbox\Shared\Magic;
use IPS\toolbox\Shared\ModuleBuilder;
use IPS\toolbox\Shared\SchemaBuilder;
use IPS\toolbox\Shared\Write;

use function array_shift;
use function count;
use function defined;
use function explode;
use function file_exists;
use function file_get_contents;
use function header;
use function implode;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function mb_strtolower;
use function mb_ucfirst;
use function str_replace;
use function trim;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

\IPS\toolbox\Application::loadAutoLoader();

/**
 * Class _GeneratorAbstract
 *
 * @package IPS\toolbox\DevCenter\Sources\Generator
 * @mixin GeneratorAbstract
 * @property string $className
 * @property string $classname
 * @property string $classname_lower
 * @property string $_classname
 * @property string $namespace
 * @property string $extends
 * @property array $implements
 * @property array $traits
 * @property bool $abstract
 * @property string $scaffolding_type
 * @property string $brief
 * @property string $content_item_class
 * @property string $item_node_class
 * @property string $comment_class
 */
abstract class _GeneratorAbstract
{

    use LanguageBuilder, SchemaBuilder, ModuleBuilder, Write, Magic;

    /**
     * activerecord descendants
     *
     * @var array
     */
    protected static $arDescendent = [
        'Activerecord',
        'Node',
        'Item',
        'Comment',
        'Review',
    ];

    /**
     * if the scaffolding code throws any errors
     *
     * @var bool
     */
    public $error = false;

    /**
     * @var \IPS\Application
     */
    protected $application;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var ClassGenerator|InterfaceGenerator|TraitGenerator
     */
    protected $generator;

    /**
     * methods that need to be added to the class
     *
     * @var array
     */
    protected $methods = [];

    /**
     * are imports to be used instead of FQN
     *
     * @var bool
     */
    protected $useImports = true;

    protected $type;

    protected $app;

    protected $database;

    protected $prefix;

    protected $mixin;

    protected $baseurl;

    /**
     * @param array $values
     * @param Application $application
     * @param bool $strip
     */
    public function __construct(array $values, Application $application, bool $strip = false)
    {
        foreach ($values as $key => $val) {
            if ($strip === false) {
                $key = str_replace('dtdevplus_class_', '', $key);
            }

            $val = !is_array($val) ? trim($val) : $val;
            if (!empty($val)) {
                $this->{$key} = $val;
            } else {
                $this->{$key} = null;
            }
        }

        if (is_array($this->ips_traits)) {
            $this->traits = is_array($this->traits) ? array_merge($this->traits, $this->ips_traits) : $this->ips_traits;
        }

        if (is_array($this->ips_implements)) {
            $this->implements = is_array($this->implements) ? array_merge(
                $this->implements,
                $this->ips_implements
            ) : $this->ips_implements;
        }

        $this->application = $application;
        $this->app = $this->application->directory;
        $this->type = mb_ucfirst($this->type);
        if (in_array($this->type, static::$arDescendent, true)) {
            if ($this->database === null) {
                $this->database = $this->app . '_' . $this->classname_lower;
            } else {
                $this->database = $this->app . '_' . $this->database;
            }

            $this->database = mb_strtolower($this->database);
        }

        if ($this->prefix !== null) {
            $this->prefix .= '_';
        }

        $this->db = new Database($this->database, $this->prefix);

        if (!in_array($this->type, ['Traits', 'Interfacing'], true)) {
            $this->generator = new ClassGenerator();
        } elseif ($this->type === 'Interfacing') {
            $this->generator = new InterfaceGenerator();
        } elseif ($this->type === 'Traits') {
            $this->generator = new TraitGenerator();
        }
    }

    /**
     * gathers all the info neeed to begin class building.
     */
    final public function process(): void
    {
        if ($this->className !== null) {
            $this->classname = mb_ucfirst($this->className);
        } elseif ($this->interfaceName !== null) {
            $this->classname = mb_ucfirst($this->interfaceName);
        } elseif ($this->traitName !== null) {
            $this->classname = mb_ucfirst($this->traitName);
        } else {
            $this->classname = 'Forms';
        }

        $this->classname_lower = mb_strtolower($this->classname);

        if (!in_array($this->type, ['Traits', 'Interfacing'], true)) {
            $this->_classname = '_' . $this->classname;
        } else {
            $this->_classname = $this->classname;
        }

        if (mb_strtolower($this->namespace) === $this->classname_lower) {
            $this->namespace = 'IPS\\' . $this->app;
        } else {
            $this->namespace = $this->namespace !== null ? 'IPS\\' . $this->app . '\\' . mb_ucfirst(
                    $this->namespace
                ) : 'IPS\\' . $this->app;
        }

        if ($this->type !== 'Api' && !in_array($this->type, static::$arDescendent, true) && !in_array(
                $this->type,
                [
                    'Traits',
                    'Interfacing',
                    'Singleton',
                    'Form',
                ],
                true
            )) {
            $body = $this->extends ? 'parent::__construct();' : '';
            $config = [
                'visibility' => T_PUBLIC,
                'document' => [
                    $this->_classname . ' constructor',
                ],
            ];
            $this->generator->addMethod('__construct', $body, [], $config);
        }

        if (in_array($this->type, static::$arDescendent, true)) {
            $this->_arDescendantProps();
        }

        $this->bodyGenerator();

        if ($this->extends !== null) {
            $this->generator->addExtends($this->extends);
        }

        if (is_array($this->implements) && count($this->implements)) {
            foreach ($this->implements as $int) {
                $this->generator->addInterface($int);
            }
        }

        if (is_array($this->traits) && count($this->traits)) {
            foreach ($this->traits as $trait) {
                $this->generator->addUse($trait);
            }
        }

        $this->mixin = $this->classname;

        if ($this->type === 'Api') {
            $dir = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/api/';
        } else {
            $dir = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/sources/' . $this->_getDir();
        }
        $file = $this->classname . '.php';
        $this->proxy = true;

        if (!in_array($this->type, ['Interface', 'Traits'])) {
            $this->proxy = false;
        }
        $this->generator->addPath($dir);
        $this->generator->addFileName($file);
        $this->generator->isProxy = $this->proxy;
        $doc = [
            '@brief      ' . $this->classname . ' ' . $this->brief,
            '@copyright  -storm_copyright-',
            '@package    IPS Social Suite',
            '@subpackage ' . $this->app,
            '@since      -storm_since_version-',
            '@version    -storm_version-',
        ];

        $this->generator->addDocumentComment($doc);
        $this->generator->addDocumentComment([$this->classname . ' Class'], true);
        $this->generator->addMixin($this->mixin);
        $this->generator->addClassName($this->_classname);
        $this->generator->addFileName($this->classname);
        $this->generator->addNameSpace($this->namespace);

        if ($this->abstract) {
            $this->generator->addType('abstract');
        }

        try {
            $this->generator->save();
            if ($this->scaffolding_create && in_array($this->type, static::$arDescendent, false)) {
                $this->_createRelation($file, $dir, $this->database);
                if (in_array('db', $this->scaffolding_type, false)) {
                    try {
                        $this->db->add('bitwise');
                        $this->db->createTable()->_buildSchemaFile($this->database, $this->application);
                    } catch (Exception $e) {
                        Log::log($e, 'Devplus database');
                    }
                }

                if (in_array('modules', $this->scaffolding_type, false)) {
                    try {
                        $this->_buildModule(
                            $this->application,
                            $this->classname,
                            $this->namespace,
                            $this->type,
                            $this->useImports
                        );
                    } catch (Exception $e) {
                        //@todo maybe we should add a error class?
                        $this->error = 1;
                    }
                }
            }
        } catch (\RuntimeException $e) {
            $this->error = 1;
            Debug::log($e);
        }
    }

    /**
     * builds the $databasePrefix section for AR descendant classes
     */
    protected function _arDescendantProps(): void
    {
        //multitons
        $document = [
            '@brief [ActiveRecord] Multion Store',
            '@var  array',
        ];

        $this->generator->addProperty(
            'multitons',
            [],
            [
                'visibility' => T_PROTECTED,
                'document' => $document,
                'static' => true,
            ]
        );

        //prefix
        if ($this->prefix) {
            $this->prefix = mb_strtolower($this->prefix);
            $document = [
                '@brief [ActiveRecord] Database Prefix',
                '@var string',
            ];

            $this->generator->addProperty(
                'databasePrefix',
                $this->prefix,
                [
                    'visibility' => T_PUBLIC,
                    'document' => $document,
                    'static' => true,
                ]
            );
        }

        //databaseTable
        $document = [
            '@brief [ActiveRecord] Database table',
            '@var string',
        ];

        $this->generator->addProperty(
            'databaseTable',
            $this->database,
            [
                'visibility' => T_PUBLIC,
                'document' => $document,
                'static' => true,
            ]
        );

        //bitoptions
        $document = [
            '@brief [ActiveRecord] Bitwise Keys',
            '@var array',
        ];

        $value = <<<EOF
array(
        'bitwise' => array(
            'bitwise' => array()
        )
    )
EOF;

        $this->generator->addProperty(
            'bitOptions',
            $value,
            [
                'visibility' => T_PUBLIC,
                'document' => $document,
                'static' => true,
                'type' => 'array',
            ]
        );
    }

    /**
     * sets and gathers the class body blank
     */
    abstract protected function bodyGenerator();

    /**
     * gets the directory to store the class file to.
     *
     * @return array|mixed|string
     */
    protected function _getDir()
    {
        $namespace = explode('\\', $this->namespace);
        array_shift($namespace);
        array_shift($namespace);
        $namespace = implode('/', $namespace);

        if (empty($namespace)) {
            return $this->classname;
        }

        return $namespace;
    }

    /**
     * @param $file
     * @param $dir
     * @param $database
     */
    protected function _createRelation($file, $dir, $database): void
    {
        $relationFile = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/data/';
        $relations = [];
        if (file_exists($relationFile . '/arRelations.json')) {
            $relations = json_decode(file_get_contents($relationFile . '/arRelations.json'), true);
        }
        $relations[$database] = str_replace(\IPS\ROOT_PATH . '/', '', $dir) . '/' . $file;
        $this->_writeFile('arRelations.json', json_encode($relations), $relationFile, false);
    }

    /**
     * adds the seoTitleColumn property
     */
    protected function seoTitleColumn(): void
    {
        $doc = [
            '@brief SEO Title Column',
            '@var string',
        ];

        $this->generator->addProperty(
            'seoTitleColumn',
            'seoTitle',
            [
                'visibility' => T_PUBLIC,
                'document' => $doc,
                'static' => true,
            ]
        );
    }

    /**
     * adds the _url property
     */
    protected function _url(): void
    {
        $doc = [
            '@brief Cached URL',
            '@var array',
        ];
        $this->generator->addProperty(
            '_url',
            null,
            [
                'visibility' => T_PROTECTED,
                'document' => $doc,
            ]
        );
    }

    /**
     * adds the url template property
     */
    protected function urlTemplate(): void
    {
        $value = $this->app . '_' . $this->classname_lower;
        if ($this->baseurl === null) {
            $this->urlBase();
        }
        $this->addFurl($value, $this->baseurl);
        $doc = [
            '@brief URL Furl Template',
            '@var string',
        ];
        $this->generator->addProperty(
            'urlTemplate',
            $value,
            [
                'visibility' => T_PUBLIC,
                'document' => $doc,
                'static' => true,
            ]
        );
    }

    /**
     * adds the URL base property
     */
    protected function urlBase(): void
    {
        $base = 'app=' . $this->app . '&module=' . $this->classname_lower . '&controller=' . $this->classname_lower;
        $this->baseurl = $base;
        $doc = [
            '@brief URL base',
            '@var string',
        ];

        $this->generator->addProperty(
            'urlBase',
            $base . '&id=',
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function addFurl($value, $url)
    {
    }
}
