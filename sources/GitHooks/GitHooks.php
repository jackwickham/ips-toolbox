<?php

namespace IPS\toolbox;

Application::loadAutoLoader();;

use Exception;
use Generator\Tokenizers\StandardTokenizer;
use IPS\IPS;
use Generator\Builders\ClassGenerator;
use IPS\toolbox\Proxy\Proxyclass;
use function chdir;
use function count;
use function exec;
use function file_get_contents;
use function function_exists;
use function getcwd;
use function in_array;
use function is_array;
use function is_file;
use function json_decode;
use function mb_strtolower;
use function preg_replace;
use function property_exists;
use const IPS\ROOT_PATH;

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
            } catch ( Exception $e ) {
            }
        }
    }

    public function writeSpecialHooks(): void
    {

        if ( property_exists( IPS::class, 'beenPatched' ) && IPS::$beenPatched ) {
            $apps = $this->apps;
            $ipsApps = \IPS\Application::$ipsApps;
            /** @var \IPS\Application $app */
            foreach ( $apps as $app ) {
                if ( !in_array( $app->directory, $ipsApps, true ) && empty( $app->extensions( 'toolbox', 'SpecialHooks' ) ) === false ) {
                    $hooks = ROOT_PATH . '/applications/' . $app->directory . '/data/hooks.json';
                    if ( is_file( $hooks ) ) {
                        $hooks = json_decode( file_get_contents( $hooks ), true );
                        if ( is_array( $hooks ) ) {
                            foreach ( $hooks as $file => $hook ) {
                                if ( mb_strtolower( $hook[ 'type' ] ) === 'c' ) {
                                    $path = ROOT_PATH . '/applications/' . $app->directory . '/hooks/' . $file . '.php';
                                    $originalHook = new StandardTokenizer( $path );
                                    $originalHook->addExtends( '_HOOK_CLASS_' . $app->directory . '_hook_' . $file );
                                    $originalHook->addFileName( $file );
                                    $originalHook->isProxy = true;
                                    $originalHook->isHook = true;
                                    $originalHook->save();

                                    $proxyFile = new ClassGenerator();
                                    $proxyFile->addPath( ROOT_PATH . '/' . Proxyclass::i()->save . '/hooks/' . $app->directory );
                                    $proxyFile->addFileName( '_HOOK_CLASS_' . $app->directory . '_hook_' . $file );
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

    public function removeSpecialHooks( $precommit = false ): void
    {

        //foo
        if ( !is_array( $this->apps ) && !count( $this->apps ) ) {
            return;
        }

        /** @var Application $app */
        foreach ( $this->apps as $app ) {
            if ( empty( $app->extensions( 'toolbox', 'SpecialHooks' ) ) === true ) {
                continue;
            }
            $hooks = ROOT_PATH . '/applications/' . $app->directory . '/data/hooks.json';
            $dir = ROOT_PATH . '/applications/' . $app->directory . '/hooks/';
            if ( is_file( $hooks ) ) {
                $hooks = json_decode( file_get_contents( $hooks ), true );
                if ( is_array( $hooks ) ) {
                    foreach ( $hooks as $file => $hook ) {
                        if ( mb_strtolower( $hook[ 'type' ] ) === 'c' ) {
                            $path = $dir . $file . '.php';
                            $rewriteHook = new StandardTokenizer( $path );
                            if ( $rewriteHook->getExtends() === '_HOOK_CLASS_' ) {
                                continue;
                            }
                            $rewriteHook->isProxy = true;
                            $rewriteHook->isHook = true;
                            $rewriteHook->addFileName( $file );
                            $rewriteHook->addExtends( '_HOOK_CLASS_' );
                            $rewriteHook->save();
                            if ( $precommit === true ) {
                                $this->add( $path, $dir );
                            }
                        }
                    }
                }
            }
        }
    }

    public function add( $file, $dir ): void
    {

        $output = 'Committing file ' . $file;
        $command = 'add ' . $file;
        $this->exec( $command, $dir, $output );
    }

    public function exec( $command, $dir, &$output = null ): void
    {

        if ( function_exists( 'exec' ) === true ) {
            $cwd = getcwd();
            chdir( $dir );
            exec( "git $command", $output );
            chdir( $cwd );
        }
    }
}
