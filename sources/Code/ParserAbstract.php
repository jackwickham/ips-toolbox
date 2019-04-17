<?php

/**
 * @brief       ParserAbstract Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Code Analyzer
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Code;

use Exception;
use IPS\Application;
use IPS\toolbox\Editor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function defined;
use function header;
use function in_array;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

abstract class _ParserAbstract
{
    /**
     * @var Finder
     */
    protected $files = [];

    /**
     * @var null|string
     */
    protected $appPath;

    /**
     * @var Application
     */
    protected $app;

    /**
     * a list of files to skip
     *
     * @var array
     */
    protected $skip = [];

    /**
     * _ParserAbstract constructor.
     *
     * @param $app
     */
    public function __construct( $app )
    {
        try {
            \IPS\toolbox\Application::loadAutoLoader();
            if ( !( $app instanceof Application ) ) {
                $app = Application::load( $app );
            }
            $this->app = $app;
            $this->appPath = \IPS\ROOT_PATH . '/applications/' . $this->app->directory . '/';
            $this->getFiles();
        } catch ( Exception $e ) {
        }
    }

    /**
     * gathers all the files in an app directory except the lang.php, jslang.php and lang.xml
     *
     * @throws \InvalidArgumentException
     */
    protected function getFiles()
    {
        $files = new Finder;
        $files->in( $this->appPath )->files();
        if ( $this->skip !== \null ) {
            foreach ( $this->skip as $name ) {
                $files->notName( $name );
            }
        }

        $filter = function ( \SplFileInfo $file )
        {
            if ( !in_array( $file->getExtension(), [ 'php', 'phtml', 'js' ] ) ) {
                return \false;
            }
            return \true;
        };

        $files->filter( $filter );

        $this->files = $files;
    }

    /**
     * looks for usage in app files
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function check(): array
    {
        return [];
    }

    /**
     * looks for used to see if they are defined
     *
     * @return array
     */
    public function verify(): array
    {
        return [];
    }

    /**
     * stores all the contents from the files for searching thru.
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getContent(): string
    {
        $content = '';
        /**
         * @var SplFileInfo $file
         */
        foreach ( $this->files as $file ) {
            $content .= $file->getContents();
        }
        return $content;
    }

    /**
     * builds a url for the file to open it up in an editor
     *
     * @param $path
     * @param $line
     *
     * @return mixed|null
     */
    protected function buildPath( $path, $line )
    {
        return ( new Editor )->replace( $path, $line );
    }
}
