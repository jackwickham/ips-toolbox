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
use InvalidArgumentException;
use IPS\Application;
use IPS\toolbox\Generator\DTClassGenerator;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Generator\DTInterfaceGenerator;
use IPS\toolbox\Generator\DTTraitGenerator;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Shared\LanguageBuilder;
use IPS\toolbox\Shared\Magic;
use IPS\toolbox\Shared\ModuleBuilder;
use IPS\toolbox\Shared\SchemaBuilder;
use IPS\toolbox\Shared\Write;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\RuntimeException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
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
use function ltrim;
use function mb_strtolower;
use function mb_ucfirst;
use function str_replace;
use function trim;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

\IPS\toolbox\Application::loadAutoLoader();

/**
 * Class _GeneratorAbstract
 *
 * @package IPS\toolbox\DevCenter\Sources\Generator
 * @mixin \IPS\toolbox\DevCenter\Sources\Generator\GeneratorAbstract
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
    public $error = \false;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var DTClassGenerator|DTInterfaceGenerator|DTTraitGenerator
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
    protected $useImports = \true;

    /**
     * @param array       $values
     * @param Application $application
     * @param bool        $strip
     */
    public function __construct( array $values, Application $application, bool $strip = \false )
    {

        foreach ( $values as $key => $val ) {
            if ( $strip === \false ) {
                $key = str_replace( 'dtdevplus_class_', '', $key );
            }

            $val = !is_array( $val ) ? trim( $val ) : $val;
            if ( !empty( $val ) ) {
                $this->{$key} = $val;
            }
            else {
                $this->{$key} = \null;
            }
        }

        $this->application = $application;
        $this->app = $this->application->directory;
        $this->type = mb_ucfirst( $this->type );
        if ( in_array( $this->type, static::$arDescendent, \true ) ) {
            if ( $this->database === \null ) {
                $this->database = $this->app . '_' . $this->classname_lower;
            }
            else {
                $this->database = $this->app . '_' . $this->database;
            }

            $this->database = mb_strtolower( $this->database );
        }

        if ( $this->prefix !== \null ) {
            $this->prefix .= '_';
        }

        $this->db = new Database( $this->database, $this->prefix );

        if ( !in_array( $this->type, [ 'Traits', 'Interfacing' ], \true ) ) {
            $this->generator = new DTClassGenerator;
        }
        else if ( $this->type === 'Interfacing' ) {
            $this->generator = new DTInterfaceGenerator;
        }
        else if ( $this->type === 'Traits' ) {
            $this->generator = new DTTraitGenerator;
        }
    }

    /**
     * gathers all the info neeed to begin class building.
     */
    final public function process()
    {


        if ( $this->className !== \null ) {
            $this->classname = mb_ucfirst( $this->className );
        }
        else if ( $this->interfaceName !== \null ) {
            $this->classname = mb_ucfirst( $this->interfaceName );
        }
        else if ( $this->traitName !== \null ) {
            $this->classname = mb_ucfirst( $this->traitName );
        }
        else {
            $this->classname = 'Forms';
        }

        $this->classname_lower = mb_strtolower( $this->classname );

        if ( !in_array( $this->type, [ 'Traits', 'Interfacing' ], \true ) ) {
            $this->_classname = '_' . $this->classname;
        }
        else {
            $this->_classname = $this->classname;
        }

        if ( mb_strtolower( $this->namespace ) === $this->classname_lower ) {
            $this->namespace = 'IPS\\' . $this->app;
        }
        else {
            $this->namespace = $this->namespace !== \null ? 'IPS\\' . $this->app . '\\' . mb_ucfirst( $this->namespace ) : 'IPS\\' . $this->app;
        }

        if ( !in_array( $this->type, static::$arDescendent, \true ) && !in_array( $this->type, [
                'Traits',
                'Interfacing',
                'Singleton',
                'Form',
            ], \true ) ) {
            $methodDocBlock = DocBlockGenerator::fromArray( [
                'shortDescription' => $this->_classname . ' constructor',
                'longDescription'  => \null,
            ] );

            $this->methods[] = MethodGenerator::fromArray( [
                'name'     => '__construct',
                'body'     => $this->extends ? 'parent::__construct();' : '',
                'docblock' => $methodDocBlock,
            ] );
        }

        if ( in_array( $this->type, static::$arDescendent, \true ) ) {
            $this->_arDescendantProps();
        }

        $this->bodyGenerator();

        if ( $this->extends !== \null ) {
            if ( $this->useImports ) {
                $this->extends = ltrim( $this->extends, '\\' );
                $this->generator->addUse( $this->extends );
            }
            $this->generator->setExtendedClass( $this->extends );
        }

        if ( is_array( $this->implements ) && count( $this->implements ) ) {
            $new = [];
            foreach ( $this->implements as $int ) {
                if ( $this->useImports ) {
                    $int = ltrim( $int, '\\' );
                    $this->generator->addUse( $int );
                }
                $new[] = $int;
            }

            $this->generator->setImplementedInterfaces( $new );
        }

        if ( is_array( $this->traits ) && count( $this->traits ) ) {
            foreach ( $this->traits as $trait ) {
                if ( $this->useImports ) {
                    $trait = ltrim( $trait, '\\' );
                    $this->generator->addUse( $trait );
                }
                $this->generator->addTrait( $trait );
            }
        }

        $this->mixin = '\\' . $this->namespace . '\\' . $this->classname;

        $headerBlock = DocBlockGenerator::fromArray( [
            'tags' => [
                [ 'name' => 'brief', 'description' => $this->classname . ' ' . $this->brief ],
                [ 'name' => 'copyright', 'description' => '-storm_copyright-' ],
                [ 'name' => 'package', 'description' => 'IPS Social Suite' ],
                [ 'name' => 'subpackage', 'description' => $this->app ],
                [ 'name' => 'since', 'description' => '-storm_since_version-' ],
                [ 'name' => 'version', 'description' => '-storm_version-' ],
            ],
        ] );

        $docBlock = DocBlockGenerator::fromArray( [
            'shortDescription' => $this->classname . ' Class',
            'longDescription'  => \null,
            'tags'             => [ [ 'name' => 'mixin', 'description' => $this->mixin ] ],
        ] );

        $this->generator->setName( $this->_classname )->setDocBlock( $docBlock )->setNamespaceName( $this->namespace );

        if ( $this->abstract ) {
            $this->generator->setAbstract( \true );
        }

        if ( !empty( $this->methods ) ) {
            $this->generator->addMethods( $this->methods );
        }

        $content = new DTFileGenerator;
        $content->setDocBlock( $headerBlock );
        $content->setClass( $this->generator );
        $dir = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/sources/' . $this->_getDir();
        $file = $this->classname . '.php';

        if ( !in_array( $this->type, [ 'Interface', 'Traits' ] ) ) {
            $this->proxy = \true;
        }

        $content->setFilename( $dir . '/' . $file );

        try {
            $content->write();


            if ( $this->scaffolding_create && in_array( $this->type, static::$arDescendent, \true ) ) {
                $this->_createRelation( $file, $dir, $this->database );

                try {
                    if ( in_array( $this->type, static::$arDescendent, \true ) ) {
                        $this->db->add( 'bitwise' );
                    }
                    $this->db->createTable()->_buildSchemaFile( $this->database, $this->application );
                } catch ( Exception $e ) {
                    Debug::add( 'DevPlus Database Compiler', $e );
                }

                try {
                    $this->_buildModule( $this->application, $this->classname, $this->namespace, $this->type, $this->useImports );
                } catch ( Exception $e ) {
                    //@todo maybe we should add a error class?
                    $this->error = 1;
                    Debug::add( 'modules', $e );
                }
            }
        } catch ( RuntimeException $e ) {
            $this->error = 1;
            Debug::add( 'modules', $e );
        }
    }

    /**
     * builds the $databasePrefix section for AR descendant classes
     */
    protected function _arDescendantProps()
    {
        //multitons
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => '[ActiveRecord] Multiton Store' ],
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => 'multitons',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY_LONG, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );

        //default values
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => '[ActiveRecord] Default Values' ],
                [ 'name' => 'var', 'description' => 'null|array' ],
            ],
        ];

        //        $config = [
        //            'name'   => 'defaultValues',
        //            'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL ),
        //            'vis'    => 'protected',
        //            'doc'    => $doc,
        //            'static' => \true,
        //        ];

        $this->addProperty( $config );

        //prefix
        if ( $this->prefix ) {
            $this->prefix = mb_strtolower( $this->prefix );
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[ActiveRecord] Database Prefix' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'databasePrefix',
                'value'  => $this->prefix,
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        }

        //databaseTable
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => '[ActiveRecord] Database table' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'databaseTable',
            'value'  => $this->database,
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );

        //bitoptions
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Bitwise Keys' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'bitOptions',
            'value'  => [ 'bitwise' => [ 'bitwise' => [] ] ],
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    /**
     * @param array $config [
     *                      'name' => 'itemClass', name of the property (required)
     *                      'value' => 'foo::bar', default value of the property
     *                      'doc' => [], doc Comment of the property @see
     *                      \Zend\Code\Generator\DocBlockGenerator::fromArray
     *                      'vis' => 'protected', the visibility of the prop, public, private, protected
     *                      'static' => (bool), true or false
     *                      ]
     */
    protected function addProperty( array $config = [] )
    {
        try {
            if ( !isset( $config[ 'name' ] ) ) {
                throw new InvalidArgumentException( 'array missing name or name value is null' );
            }
            $config[ 'defaultvalue' ] = $config[ 'value' ] ?? \null;
            if ( !empty( $config[ 'doc' ] ) ) {
                $config[ 'docblock' ] = DocBlockGenerator::fromArray( $config[ 'doc' ] );
                unset( $config[ 'doc' ] );
            }
            $config[ 'visibility' ] = $config[ 'vis' ] ?? \false;
            $config[ 'static' ] = $config[ 'static' ] ?? \false;
            $prop = PropertyGenerator::fromArray( $config );
            $this->generator->addPropertyFromGenerator( $prop );
        } catch ( \Exception $e ) {
            Debug::add( 'addProperty', $e );
        }
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
        $namespace = explode( '\\', $this->namespace );
        array_shift( $namespace );
        array_shift( $namespace );
        $namespace = implode( '/', $namespace );

        if ( empty( $namespace ) ) {
            return $this->classname;
        }

        return $namespace;
    }

    /**
     * @param $file
     * @param $dir
     * @param $database
     */
    protected function _createRelation( $file, $dir, $database )
    {
        $relationFile = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/data/';
        $relations = [];
        if ( file_exists( $relationFile . '/arRelations.json' ) ) {
            $relations = json_decode( file_get_contents( $relationFile . '/arRelations.json' ), \true );
        }
        $relations[ $database ] = str_replace( \IPS\ROOT_PATH . '/', '', $dir ) . '/' . $file;
        $this->_writeFile( 'arRelations.json', json_encode( $relations ), $relationFile, \false );
    }

    /**
     * adds the seoTitleColumn property
     */
    protected function seoTitleColumn()
    {
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'SEO Title Column' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'seoTitleColumn',
            'value'  => new PropertyValueGenerator( 'seoTitle', PropertyValueGenerator::TYPE_STRING, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    /**
     * adds the URL base property
     */
    protected function urlBase()
    {
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'URL Base' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $base = 'app=' . $this->app . '&module=' . $this->classname_lower . '&controller=' . $this->classname_lower . '&id=';

        $config = [
            'name'   => 'urlBase',
            'value'  => new PropertyValueGenerator( $base, PropertyValueGenerator::TYPE_STRING ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    /**
     * adds the _url property
     */
    protected function _url()
    {
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Cached URL' ],
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => '_url',
            'value'  => new PropertyValueGenerator( [], PropertyValueGenerator::TYPE_ARRAY_LONG ),
            'vis'    => 'protected',
            'doc'    => $doc,
            'static' => \false,
        ];

        $this->addProperty( $config );
    }

    /**
     * adds the url template property
     */
    protected function urlTemplate()
    {
        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'URL Furl Template' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'urlTemplate',
            'value'  => new PropertyValueGenerator( $this->app . '_' . $this->classname_lower, PropertyValueGenerator::TYPE_STRING ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }
}
