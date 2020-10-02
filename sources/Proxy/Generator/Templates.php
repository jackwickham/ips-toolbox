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
use Generator\Builders\ClassGenerator;
use IPS\Data\Store;
use IPS\Log;
use IPS\Theme;
use IPS\toolbox\Application;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Shared\Read;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use function array_pop;
use function array_values;
use function count;
use function defined;
use function explode;
use function function_exists;
use function header;
use function ksort;
use function md5;
use function rand;
use function random_int;
use function str_replace;
use function time;
use function trim;
use const DIRECTORY_SEPARATOR;

Application::loadAutoLoader();

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Templates Class
 *
 * @mixin Templates
 */
class _Templates extends GeneratorAbstract
{

    use Read;

    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;

    public function create()
    {
        $jsonMeta = Store::i()->dt_json ?? [];

        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                "IPS\\Theme::getTemplate:0",
            ],
            'provider'  => 'templateGroup',
            'language'  => 'php',
        ];
        //this pisses me off, this use to work!
        $jsonMeta[ 'registrar' ][] = [
            'signature' => [
                'IPS\\Theme::getTemplate:0',
            ],
            'signatures' => [
                [
                    'class'  => Theme::class,
                    'method' => 'getTemplate',
                    'index' => 0,
                    'type'   => 'type',
                ],

            ],
            'provider'   => 'templateClass',
            'language'   => 'php',
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

        $templates = [];
        $tempStore = [];
        $tempClass = [];

        if ( isset( Store::i()->dtproxy_templates ) ) {
            $templates = Store::i()->dtproxy_templates;
        }
        if ( count( $templates ) ) {
            foreach ( $templates as $key => $template ) {
                $key = str_replace( \IPS\ROOT_PATH . '/applications/', '', $key );
                $tpl = explode( DIRECTORY_SEPARATOR, $key );
                array_pop( $tpl );
                $temp = array_pop( $tpl );
                $ori = $temp;
                $newParams = [];
                if ( $temp === 'global' ) {
                    $temp = 'nglobal';
                }

                $tempStore[ $ori ] = [
                    'lookup_string' => $ori,
                    'type'          => 'IPS\\Theme\\Templates\\' . $temp,

//                    "type" => \DateTime::class,
                    'icon' => "com.jetbrains.php.PhpIcons.CLASS",
                ];

                if ( !empty( $template[ 'params' ] ) ) {

                    $rand = trim( $template[ 'method' ] ) . random_int( 1, 20000 ) . random_int( 1, 30000 ) . md5( time() + rand( 1, 10000 ) );
                    $fun = 'function ' . $rand . '( ' . $template[ 'params' ] . ' ) {}';
                    @eval( $fun );
                    if (function_exists( $rand)) {
                        try {
                            $reflection = new ReflectionFunction($rand);
                            $params = $reflection->getParameters();

                            /** @var ReflectionParameter $param */
                            foreach ( $params as $param ) {
                                $data  = [
                                    'name' => $param->getName()
                                ];


                                if ( $param->getType() ) {
                                    $data['hint'] = $param->getType();
                                }

                                try {
                                    $data['value'] = $param->getDefaultValue();
                                } catch ( ReflectionException $e ) {
                                }
                                $newParams[$param->getPosition()] = $data;

                            }
                        } catch (\Exception $e) {
                        }
                    }
                }

                $mn = mb_strtolower( trim( $template[ 'method' ] ) );
                    $tempClass[ $temp ][ $template[ 'method' ] ] = [
                        'name' => $template[ 'method' ],
                        'params' => $newParams
                    ];
            }
        }

        ksort( $tempStore );
        $tempStore = array_values( $tempStore );
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
//        foreach ( $classes as $key => $templates ) {
//            try {
//
//                $newClass = new ClassGenerator;
//                $newClass->addNameSpace( 'IPS\Theme\Templates' );
//                $newClass->addClassName( $key );
//                $newClass->addFileName($key );
//                $newClass->addPath($this->save . '/templates/');
//                foreach( $templates as $template ){
//                    $newClass->addMethod($template['name'], '', $template['params']);
//                }
//                $newClass->save();
//            } catch ( Exception $e ) {
//                Debug::log( $e );
//            }
//        }
    }
}

