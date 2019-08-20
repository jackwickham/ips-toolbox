//<?php

use IPS\Data\Store;
use IPS\Request;
use IPS\Settings;
use IPS\toolbox\Application;
use IPS\toolbox\Editor;
use IPS\toolbox\Profiler\Memory;
use IPS\toolbox\Profiler\Time;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class toolbox_hook_DevTemplate extends _HOOK_CLASS_
{
    public static $debugFileName = '\null';

    public function __call( $bit, $params )
    {

        static::$debugFileName = \null;
        $template = \null;
        if ( !Request::i()->isAjax() && \IPS\QUERY_LOG && Settings::i()->dtprofiler_enabled_templates && $this->app !== 'dtprofiler' && ( $this->app === 'core' && $bit !== 'cachingLog' ) ) {
            $name = \null;
            $app = $this->app;
            $group = $this->templateName;
            $location = $this->templateLocation;
            $memory = \null;
            $time = \null;
            if ( Settings::i()->dtprofiler_enabled_memory ) {
                $memory = new Memory;
            }

            if ( Settings::i()->dtprofiler_enabled_executions ) {
                $time = new Time;
            }
        }

        if ( \IPS\IN_DEV === \true && \IPS\NO_WRITES === \false && Settings::i()->toolbox_debug_templates ) {
            $functionName = "theme_{$this->app}\_{$this->templateLocation}\_{$this->templateName}\_{$bit}";
            /* Find the file */
            $file = \null;
            if ( $this->sourceFolder === \IPS\ROOT_PATH . '/plugins' ) {
                foreach ( new \GlobIterator( $this->sourceFolder . '/*/dev/html/' . $bit . '.phtml' ) as $file ) {
                    break;
                }
            }
            else {
                $file = $this->sourceFolder . $bit . '.phtml';
            }

            /* Get the content or return an BadMethodCallException if the template doesn't exist */
            if ( $file === \null || !\file_exists( $file ) ) {
                if ( !$file && $this->sourceFolder === \IPS\ROOT_PATH . '/plugins' ) {
                    throw new \BadMethodCallException( 'NO_PLUGIN_TEMPLATE_FILE - ' . $bit );
                }

                throw new \BadMethodCallException( 'NO_TEMPLATE_FILE - ' . $file );
            }

            $path = \IPS\ROOT_PATH . '/toolbox_templates/';

            $mtime = 0;
            if ( !\in_array( $this->app, Application::$ipsApps, \true ) ) {
                $mtime = \filemtime( $file );
            }

            $hookData = \IPS\IPS::$hooks;
            if ( isset( $hookData[ $bit ] ) ) {
                $hooks = $hookData[ $bit ];
                foreach ( $hooks as $hook ) {
                    $mtime += \filemtime( \IPS\ROOT_PATH . $hook[ 'file' ] );
                }
            }

            static::$debugFileName = $filename = $functionName . $mtime . '.php';

            if ( \file_exists( $path . $filename ) ) {
                include_once( $path . $filename );

                if ( \function_exists( 'IPS\Theme\\' . $functionName ) ) {
                    /* Run it */
                    \ob_start();
                    $function = 'IPS\\Theme\\' . $functionName;
                    $template = $function( ...$params );
                    if ( $error = \ob_get_clean() ) {
                        $output = \file_get_contents( $file );
                        echo "<strong>{$functionName}</strong><br>{$error}<br><br><pre>{$output}";
                        exit;
                    }
                }
            }
            else {
                //this is to clear files that are no longer valid
                try {
                    Application::loadAutoLoader();
                    $finder = new Finder();
                    $finder->in( $path )->files()->name( $functionName . '*.php' );
                    $fs = new Filesystem;
                    foreach ( $finder as $f ) {
                        $fs->remove( $f->getRealPath() );
                    }
                } catch ( \Exception $e ) {
                }
                $template = parent::__call( $bit, $params );
            }
        }
        else {
            $template = parent::__call( $bit, $params );
        }

        if ( \IPS\IN_DEV === \true && !Request::i()->isAjax() && \IPS\QUERY_LOG && Settings::i()->dtprofiler_enabled_templates && $this->app !== 'dtprofiler' && ( $this->app === 'core' && $bit !== 'cachingLog' ) && $this->app !== 'dtprofiler' ) {
            if ( Settings::i()->dtprofiler_enabled_memory || Settings::i()->dtprofiler_enabled_executions ) {
                $path = \IPS\ROOT_PATH . '/applications/' . $app . '/dev/html/' . $location . '/' . $group . '/' . $bit . '.phtml';
                $url = ( new Editor )->replace( $path );
                $name = $app . ' -> ' . $location . ' -> ' . $bit;

            }

            if ( Settings::i()->dtprofiler_enabled_executions ) {
                $time->end( $url, $name );
            }

            if ( Settings::i()->dtprofiler_enabled_memory ) {
                $memory->end( $url, $name );
            }

            if ( isset( Store::i()->dtprofiler_templates ) ) {
                $log = Store::i()->dtprofiler_templates;
            }

            $log[] = [
                'name'     => $bit,
                'group'    => $group,
                'location' => $location,
                'app'      => $app,
            ];

            Store::i()->dtprofiler_templates = $log;
        }

        if ( $template === \null ) {

            static::$debugFileName = \null;

            return parent::__call( $bit, $params );
        }

        return $template;
    }

    protected function buildTemplateCache()
    {

        $cachePath = \IPS\ROOT_PATH . '/toolbox_templates/';
        if ( !\is_dir( $cachePath ) ) {
            \mkdir( $cachePath, 0777, \true );
        }
        $classFile = "{$this->app}\_{$this->templateLocation}\_{$this->templateName}";
        $path = \IPS\ROOT_PATH . '/applications/' . $this->app . '/dev/html/' . $this->templateLocation . '/' . $this->templateName . '/';

        $filter = function ( \SplFileInfo $file )
        {

            if ( !\in_array( $file->getExtension(), [ 'phtml' ] ) ) {
                return \false;
            }

            return \true;
        };
        $mtime = 0;

        if ( !\in_array( $this->app, Application::$ipsApps, \true ) ) {
            $files = new Finder();
            $files->in( $path );
            $files->filter( $filter )->files();
            foreach ( $files as $file ) {
                $mtime += $file->getMTime();
            }
        }

        $classFileName = $cachePath . $classFile . $mtime . '.php';

        if ( !\file_exists( $classFileName ) ) {
            $func = [];
            $files = new Finder;
            $files->in( $path );
            $files->filter( $filter )->files();

            foreach ( $files as $file ) {
                $functionName = $file->getBasename( '.phtml' );

                $content = $file->getContents();
                if ( !\preg_match( '/^<ips:template parameters="(.+?)?"(\s+)?\/>(\r\n?|(\r\n?|\n))/', $content, $matches ) ) {
                    throw new \BadMethodCallException( 'NO_HEADER - ' . $file->getBasename() );
                }

                $params = $matches[ 1 ] ?? '';

                $content = \preg_replace( '/^<ips:template parameters="(.+?)?"(\s+)?\/>(\r\n?|\n)/', '', $content );

                $func[] = \IPS\Theme::compileTemplate( $content, $functionName, $params, \true, \false );

            }

            $func = \implode( "\n", $func );

            $classContent = <<<EOF
<?php

namespace IPS\Theme\Templates;

class {$classFile} {
{$func}
}
EOF;

            \file_put_contents( $classFileName, $classContent );
        }

        include_once( $classFileName );
    }

}




















