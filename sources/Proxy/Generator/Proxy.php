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
use IPS\toolbox\Generator\Builders\ClassGenerator;
use IPS\toolbox\Generator\Tokenizers\StandardTokenizer;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Proxy\Helpers\HelpersAbstract;
use IPS\toolbox\Proxy\Proxyclass;
use IPS\toolbox\ReservedWords;
use IPS\toolbox\Shared\Write;
use function array_filter;
use function array_merge;
use function array_shift;
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
use function preg_match_all;
use function str_replace;
use function trim;
use const IPS\ROOT_PATH;
use IPS\_Settings;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
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
    public static function adjustModel( $table ): void
    {

        $apps = Application::applications();
        $relations = [ [] ];
        foreach ( $apps as $app ) {
            $dir = ROOT_PATH . '/applications/' . $app->directory . '/data/arRelations.json';
            if ( file_exists( $dir ) ) {
                $relations[] = json_decode( file_get_contents( $dir ), true );
            }
        }

        $relations = array_merge( ...$relations );

        if ( isset( $relations[ $table ] ) ) {
            $class = ROOT_PATH . '/' . $relations[ $table ];

            if ( file_exists( $class ) ) {
                //$content = file_get_contents( $class );
                static::i()->create( $class );
            }
        }
    }

    /**
     * @param $content
     */
    public function create( string $file ): void
    {

        try {

            $currentClass = new StandardTokenizer( $file );
            $namespace = $currentClass->getNameSpace();
            $ns2 = explode( '\\', $namespace );
            array_shift( $ns2 );
            $app = array_shift( $ns2 );
            $isApp = false;
            $appPath = ROOT_PATH . '/applications/' . $app;
            $ipsClass = $currentClass->getClassName();
            if ( $app && is_dir( $appPath ) ) {
                $isApp = true;
            }

            if ( ( $namespace === 'IPS' && $ipsClass === '_Settings' ) || mb_strpos( $namespace, 'IPS\convert' ) !== false ) {
                return;
            }

            $first = mb_substr( $ipsClass, 0, 1 );
            if ( $first === '_' ) {
                $class = mb_substr( $ipsClass, 1 );

                if ( ReservedWords::check( $class ) ) {
                    return;
                }

                $classBlock = null;
                $props = null;
                $extraPath = $isApp ? $app : 'system';
                $path = $this->save . '/class/' . $extraPath . '/';
                $alt = str_replace( [
                    "\\",
                    ' ',
                    ';',
                ], '_', $namespace );
                $file = $alt . '_' . $class . '.php';
                $type = $currentClass->getType();

                $nc = new ClassGenerator();
                $nc->addNameSpace( $namespace );
                $nc->addExtends( $namespace . '\\' . $ipsClass );
                $nc->addClassName( $class );
                $nc->addType( $type );
                $nc->addFileName( $file );
                $nc->addPath( $path );

                foreach ( $currentClass->getImports() as $import ) {
                    $class = $import[ 'class' ];
                    $alias = $import[ 'alias' ];
                    $nc->addImport( $class, $alias );
                }

                foreach ( $currentClass->getImportFunctions() as $import ) {
                    $class = $import[ 'class' ];
                    $alias = $import[ 'alias' ];
                    $nc->addImportFunction( $class, $alias );
                }

                foreach ( $currentClass->getImportConstants() as $import ) {
                    $class = $import[ 'class' ];
                    $nc->addImportConstant( $class );
                }

                $this->cache->addClass( $namespace . '\\' . $class );
                $this->cache->addNamespace( $namespace );

                if ( Proxyclass::i()->doProps ) {
                    $dbClass = $namespace . '\\' . $class;
                    try {
                        $db = \IPS\Db::i();
                        $databaseTable = $currentClass->getPropertyValue( 'databaseTable' );
                        if ( $databaseTable !== null && $db->checkForTable( $databaseTable ) ) {
                            /* @var array $definitions */
                            $definitions = $db->getTableDefinition( $databaseTable );
                            if ( isset( $definitions[ 'columns' ] ) ) {
                                /* @var array $columns */
                                $columns = $definitions[ 'columns' ];
                                $prefix = $currentClass->getPropertyValue( 'databasePrefix' );
                                $len = mb_strlen( $prefix );
                                foreach ( $columns as $key => $val ) {
                                    if ( $len && 0 === mb_strpos( $key, $prefix ) ) {
                                        $key = mb_substr( $key, $len );
                                    }
                                    $key = trim( $key );
                                    $this->buildDbToProperties( $key, $val, $nc );
                                }
                            }

                        }
                    } catch ( Exception $e ) {
                    }
                    $bitOptions = $currentClass->getPropertyValue( 'bitOptions' );
                    if ( $bitOptions !== null && is_array( $bitOptions ) ) {
                        foreach ( $bitOptions as $key => $value ) {
                            foreach ( $value as $k => $v ) {
                                $nc->addPropertyTag( $k, [ 'hint' => 'Bitwise' ] );
                            }
                        }
                        $nc->addImport( Bitwise::class );
                    }
                    $this->buildProprties( $currentClass, $nc );
                    $this->runHelperClasses( $dbClass, $nc, $ipsClass );
                }

                $nc->isProxy = true;
                $nc->save();

            }
        } catch ( Exception $e ) {
            Debug::log( $e );
        }
    }

    /**
     * builds the docblock for proxy props
     *
     * @param                $name
     * @param                $def
     * @param ClassGenerator $classGenerator
     *
     * @return void
     */
    protected function buildDbToProperties( $name, $def, ClassGenerator $classGenerator ): void
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

        if ( $def[ 'comment' ] ) {
            $comment = $def[ 'comment' ];
        }

        $type = null;

        if ( in_array( $def[ 'type' ], $ints, true ) ) {
            $type = 'int';
        }
        else {
            $type = 'string';
        }

        if ( $def[ 'allow_null' ] ) {
            $type .= '|null';
        }

        $classGenerator->addPropertyTag( $name, [ 'hint' => $type, 'comment' => $comment ] );
    }

    /**
     * builds props out of the setters and getters
     *
     * @param ClassGenerator $oldClass
     * @param ClassGenerator $newClass
     */
    public function buildProprties( ClassGenerator $oldClass, ClassGenerator $newClass ): void
    {

        try {
            $data = [];
            $methods = $oldClass->getMethods();
            if ( empty( $methods ) !== true ) {
                foreach ( $methods as $name => $method ) {
                    $type = trim( mb_substr( $name, 0, 4 ) );
                    $key = trim( mb_substr( $name, 4, mb_strlen( $name ) ) );
                    if ( $type === 'set_' || $type === 'get_' ) {
                        $pt = null;
                        if ( !isset( $data[ $key ] ) && $newClass->getPropertyTag( $key ) === null ) {
                            if ( $type === 'set_' ) {
                                $pt = 'w';
                            }

                            if ( $type === 'get_' ) {
                                $pt = 'r';
                            }
                        }
                        else {
                            if ( $newClass->getProperty( $key ) !== null ) {
                                $newClass->removeProperty( $key );
                            }
                            $pt = 'p';
                        }

                        $comment = null;
                        $return = $type === 'set_' ? 'void' : 'string';
                        if ( $method[ 'returnType' ] !== null ) {
                            $return = $method[ 'returnType' ];
                        }
                        else {
                            $docs = $method[ 'document' ];

                            if ( $docs !== null ) {
                                foreach ( $docs as $doc ) {
                                    if ( mb_strpos( $doc, '@return' ) !== false ) {
                                        preg_match_all( '#@return([^\n]+)?#', $doc, $match );

                                        if ( isset( $match[ 1 ][ 0 ] ) ) {
                                            $match = array_filter( explode( ' ', $match[ 1 ][ 0 ] ) );
                                            $mtype = trim( array_shift( $match ) );
                                            if ( is_array( $match ) && count( $match ) ) {
                                                $comment = implode( ' ', $match );
                                            }

                                            $return = $mtype;
                                        }
                                    }
                                }
                            }
                        }

                        if ( isset( $data[ $key ] ) ) {
                            if ( $return === 'void' || $data[ $key ][ 'type' ] !== 'void' ) {
                                $return = $data[ $key ][ 'type' ];
                            }
                        }

                        $data[ $key ] = [
                            'prop'    => trim( $key ),
                            'pt'      => $pt,
                            'type'    => $return,
                            'comment' => $comment,
                        ];
                    }
                }

                foreach ( $data as $prop => $value ) {
                    $pt = $value[ 'pt' ];
                    $extra = [ 'type' => null ];
                    if ( $pt === 'r' ) {
                        $extra[ 'type' ] = 'read';
                    }
                    else if ( $pt === 'w' ) {
                        $extra[ 'type' ] = 'write';
                    }
                    $extra[ 'comment' ] = $value[ 'comment' ];
                    $extra[ 'hint' ] = $value[ 'type' ];
                    $newClass->addPropertyTag( $prop, $extra );
                }
            }
        } catch ( Exception $e ) {
        }
    }

    /**
     * if there is a helper class, will run it here.
     *
     * @param                $class
     * @param ClassGenerator $classGenerator
     * @param                $classExtends
     */
    protected function runHelperClasses( $class, ClassGenerator $classGenerator, &$classExtends ): void
    {

        $helpers = [];

        try {
            if ( empty( $this->helperClasses ) === true ) {
                /* @var Application $app */
                foreach ( Application::appsWithExtension( 'toolbox', 'ProxyHelpers' ) as $app ) {
                    $extensions = $app->extensions( 'toolbox', 'ProxyHelpers', true );
                    foreach ( $extensions as $extension ) {
                        if ( method_exists( $extension, 'map' ) ) {
                            $extension->map( $helpers );
                        }
                    }
                }

                $this->helperClasses = $helpers;
            }
            if ( isset( $this->helperClasses[ $class ] ) && is_array( $this->helperClasses[ $class ] ) ) {
                /* @var HelpersAbstract $helperClass */
                foreach ( $this->helperClasses[ $class ] as $helper ) {
                    $helperClass = new $helper;
                    $helperClass->process( $class, $classGenerator, $classExtends );
                }
            }

        } catch ( Exception $e ) {
        }
    }

    /**
     * takes the settings from store and creates proxy props for them, so they will autocomplete
     */
    public function generateSettings(): void
    {

        try {

            $classDoc = [];
            $class = new ClassGenerator;
            $class->addPath( $this->save . '/IPS_Settings.php' );
            $class->isProxy = true;
            $class->addNameSpace( 'IPS' );
            $class->addClassName( 'Settings' );
            $class->addExtends( _Settings::class );
            /**
             * @var array $load
             */
            $load = Store::i()->settings;
            foreach ( $load as $key => $val ) {
                if ( is_array( $val ) ) {
                    $type = 'array';
                }
                else if ( is_int( $val ) ) {
                    $type = 'int';
                }
                else if ( is_float( $val ) ) {
                    $type = 'float';
                }
                else if ( is_bool( $val ) ) {
                    $type = 'bool';
                }
                else {
                    $type = 'string';
                }
                $class->addPropertyTag( $key, [ 'hint' => $type, 'type' => 'read' ] );
            }

            if ( Proxyclass::i()->doConstants ) {
                $load = IPS::defaultConstants();
                $extra = "\n";
                foreach ( $load as $key => $val ) {
                    $vals = null;
                    if ( defined( $key ) ) {
                        $vals = constant( $key );
                    }

                    if ( is_bool( $val ) ) {
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
                $class->extra( [ $extra ] );
            }
            $class->write();
        } catch ( Exception $e ) {
        }

    }

    /**
     * builds the constants out since they are a mapped array in init.php
     *
     * @deprecated now is apart of the settings, since it adds it to the end of the file.
     */
    public function buildConstants(): void
    {

    }
}
