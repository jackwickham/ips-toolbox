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
use IPS\_Settings;
use IPS\Application;
use IPS\Data\Store;
use IPS\IPS;
use IPS\Patterns\ActiveRecord;
use IPS\Settings;
use IPS\toolbox\Generator\DTClassGenerator;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Proxy\Helpers\HelpersAbstract;
use IPS\toolbox\Proxy\Proxyclass;
use IPS\toolbox\ReservedWords;
use IPS\toolbox\Shared\Write;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use function array_filter;
use function array_merge;
use function array_shift;
use function class_exists;
use function explode;
use function file_exists;
use function file_get_contents;
use function header;
use function implode;
use function is_dir;
use function is_numeric;
use function json_decode;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
use function method_exists;
use function preg_match_all;
use function property_exists;
use function str_replace;
use function token_get_all;
use function trim;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Proxy Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Proxy
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
    public static function adjustModel( $table )
    {
        $apps = Application::applications();
        $relations = [ [] ];
        foreach ( $apps as $app ) {
            $dir = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/arRelations.json';
            if ( file_exists( $dir ) ) {
                $relations[] = json_decode( file_get_contents( $dir ), \true );
            }
        }

        $relations = array_merge( ...$relations );

        if ( isset( $relations[ $table ] ) ) {
            $class = \IPS\ROOT_PATH . '/' . $relations[ $table ];

            if ( file_exists( $class ) ) {
                $content = file_get_contents( $class );
                static::i()->create( $content );
            }
        }
    }

    /**
     * @param $content
     */
    public function create( string $content )
    {
        try {
            $data = $this->tokenize( $content );
            if ( isset( $data[ 'class' ], $data[ 'namespace' ] ) ) {
                $namespace = $data[ 'namespace' ];
                $ns2 = explode( '\\', $namespace );
                array_shift( $ns2 );
                $app = array_shift( $ns2 );
                $isApp = \false;
                $appPath = \IPS\ROOT_PATH . '/applications/' . $app;

                if ( $app && is_dir( $appPath ) ) {
                    $isApp = \true;
                }

                $ipsClass = $data[ 'class' ];

                if ( ( $namespace === 'IPS' && $ipsClass === '_Settings' ) || mb_strpos( $namespace, 'IPS\convert' ) !== \false ) {
                    return;
                }

                $first = mb_substr( $ipsClass, 0, 1 );
                if ( $first === '_' ) {
                    $class = mb_substr( $ipsClass, 1 );

                    if ( ReservedWords::check( $class ) ) {
                        return;
                    }

                    $type = '';
                    $body = [];
                    $classDefinition = [];
                    $classBlock = \null;
                    $props = \null;

                    $extraPath = $isApp ? $app : 'system';
                    $path = $this->save . '/class/' . $extraPath . '/';
                    $alt = str_replace( [
                        "\\",
                        ' ',
                        ';',
                    ], '_', $namespace );
                    $file = $alt . '_' . $class . '.php';

                    if ( $data[ 'final' ] ) {
                        $type = 'final ';
                    }

                    if ( $data[ 'abstract' ] ) {
                        $type = 'abstract ';
                    }

                    $new = new ClassGenerator();
                    $new->setName( $class );
                    $new->setNamespaceName( $namespace );
                    $new->setExtendedClass( $namespace . '\\' . $ipsClass );
                    $this->cache->addClass( $namespace . '\\' . $class );
                    $this->cache->addNamespace( $namespace );
                    if ( $type === 'abstract' ) {
                        $new->setAbstract( \true );
                    }

                    if ( $type === 'final' ) {
                        $new->setFinal( \true );
                    }

                    if ( Proxyclass::i()->doProps ) {
                        /* @var ActiveRecord $dbClass */
                        $dbClass = $namespace . '\\' . $class;
                        try {
                            if ( class_exists( $dbClass ) && method_exists( $dbClass, 'db' ) && property_exists( $dbClass, 'databaseTable' ) ) {
                                $table = $dbClass::$databaseTable;
                                if ( $table && $dbClass::db()->checkForTable( $table ) ) {
                                    /* @var array $definitions */
                                    $definitions = $dbClass::db()->getTableDefinition( $table );

                                    if ( isset( $definitions[ 'columns' ] ) ) {
                                        /* @var array $columns */
                                        $columns = $definitions[ 'columns' ];
                                        $len = mb_strlen( $dbClass::$databasePrefix );
                                        foreach ( $columns as $key => $val ) {
                                            if ( $len && 0 === mb_strpos( $key, $dbClass::$databasePrefix ) ) {
                                                $key = mb_substr( $key, $len );
                                            }
                                            $key = trim( $key );
                                            $this->buildHead( $key, $val, $classDefinition );
                                        }
                                    }

                                    $this->buildProperty( $dbClass, $classDefinition );
                                }
                            }
                        } catch ( Exception $e ) {
                        }

                        $this->runHelperClasses( $dbClass, $classDefinition, $ipsClass, $body );
                        $classBlock = $this->buildClassDoc( $classDefinition );
                    }

                    if ( \is_array( $body ) ) {
                        $newMethods = [];
                        foreach ( $body as $method ) {
                            if ( $method instanceof MethodGenerator ) {
                                $newMethods[ $method->getName() ] = $method;
                            }
                        }

                        if ( \count( $newMethods ) ) {
                            $new->addMethods( $newMethods );
                        }
                    }

                    if ( $classBlock instanceof DocBlockGenerator ) {
                        $new->setDocBlock( $classBlock );
                    }

                    $proxyFile = new DTFileGenerator;
                    $proxyFile->isProxy = \true;
                    $proxyFile->setClass( $new );
                    $proxyFile->setFilename( $path . '/' . $file );
                    $proxyFile->write();
                }
            }
        } catch ( Exception $e ) {
            Debug::add( 'Proxy Create', $e );
        }
    }

    /**
     * returns the class and namespace
     *
     * @param $source
     *
     * @return array|null
     */
    public function tokenize( $source )
    {
        $namespace = 0;
        $tokens = token_get_all( $source );
        $count = \count( $tokens );
        $dlm = \false;
        $final = \false;
        $abstract = \false;

        for ( $i = 2; $i < $count; $i++ ) {
            if ( ( isset( $tokens[ $i - 2 ][ 1 ] ) && ( $tokens[ $i - 2 ][ 1 ] === 'phpnamespace' || $tokens[ $i - 2 ][ 1 ] === 'namespace' ) ) || ( $dlm && $tokens[ $i - 1 ][ 0 ] === \T_NS_SEPARATOR && $tokens[ $i ][ 0 ] === \T_STRING ) ) {
                if ( !$dlm ) {
                    $namespace = 0;
                }
                if ( isset( $tokens[ $i ][ 1 ] ) ) {
                    $namespace = $namespace ? $namespace . "\\" . $tokens[ $i ][ 1 ] : $tokens[ $i ][ 1 ];
                    $dlm = \true;
                }
            }
            else if ( $dlm && ( $tokens[ $i ][ 0 ] !== \T_NS_SEPARATOR ) && ( $tokens[ $i ][ 0 ] !== \T_STRING ) ) {
                $dlm = \false;
            }

            if ( $tokens[ $i ][ 0 ] === \T_FINAL ) {
                $final = \true;
            }

            if ( $tokens[ $i ][ 0 ] === \T_ABSTRACT ) {
                $abstract = \true;
            }

            if ( ( $tokens[ $i - 2 ][ 0 ] === \T_CLASS || ( isset( $tokens[ $i - 2 ][ 1 ] ) && $tokens[ $i - 2 ][ 1 ] === 'phpclass' ) ) && $tokens[ $i - 1 ][ 0 ] === \T_WHITESPACE && $tokens[ $i ][ 0 ] === \T_STRING ) {
                $class = $tokens[ $i ][ 1 ];
                return [
                    'namespace' => $namespace,
                    'class'     => $class,
                    'abstract'  => $abstract,
                    'final'     => $final,
                ];
            }
        }

        return \null;
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
    protected function buildHead( $name, $def, &$classDefinition )
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

        $comment = \null;

        if ( $def[ 'comment' ] ) {
            $comment = $def[ 'comment' ];
        }

        $type = \null;

        if ( \in_array( $def[ 'type' ], $ints, \true ) ) {
            $type = 'int';
        }
        else {
            $type = 'string';
        }

        if ( $def[ 'allow_null' ] ) {
            $type .= '|null';
        }

        $classDefinition[ $name ] = [ 'pt' => 'p', 'prop' => $name, 'type' => $type, 'comment' => $comment ];
    }

    /**
     * builds props out of the setters and getters
     *
     * @param $class
     * @param $classDefinition
     */
    protected function buildProperty( $class, &$classDefinition )
    {
        try {
            $gs = [ 'set_', 'get_' ];
            $reflect = new ReflectionClass( $class );
            $methods = $reflect->getMethods();
            if ( \is_array( $methods ) && \count( $methods ) ) {
                $data = [];
                foreach ( $methods as $method ) {
                    if ( $method->name !== \null ) {
                        $type = trim( mb_substr( $method->name, 0, 4 ) );
                        $key = trim( mb_substr( $method->name, 4, mb_strlen( $method->name ) ) );

                        if ( \in_array( $type, $gs, \true ) ) {
                            $comment = \null;
                            $return = [ 'type' => $type === 'set_' ? 'void' : 'string' ];
                            if ( $method->hasReturnType() ) {
                                $return = [ 'type' => $method->getReturnType() ];
                            }
                            else {
                                $doc = $method->getDocComment();
                                preg_match_all( '#@return([^\n]+)?#', $doc, $match );

                                if ( isset( $match[ 1 ][ 0 ] ) ) {
                                    $match = array_filter( explode( ' ', $match[ 1 ][ 0 ] ) );
                                    $mtype = trim( array_shift( $match ) );
                                    if ( \is_array( $match ) && \count( $match ) ) {
                                        $comment = implode( ' ', $match );
                                    }

                                    $return = [ 'type' => $mtype, 'comment' => $comment ];
                                }
                            }

                            if ( $type === 'set_' ) {
                                if ( !isset( $classDefinition[ $key ] ) && isset( $data[ 'get' ][ $key ] ) ) {
                                    $data[ 'prop' ][ $key ] = $data[ 'get' ][ $key ];
                                    unset( $data[ 'get' ][ $key ] );
                                }
                                else if ( !isset( $data[ 'prop' ][ $key ] ) ) {
                                    if ( !isset( $classDefinition[ $key ] ) ) {
                                        $data[ 'set' ][ $key ] = $return;
                                    }
                                    else {
                                        $data[ 'prop' ][ $key ] = $return;
                                    }
                                }
                            }
                            else if ( $type === 'get_' ) {
                                if ( !isset( $classDefinition[ $key ] ) && isset( $data[ 'set' ][ $key ] ) ) {
                                    $return = [ 'type' => 'string' ];
                                    if ( $method->hasReturnType() ) {
                                        $return = [ 'type' => $method->getReturnType() ];
                                    }
                                    else {
                                        $doc = $method->getDocComment();
                                        preg_match_all( '#@return([^\n]+)?#', $doc, $match );
                                        if ( isset( $match[ 1 ][ 0 ] ) ) {
                                            $match = array_filter( explode( ' ', $match[ 1 ][ 0 ] ) );
                                            $mtype = trim( array_shift( $match ) );
                                            if ( \is_array( $match ) && \count( $match ) ) {
                                                $comment = implode( ' ', $match );
                                            }

                                            $return = [ 'type' => $mtype, 'comment' => $comment ];
                                        }
                                    }
                                    $data[ 'prop' ][ $key ] = $return;
                                    unset( $data[ 'set' ][ $key ] );
                                }
                                else {
                                    if ( !isset( $data[ 'prop' ][ $key ] ) ) {
                                        if ( !isset( $classDefinition[ $key ] ) ) {
                                            $data[ 'get' ][ $key ] = $return;
                                        }
                                        else {
                                            $data[ 'prop' ][ $key ] = $return;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ( $data as $key => $value ) {
                    if ( \is_array( $value ) && \count( $value ) ) {
                        foreach ( $value as $prop => $return ) {
                            $prop = trim( $prop );
                            switch ( $key ) {
                                case 'get':
                                    $vals = 'r';
                                    break;
                                case 'set':
                                    $vals = 'w';
                                    break;
                                default:
                                case 'prop':
                                    $vals = 'p';
                                    break;
                            }

                            $classDefinition[ $prop ] = [
                                'pt'      => $vals,
                                'prop'    => $prop,
                                'type'    => $return[ 'type' ],
                                'comment' => $return[ 'comment' ] ?? \null,
                            ];
                        }
                    }
                }
            }
        } catch ( Exception $e ) {
            Debug::add( 'class', $e );
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
    protected function runHelperClasses( $class, &$classDoc, &$classExtends, &$body )
    {
        $helpers = [];

        try {
            if ( empty( $this->helperClasses ) === true ) {
                /* @var Application $app */
                foreach ( Application::appsWithExtension( 'toolbox', 'ProxyHelpers' ) as $app ) {
                    $extensions = $app->extensions( 'toolbox', 'ProxyHelpers', \true );
                    foreach ( $extensions as $extension ) {
                        if ( method_exists( $extension, 'map' ) ) {
                            $extension->map( $helpers );
                        }
                    }
                }

                $this->helperClasses = $helpers;
                Debug::add( 'helperClasses', $this->helperClasses, true );
            }
            if ( isset( $this->helperClasses[ $class ] ) && \is_array( $this->helperClasses[ $class ] ) ) {
                /* @var HelpersAbstract $helperClass */
                foreach ( $this->helperClasses[ $class ] as $helper ) {
                    $helperClass = new $helper;
                    $helperClass->process( $class, $classDoc, $classExtends, $body );
                }
            }

        } catch ( Exception $e ) {
            Debug::add( 'helpers', $e );
        }
    }

    /**
     * @param array $properties
     *
     * @return mixed
     */
    public function buildClassDoc( array $properties )
    {
        $block = [];
        foreach ( $properties as $key => $property ) {
            try {
                if ( class_exists( $property[ 'type' ] ) ) {
                    $property[ 'type' ] = '\\' . $property[ 'type' ];
                }
                $comment = $property[ 'comment' ] ?? '';
                $content = $property[ 'type' ] . ' $' . $property[ 'prop' ] . ' ' . $comment;
                $pt = 'property';
                switch ( $property[ 'pt' ] ) {
                    case 'p':
                        $pt = 'property';
                        break;
                    case 'w':
                        $pt = 'property-write';
                        break;
                    case 'r':
                        $pt = 'property-read';
                }
                $block[] = new GenericTag( $pt, $content );
            } catch ( Exception $e ) {
                Debug::add( 'proxy', $property );
                Debug::add( 'proxy2', $e );
                Debug::add( 'proxy3', $properties );
            }
        }

        $docBlock = new DocBlockGenerator();
        $docBlock->setTags( $block );
        return $docBlock;
    }

    /**
     * takes the settings from store and creates proxy props for them, so they will autocomplete
     */
    public function generateSettings()
    {
        try {

            Settings::i();
            $classDoc = [];

            /**
             * @var array $load
             */
            $load = Store::i()->settings;
            foreach ( $load as $key => $val ) {
                if ( \is_array( $val ) ) {
                    $type = 'array';
                }
                else if ( \is_int( $val ) ) {
                    $type = 'int';
                }
                else if ( \is_float( $val ) ) {
                    $type = 'float';
                }
                else if ( \is_bool( $val ) ) {
                    $type = 'bool';
                }
                else {
                    $type = 'string';
                }

                $classDoc[] = [ 'pt' => 'p', 'prop' => $key, 'type' => $type ];
            }

            $header = $this->buildClassDoc( $classDoc );
            $class = new DTClassGenerator();
            $class->setNamespaceName( 'IPS' );
            $class->setName( 'Settings' );
            $class->setExtendedClass( _Settings::class );
            $class->setDocBlock( $header );
            $file = new DTFileGenerator;
            $file->setClass( $class );
            $file->setFilename( $this->save . '/IPS_Settings.php' );
            $file->write();
        } catch ( Exception $e ) {
        }

    }

    /**
     * builds the constants out since they are a mapped array in init.php
     */
    public function buildConstants()
    {
        if ( Proxyclass::i()->doConstants ) {
            $load = IPS::defaultConstants();
            $extra = "\n";
            foreach ( $load as $key => $val ) {
                $vals = \null;
                if ( \defined( $key ) ) {
                    $vals = \constant( $key );
                }

                if ( \is_bool( $val ) ) {
                    $vals = (int)$vals;
                    $val = $vals === 1 ? 'true' : 'false';
                }
                else if ( !is_numeric( $val ) ) {
                    $val = "'" . $val . "'";
                }

                $extra .= 'define( "IPS\\' . $key . '",' . $val . ");\n";
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
            $file->setBody( $extra );
            $this->_writeFile( 'IPS_Constants.php', $file->generate(), $this->save, \false );
        }
    }
}

