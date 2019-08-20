<?php

namespace IPS\toolbox\modules\admin\devcenter;

use IPS\Application;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevCenter\Sources;
use IPS\toolbox\Proxy\Generator\Cache;
use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * sources
 */
class _sources extends \IPS\Dispatcher\Controller
{

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Sources
     */
    protected $elements;

    public function execute()
    {

        \IPS\Dispatcher::i()->checkAcpPermission( 'sources_manage' );
        Sources::menu();
        $this->application = Application::load( Request::i()->appKey );
        $this->elements = new Sources( $this->application );
        parent::execute();
    }

    protected function manage()
    {
    }

    protected function standard()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Abstract',
            'Extends',
            'Interfaces',
            'Traits',
        ];

        $this->doOutput( $config, 'standard', 'Standard Class' );
    }

    protected function doOutput( $config, $type, $title )
    {

        $this->elements->buildForm( $config, $type );
        $this->elements->create();
        $url = (string)Url::internal( 'app=core&module=applications&controller=developer&appKey=' . Request::i()->appKey );
        Output::i()->breadcrumb[] = [ $url, 'Developer Ceneter' ];
        Output::i()->breadcrumb[] = [ $url, $this->application->directory ];
        Output::i()->breadcrumb[] = [ \null, $title ];

        Output::i()->title = \mb_strtoupper( $this->application->directory ) . ': ' . $title;

        //        $form = $this->elements->form->customTemplate( [
        //            call_user_func( [ Theme::i(), 'getTemplate' ], 'forms', 'core', 'front' ),
        //            'popupTemplate',
        //        ] );
        Output::i()->output = $this->elements->form;
    }

    protected function debug()
    {

        $this->elements->type = 'Debug';
        $this->elements->generate();
        $url = Url::internal( 'app=core&module=applications&controller=developer&appKey=' . $this->application->directory );
        Output::i()->redirect( $url, 'Profiler Debug Class Generated' );
    }

    protected function memory()
    {

        $this->elements->type = 'Memory';
        $this->elements->generate();
        $url = Url::internal( 'app=core&module=applications&controller=developer&appKey=' . $this->application->directory );
        Output::i()->redirect( $url, 'Profiler Memory Class Generated' );
    }

    protected function form()
    {

        $this->elements->type = 'Form';
        $this->elements->generate();
        $url = Url::internal( 'app=core&module=applications&controller=developer&appKey=' . $this->application->directory );
        Output::i()->redirect( $url, 'Form Class Generated' );
    }

    protected function cinterface()
    {

        $config = [
            'Namespace',
            'ClassName',
        ];

        $this->doOutput( $config, 'interfacing', 'Interface' );
    }

    protected function ctraits()
    {

        $config = [
            'Namespace',
            'ClassName',
        ];

        $this->doOutput( $config, 'traits', 'Trait' );
    }

    protected function singleton()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Interfaces',
            'Traits',
        ];

        $this->doOutput( $config, 'singleton', 'Singleton' );
    }

    protected function ar()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Database',
            'prefix',
            'scaffolding',
            'Interfaces',
            'Traits',
        ];

        $this->doOutput( $config, 'activerecord', 'ActiveRecord Class' );
    }

    protected function node()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Database',
            'prefix',
            'Scaffolding',
            'SubNode',
            'ItemClass',
            'NodeInterfaces',
            'NodeTraits',
        ];
        $this->doOutput( $config, 'node', 'Node Class' );
    }

    protected function item()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Database',
            'prefix',
            'Scaffolding',
            'ItemNodeClass',
            'ItemCommentClass',
            'ItemReviewClass',
            'ItemInterfaces',
            'ItemTraits',
        ];
        $this->doOutput( $config, 'item', 'Item Class' );
    }

    protected function comment()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Database',
            'prefix',
            'Scaffolding',
            'ContentItemClass',
            'CommentInterfaces',
            'ItemTraits',
        ];
        $this->doOutput( $config, 'comment', 'Comment Class' );
    }

    protected function review()
    {

        $config = [
            'Namespace',
            'ClassName',
            'Imports',
            'Database',
            'prefix',
            'Scaffolding',
            'ContentItemClass',
            'CommentInterfaces',
            'ItemTraits',
        ];
        $this->doOutput( $config, 'review', 'Review Class' );
    }

    protected function findClass()
    {

        $classes = Cache::i()->getClasses();

        if ( empty( $classes ) !== true ) {
            $input = ltrim( Request::i()->input, '\\' );

            $root = preg_quote( $input, '#' );
            $foo = preg_grep( '#^' . $root . '#i', $classes );
            $return = [];
            foreach ( $foo as $f ) {
                $return[] = [
                    'value' => '\\' . $f,
                    'html'  => '\\' . $f,
                ];
            }
            Output::i()->json( $return );
        }
    }

    protected function findClass2()
    {

        $classes = Cache::i()->getClasses();

        if ( empty( $classes ) !== true ) {
            $input = 'IPS\\' . Request::i()->appKey . '\\' . ltrim( Request::i()->input, '\\' );

            $root = preg_quote( $input, '#' );
            $foo = preg_grep( '#^' . $root . '#i', $classes );
            $return = [];
            foreach ( $foo as $f ) {
                $return[] = [
                    'value' => str_replace( 'IPS\\' . Request::i()->appKey . '\\', '', $f ),
                    'html'  => '\\' . $f,
                ];
            }
            Output::i()->json( $return );
        }
    }

    protected function findNamespace()
    {

        $ns = Cache::i()->getNamespaces();

        if ( empty( $ns ) !== true ) {
            $input = 'IPS\\' . Request::i()->appKey . '\\' . ltrim( Request::i()->input, '\\' );
            $root = preg_quote( $input, '#' );
            $foo = preg_grep( '#^' . $root . '#i', $ns );
            $return = [];
            foreach ( $foo as $f ) {
                $return[] = [
                    'value' => str_replace( 'IPS\\' . Request::i()->appKey . '\\', '', $f ),
                    'html'  => '\\' . $f,
                ];
            }
            Output::i()->json( $return );
        }
    }

    protected function api()
    {

        $config = [
            'ClassName',
            'apiType',
        ];
        $this->doOutput( $config, 'api', 'API Class' );
    }
}
