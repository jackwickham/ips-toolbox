<?php
/**
 * @brief      Templates Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */

namespace IPS\toolbox\Proxy\Generator;

use Exception;
use Generator\Tokenizers\StandardTokenizer;
use IPS\Data\Store;
use IPS\Log;
use IPS\Theme;
use ReflectionException;
use ReflectionFunction;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use function defined;
use function function_exists;
use function header;
use function md5;
use function rand;
use function random_int;
use function str_replace;
use function time;

\IPS\toolbox\Application::loadAutoLoader();

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Templates Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Templates
 */
class _Templates extends GeneratorAbstract
{

    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;

    /**
     * creates the jsonMeta for the json file and writes the provider to disk.
     */
    public function create()
    {

        $jsonMeta = [];
        if ( isset( Store::i()->dt_json ) ) {
            $jsonMeta = Store::i()->dt_json;
        }
        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                "IPS\\Theme::getTemplate:0",
            ],
            'provider'  => 'templateGroup',
            'language'  => 'php',
        ];
        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                "IPS\\Theme::getTemplate:2",
                'IPS\\Output::js:2',
                'IPS\\Output::css:2',
            ],
            'provider'  => 'templateLocation',
            'language'  => 'php',
        ];
        $jsonMeta[ 'providers' ][] = [
            'name'           => 'templateLocation',
            'lookup_strings' => [
                'admin',
                'front',
                'global',
            ],
        ];
        $jsonMeta[ 'registrar' ][] = [
            'signature'  => [
                'IPS\\Theme::getTemplate:0',
            ],
            'signatures' => [
                [
                    'class'  => Theme::class,
                    'method' => 'getTemplate',
                    'index'  => 0,
                    'type'   => 'type',
                ],

            ],
            'provider'   => 'templateClass',
            'language'   => 'php',
        ];
        $templates = [];
        $tempStore = [];
        $tempClass = [];

        if ( isset( Store::i()->dtproxy_templates ) ) {
            $templates = Store::i()->dtproxy_templates;
        }
        if ( \count( $templates ) ) {
            foreach ( $templates as $key => $template ) {
                $key = str_replace( \IPS\ROOT_PATH . '/applications/', '', $key );
                $tpl = \explode( \DIRECTORY_SEPARATOR, $key );
                \array_pop( $tpl );
                $temp = \array_pop( $tpl );
                $ori = $temp;
                $newParams = [];
                if ( $temp === 'global' ) {
                    $temp = 'nglobal';
                }

                $tempStore[ $ori ] = [
                    'lookup_string' => $ori,
                    'type'          => 'dtProxy\\Templates\\' . $temp,
                ];
                $fileName = str_replace( [ '\\', '/' ], '', $temp );
                $path = $this->save . '/templates/' . $fileName . '.php';

                if ( file_exists( $path ) ) {
                    $class = new StandardTokenizer( $path );
                }
                else {
                    $class = new \Generator\Builders\ClassGenerator;
                    $class->addPath( $this->save . '/templates/' );
                    $class->addFileName( $fileName );
                    $class->addNameSpace( 'dtProxy\Templates' );
                    $class->addClassName( $temp );
                }

                if ( !empty( $template[ 'params' ] ) ) {

                    $rand = \trim( $template[ 'method' ] ) . random_int( 1, 20000 ) . random_int( 1, 30000 ) . md5( time() + rand( 1, 10000 ) );
                    $fun = 'function ' . $rand . '( ' . $template[ 'params' ] . ' ) {}';

                    $continue = \true;

                    if ( !function_exists( 'function ' . $rand ) && eval( $fun ) === \false ) {
                        $continue = \false;
                    }

                    //                    if ( $continue ) {
                    //                        $reflection = new ReflectionFunction( $rand );
                    //                        $params = $reflection->getParameters();
                    //                        $newParams = [];
                    //                        /** @var \ReflectionParameter $param */
                    //                        foreach ( $params as $param ) {
                    //                            $position = $param->getPosition();
                    //                            $newParams[ $position ][ 'name' ] = $param->getName();
                    //                            $newParams[ $position ][ 'hint' ] = $param->getType();
                    //                            if ( $param->allowsNull() ) {
                    //
                    //                            }
                    //                            try {
                    //                                $newParams[ $position ][ 'value' ] = $param->getDefaultValue();
                    //
                    //                            } catch ( ReflectionException $e ) {
                    //                                $newParams[ $position ][ 'value' ] = \null;
                    //                            }
                    //
                    //                        }
                    //                    }
                }

                //                $class->addMethod( $template[ 'method' ] );
                try {
                    $mn = $template[ 'method' ];
                    $tempClass[ $temp ][ $mn ] = MethodGenerator::fromArray( [
                        'name'       => $template[ 'method' ],
                        'parameters' => $newParams,
                        'static'     => \false,
                    ] );
                } catch ( Exception $e ) {
                    Log::log( $e );
                }
            }
        }

        \ksort( $tempStore );
        $tempStore = \array_values( $tempStore );
        $jsonMeta[ 'providers' ][] = [
            'name'  => 'templateClass',
            'items' => $tempStore,
        ];
        Store::i()->dt_json = $jsonMeta;
        $this->makeTempClasses( $tempClass );

    }

    /**
     * @param array $classes
     */
    public function makeTempClasses( array $classes )
    {

        foreach ( $classes as $key => $templates ) {
            try {
                $fileName = str_replace( [ '\\', '/' ], '', $key );
                $newClass = new ClassGenerator;
                $newClass->setNamespaceName( 'dtProxy\Templates' );
                $newClass->setName( $key );
                $newClass->addMethods( $templates );
                $content = new FileGenerator;
                $content->setClass( $newClass );
                $content->setFilename( $this->save . '/templates/' . $fileName . '.php' );
                $content->write();
            } catch ( Exception $e ) {
                Log::log( $e );
            }
        }
    }
}

