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
     * @param array $classDoc
     */
    public function store( &$classDoc )
    {
        $classDoc[] = [ 'pt' => 'p', 'prop' => 'dtdevplus_class_namespace', 'type' => 'array' ];
    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param array $classDoc
     *
     * @throws \Exception
     */
    public function request( &$classDoc )
    {
        $key = GeneratorAbstract::class;
        $classDoc[] = [ 'pt' => 'p', 'prop' => 'dtdevplus_class_namespace', 'type' => '\\' . $key ];
        $this->loop( Sources::i()->elements(), $classDoc );
        $this->loop( Dev::i()->elements(), $classDoc );
        $app = Application::load( 'core' );
        $this->loop( ( new ContentRouter( $app, $app, 'foo' ) )->elements(), $classDoc );
        $this->loop( ( new CreateMenu( $app, $app, 'foo' ) )->elements(), $classDoc );
        $this->loop( ( new FileStorage( $app, $app, 'foo' ) )->elements(), $classDoc );
    }

    protected function loop( array $elements, &$classDoc )
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

                $classDoc[ $el[ 'name' ] ] = [ 'pt' => 'p', 'prop' => "{$prefix}{$el['name']}", 'type' => $key ];
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
