<?php

namespace IPS\toolbox;

use IPS\toolbox\Generator\Builders\ClassGenerator;
use IPS\toolbox\Generator\DTClassGenerator;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Generator\Tokenizers\StandardTokenizer;
use IPS\toolbox\Proxy\Proxyclass;
use function count;
use function is_array;
use function is_file;
use function json_decode;
use function preg_replace;
use function property_exists;

class _GitHooks
{

    protected $apps = [];

    public function __construct( array $apps )
    {

        foreach ( $apps as $app ) {
            try {
                if ( !( $app instanceof \IPS\Application ) ) {
                    $app = Application::load( $app );
                }
                $this->apps[ $app->directory ] = $app;
            } catch ( \Exception $e ) {
            }
        }
    }

    public function writeSpecialHooks()
    {

        if ( property_exists( \IPS\IPS::class, 'beenPatched' ) && \IPS\IPS::$beenPatched ) {
            $apps = $this->apps;
            $ipsApps = \IPS\Application::$ipsApps;
            /** @var \IPS\Application $app */
            foreach ( $apps as $app ) {
                if ( !\in_array( $app->directory, $ipsApps, \true ) && empty( $app->extensions( 'toolbox', 'SpecialHooks' ) ) === \false ) {
                    $hooks = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/hooks.json';
                    if ( is_file( $hooks ) ) {
                        $hooks = json_decode( \file_get_contents( $hooks ), \true );
                        if ( \is_array( $hooks ) ) {
                            foreach ( $hooks as $file => $hook ) {
                                if ( \mb_strtolower( $hook[ 'type' ] ) === 'c' ) {
                                    $path = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/hooks/' . $file . '.php';
                                    //                                    $content = \file_get_contents( $path );
                                    //                                    //$row['app'] . '_hook_' . $row['filename']
                                    //                                    $content = preg_replace( '#\b(?<![\'|"])_HOOK_CLASS_\b#', '_HOOK_CLASS_' . $app->directory . '_hook_' . $file, $content );
                                    //                                    \file_put_contents( $path, $content );

                                    $originalHook = new StandardTokenizer( $path );
                                    $originalHook->addExtends( '_HOOK_CLASS_' . $app->directory . '_hook_' . $file );
                                    $originalHook->addFileName( $file );
                                    $originalHook->isProxy = true;
                                    $originalHook->isHook = true;
                                    $originalHook->save();

                                    $proxyFile = new ClassGenerator();
                                    $proxyFile->addPath( \IPS\ROOT_PATH . '/' . Proxyclass::i()->save . '/hooks/' . $app->directory );
                                    $proxyFile->addFileName( '_HOOK_CLASS_' . $file );
                                    $proxyFile->addExtends( $hook[ 'class' ] );
                                    $proxyFile->isProxy = true;
                                    $proxyFile->addClassName( '_HOOK_CLASS_' . $app->directory . '_hook_' . $file );
                                    $proxyFile->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function removeSpecialHooks( $precommit = \false )
    {

        if ( !is_array( $this->apps ) && !count( $this->apps ) ) {
            return;
        }

        /** @var Application $app */
        foreach ( $this->apps as $app ) {
            $hooks = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/hooks.json';
            $dir = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/hooks/';
            if ( is_file( $hooks ) ) {
                $hooks = json_decode( \file_get_contents( $hooks ), \true );
                if ( \is_array( $hooks ) ) {
                    foreach ( $hooks as $file => $hook ) {
                        if ( \mb_strtolower( $hook[ 'type' ] ) === 'c' ) {
                            $path = $dir . $file . '.php';
                            $rewriteHook = new StandardTokenizer( $path );
                            $rewriteHook->isProxy = true;
                            $rewriteHook->isHook = true;
                            $rewriteHook->addFileName( $file );
                            $rewriteHook->addExtends( '_HOOK_CLASS_' );
                            $rewriteHook->save();
                            if ( $precommit === \true ) {
                                $this->add( $path, $dir );
                            }
                        }
                    }
                }
            }
        }
    }

    public function add( $file, $dir )
    {

        $output = 'Committing file ' . $file;
        $command = 'add ' . $file;
        $this->exec( $command, $dir, $output );
    }

    public function exec( $command, $dir, &$output = \null )
    {

        if ( \function_exists( 'exec' ) === \true ) {
            $cwd = \getcwd();
            \chdir( $dir );
            \exec( "git $command", $output );
            \chdir( $cwd );
        }
    }
}
