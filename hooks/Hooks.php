//<?php


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

use Generator\Builders\ClassGenerator;
use IPS\Application;
use IPS\Helpers\Form;
use IPS\Helpers\Form\Text;
use IPS\IPS;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\Proxy\Proxyclass;
use const IPS\ROOT_PATH;
class toolbox_hook_Hooks extends _HOOK_CLASS_
{

    public static function devTable( $url, $appOrPluginId, $hookDir ){

        $hookTable = Request::i()->hookTable;

        if ( $hookTable === 'add' && !\is_int( $appOrPluginId ) && Request::i()->plugin_hook_type === 'C' && Request::i()->plugin_hook_class !== null ) {

            $class = Request::i()->plugin_hook_class;
            if ( $class !== null && class_exists( 'IPS\\' . $class ) ) {
                $app = Application::load( $appOrPluginId );
                $specialHooks = $app->extensions( 'toolbox', 'SpecialHooks' ) && property_exists( IPS::class, 'beenPatched' ) && IPS::$beenPatched === true;
                $hook = new static;
                $hook->app = $appOrPluginId;
                $hook->type = Request::i()->plugin_hook_type;
                $hook->class = ( '\IPS\\' . $class );
                $hook->filename = Request::i()->plugin_hook_location ?: md5( mt_rand() );
                $hook->save();

                $reflection = new ReflectionClass( $hook->class );
                $classname = "{$appOrPluginId}_hook_{$hook->filename}";
                $hookClass = new ClassGenerator();
                $hookClass->isHook = true;
                $classDoc[] = 'Hook For ' . $hook->class;
                $classDoc[] = '@mixin ' . $hook->class;
                $hookClass->addDocumentComment( $classDoc, true );
                $hookClass->addClassName( $classname );
                $hookClass->addFileName( $hook->filename );
                $hookClass->addPath( $hookDir );
                if ( $reflection->isAbstract() === true ) {
                    $hookClass->isAbstract();
                }

                $extends = $specialHooks ? '_HOOK_CLASS_' . $hook->app . '_hook_' . $hook->filename : '_HOOK_CLASS_';
                $hookClass->addExtends( $extends, false );
                $hookClass->save();

                if ( $specialHooks === true ) {
                    $proxyClass = new ClassGenerator();
                    $proxyClass->isHook = true;
                    $proxyClass->addPath( ROOT_PATH . '//' . Proxyclass::i()->save . '/hooks/' . $hook->app . '/' );
                    $proxyClass->addFileName( $extends );
                    $proxyClass->addClassName( $extends );
                    $proxyClass->addExtends( $hook->class );
                    $proxyClass->save();
                }

                static::writeDataFile();
                $app->skip = true;
                $app->buildHooks();
                Output::i()->redirect( $url );
            }
        }
        $parent = parent::devTable( $url, $appOrPluginId, $hookDir );

        /** @var Form $parent */
        if ( $hookTable === 'add' ) {
            $elements = $parent->elements;

            $options = [
                'placeholder'  => 'Namespace',
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass&appKey=' . Request::i()->appKey,
                    'minimized'            => false,
                    'commaTrigger'         => false,
                    'unique'               => true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ];

            unset( $elements[ 'plugin_hook_class' ] );
            $parent->elements = $elements;

            $parent->add( new Text( 'plugin_hook_class', null, true, $options, function ( $val )
            {

                if ( $val && !class_exists( 'IPS\\' . $val ) ) {
                    throw new DomainException( 'plugin_hook_class_err' );
                }
            }, 'IPS\\', null, 'plugin_hook_class' ) );
        }

        return $parent;
    }
}const 