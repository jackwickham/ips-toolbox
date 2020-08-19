<?php

/**
 * @brief       Langs Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Code Analyzer
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Code;

use IPS\Member;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function array_diff;
use function array_keys;
use function count;
use function defined;
use function explode;
use function header;
use function in_array;
use function is_array;
use function mb_substr;
use function preg_match_all;
use function trim;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Langs extends ParserAbstract
{
    /**
     * @var null|array
     */
    protected $langs;

    /**
     * @var null|array
     */
    protected $jslangs;

    /**
     * Array containing keys which should be hidden from the warning list (e.g. we know that __app_foo has to exist,
     * but it won't be used anywhere in the code )
     *
     * @var array
     */
    protected $ignore = [];

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function __construct( $app )
    {
        parent::__construct( $app );
        $this->addKeyToIgnoreList( '__app_' . $this->app->directory );
        $this->getLangs();
        $this->skip = [
            'lang.php',
            'jslang.php',
            'lang.xml',
        ];
    }

    /**
     * Adds a key to the ignore list
     *
     * @param $name
     */
    public function addKeyToIgnoreList( $name )
    {
        $this->ignore[] = $name;
    }

    /**
     * builds the lang strings into $this->langs and $this->jslangs
     *
     * @throws \InvalidArgumentException
     */
    protected function getLangs()
    {
        if ( $this->app === \null ) {
            return;
        }

        $files = new Finder;
        $files->in( $this->appPath . 'dev/' )->files()->name( '*lang.php' );

        /**
         * @var SplFileInfo $langs
         */
        foreach ( $files as $langs ) {
            if ( $langs->getFilename() === 'lang.php' ) {
                $lang = \null;
                require $langs->getRealPath();
                $this->langs = $lang;
            }
            else {
                if ( $langs->getFilename() === 'jslang.php' ) {
                    $lang = \null;
                    require $langs->getRealPath();
                    $this->jslangs = $lang;
                }
            }
        }
    }

    /**
     * checks to see if the language strings are in use
     *
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function check(): array
    {
        if ( $this->files === \null ) {
            return [];
        }

        $content = $this->getContent();
        $keys = is_array( $this->langs ) ? array_keys( $this->langs ) : [];
        $jskeys = is_array( $this->jslangs ) ? array_keys( $this->jslangs ) : [];
        $warning = [];

        /* Remove the ignored language strings like the app name */
        $keys = array_diff( $keys, $this->ignore );

        foreach ( $keys as $find ) {
            preg_match_all( '#[\'|"]' . $find . '[\'|"]#u', $content, $match );

            if ( !count( $match[ 0 ] ) ) {
                $warning[ 'langs' ][] = $find;
            }
        }

        foreach ( $jskeys as $find ) {
            preg_match_all( "#['|\"]" . $find . "['|\"]#u", $content, $match );
            if ( !count( $match[ 0 ] ) ) {
                $warning[ 'jslangs' ][] = $find;
            }
        }
        return $warning;
    }

    /**
     * checks to see the language strings in use are defined.
     *
     * @throws \RuntimeException
     */
    public function verify(): array
    {
        if ( $this->files === \null ) {
            return [];
        }

        $jskeys = is_array( $this->jslangs ) ? $this->jslangs : [];
        $warning = [];

        /**
         * @var SplFileInfo $file
         */
        foreach ( $this->files as $file ) {
            $data = $file->getContents();
            $line = 1;
            $lines = explode( "\n", $data );
            $name = $file->getRealPath();
            foreach ( $lines as $content ) {
                $path = $this->buildPath( $name, $line );

                if ( $file->getExtension() === 'phtml' ) {
                    $matches = [];
                    preg_match_all( "#{lang=['|\"](.*?)['|\"]#u", $content, $matches );
                    if ( isset( $matches[ 1 ] ) && count( $matches[ 1 ] ) ) {
                        /* @var array $found */
                        $found = $matches[ 1 ];
                        foreach ( $found as $key => $val ) {
                            $val = trim( $val );
                            if ( $val && ( !in_array( mb_substr( $val, 0, 1 ), [
                                    '$',
                                    '{',
                                ] ) ) && !Member::loggedIn()->language()->checkKeyExists( $val ) ) {
                                $warning[] = [ 'file' => $name, 'key' => $val, 'line' => $line, 'path' => $path ];
                            }
                        }
                    }
                }

                if ( $file->getExtension() === 'php' ) {
                    $matches = [];
                    preg_match_all( '/addToStack\((?:\s)[\'|"](.*?)[\'|"]/u', $content, $matches );
                    if ( isset( $matches[ 1 ] ) && count( $matches[ 1 ] ) ) {
                        /* @var array $found */
                        $found = $matches[ 1 ];
                        foreach ( $found as $key => $val ) {
                            $val = trim( $val );
                            if ( $val && ( !in_array( mb_substr( $val, 0, 1 ), [
                                    '$',
                                    '{',
                                ] ) ) && !Member::loggedIn()->language()->checkKeyExists( $val ) ) {
                                $warning[] = [ 'file' => $name, 'key' => $val, 'line' => $line, 'path' => $path ];
                            }
                        }
                    }

                    $matches = [];
                    preg_match_all( '/->get\((?:\s)[\'|"](.*?)[\'|"]/u', $content, $matches );
                    if ( isset( $matches[ 1 ] ) && count( $matches[ 1 ] ) ) {
                        /* @var array $found */
                        $found = $matches[ 1 ];
                        foreach ( $found as $key => $val ) {
                            $val = trim( $val );
                            if ( $val && ( !in_array( mb_substr( $val, 0, 1 ), [
                                    '$',
                                    '{',
                                ] ) ) && !Member::loggedIn()->language()->checkKeyExists( $val ) ) {
                                $warning[] = [ 'file' => $name, 'key' => $val, 'line' => $line, 'path' => $path ];
                            }
                        }
                    }
                }

                if ( $file->getExtension() === 'js' ) {
                    $matches = [];
                    preg_match_all( '/getString\((?:\s)[\'|"](.*?)[\'|"]/u', $content, $matches );
                    /**
                     * @var array $matches
                     */
                    if ( isset( $matches[ 1 ] ) && count( $matches[ 1 ] ) ) {
                        /* @var array $found */
                        $found = $matches[ 1 ];
                        foreach ( $found as $key => $val ) {
                            $val = trim( $val );
                            if ( $val && ( !in_array( mb_substr( $val, 0, 1 ), [
                                    '$',
                                    '{',
                                ] ) ) && ( !isset( $jskeys[ $val ] ) && !Member::loggedIn()->language()->checkKeyExists( $val ) ) ) {
                                $warning[] = [ 'file' => $name, 'key' => $val, 'line' => $line, 'path' => $path ];
                            }
                        }
                    }
                }

                $line++;

            }
        }

        return $warning;
    }
}
