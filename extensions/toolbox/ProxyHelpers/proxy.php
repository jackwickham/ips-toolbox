<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Proxy
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Proxy Class Generator
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use IPS\Content\Item;
use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Node\Model;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevCenter\Sources\Generator\GeneratorAbstract as devPlusGeneratorAbstract;
use IPS\toolbox\Generator\Builders\ClassGenerator;
use IPS\toolbox\Proxy\Helpers\GeneratorAbstract;
use IPS\toolbox\Proxy\Helpers\Request as HelpersRequest;
use IPS\toolbox\Proxy\Helpers\Store as HelpersStore;
use IPS\Widget;
use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * proxy
 */
class _proxy
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param array $classDoc
     */
    public function store( ClassGenerator $classGenerator )
    {

        $classDoc[] = [ 'prop' => 'dtproxy_proxy_files', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dt_json', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'dtproxy_templates', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'toolbox_proxy_namespaces', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'toolbox_proxy_classes', 'hint' => 'array' ];
        foreach ( $classDoc as $doc ) {
            $classGenerator->addPropertyTag( $doc[ 'prop' ], [ 'hint' => $doc[ 'hint' ] ] );

        }
    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param array $classDoc
     */
    public function request( ClassGenerator $classGenerator )
    {

    }

    /**
     * returns a list of classes available to run on classes
     *
     * @param $helpers
     */
    public function map( &$helpers )
    {

        $helpers[ Request::class ][] = HelpersRequest::class;
        $helpers[ Store::class ][] = HelpersStore::class;
        $helpers[ devPlusGeneratorAbstract::class ][] = GeneratorAbstract::class;
        $helpers[ Output::class ][] = \IPS\toolbox\Proxy\Helpers\Output::class;
        $helpers[ Model::class ][] = \IPS\toolbox\Proxy\Helpers\Model::class;
        $helpers[ Url::class ][] = \IPS\toolbox\Proxy\Helpers\Url::class;
        $helpers[ Item::class ][] = \IPS\toolbox\Proxy\Helpers\Item::class;

    }
}
