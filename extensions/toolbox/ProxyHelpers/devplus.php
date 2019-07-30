<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Devplus
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use IPS\Application;
use IPS\toolbox\DevCenter\Dev;
use IPS\toolbox\DevCenter\Extensions\ContentRouter;
use IPS\toolbox\DevCenter\Extensions\CreateMenu;
use IPS\toolbox\DevCenter\Extensions\FileStorage;
use IPS\toolbox\DevCenter\Helpers\HelperCompilerAbstract;
use IPS\toolbox\DevCenter\Sources\Generator\GeneratorAbstract;
use IPS\toolbox\Generator\Builders\ClassGenerator;
use function defined;
use function header;
use function mb_strtolower;

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * devplus
 */
class _devplus
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param ClassGenerator $classGenerator
     */
    public function store( ClassGenerator $classGenerator )
    {

        $classGenerator->addPropertyTag( 'dtdevplus_class_namespace', [ 'hint' => 'array' ] );

    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param ClassGenerator $classGenerator
     *
     * @throws \Exception
     */
    public function request( ClassGenerator $classGenerator )
    {

        $key = GeneratorAbstract::class;
        $classGenerator->addPropertyTag( 'dtdevplus_class_namespace', [ 'hint' => '\\' . $key ] );

        $app = Application::load( 'core' );
        $this->loop( ( new ContentRouter( $app, $app, 'foo' ) )->elements(), $classGenerator );
        $this->loop( ( new CreateMenu( $app, $app, 'foo' ) )->elements(), $classGenerator );
        $this->loop( ( new FileStorage( $app, $app, 'foo' ) )->elements(), $classGenerator );
    }

    protected function loop( array $elements, ClassGenerator $classGenerator )
    {

        $prefix = \null;
        if ( isset( $elements[ 'prefix' ] ) ) {
            $prefix = $elements[ 'prefix' ];
        }

        foreach ( $elements as $el ) {
            if ( isset( $el[ 'name' ] ) && $el[ 'name' ] !== 'namespace' ) {
                if ( isset( $el[ 'class' ] ) && 'stack' === mb_strtolower( $el[ 'class' ] ) ) {
                    $key = 'array';
                }
                else {
                    $key = 'string';
                }
                $classGenerator->addPropertyTag( $prefix . $el[ 'name' ], [ 'hint' => $key ] );
            }
        }
    }

    /**
     * returns a list of classes available to run on classes
     *
     * @return array
     * return [ class\to\look\for => class\of\helper\class ]
     */
    public function map( &$helpers )
    {

        $helpers[ Dev\Compiler\CompilerAbstract::class ][] = HelperCompilerAbstract::class;
    }
}
