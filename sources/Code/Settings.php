<?php

/**
 * @brief       Settings Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Code Analyzer
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Code;

use IPS\Application;
use Symfony\Component\Finder\SplFileInfo;
use function count;
use function defined;
use function explode;
use function file_get_contents;
use function header;
use function in_array;
use function is_file;
use function json_decode;
use function mb_substr;
use function preg_match_all;
use function trim;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Settings extends ParserAbstract
{
    /**
     * contains a list of all the settings currently in the suite
     *
     * @var array
     */
    protected $globalSettings = [];

    /**
     * contains a list of all the settings for current application
     *
     * @var array
     */
    protected $appSettings = [];

    /**
     * {@inheritdoc}
     */
    public function __construct( $app )
    {
        parent::__construct( $app );
        $this->skip = [
            'settings.json',
        ];
    }

    /**
     * gathers the defined settings for all the apps and current app, include conf.global settings
     *
     * @return $this
     */
    public function buildSettings(): self
    {
        if ( $this->app === \null ) {
            return $this;
        }

        foreach ( Application::applications() as $app ) {
            $dir = \IPS\ROOT_PATH . '/applications/' . $app->directory . '/data/settings.json';
            if ( is_file( $dir ) ) {
                /**
                 * @var array $appSettings
                 */
                $appSettings = json_decode( file_get_contents( $dir ), \true );
                foreach ( $appSettings as $setting ) {
                    $this->globalSettings[ $setting[ 'key' ] ] = $setting[ 'key' ];
                }
            }
        }

        $dir = \IPS\ROOT_PATH . '/applications/' . $this->app->directory . '/data/settings.json';
        if ( is_file( $dir ) ) {
            $settings = json_decode( file_get_contents( $dir ), \true ) ?? [];
            foreach ( $settings as $setting ) {
                $this->appSettings[ $setting[ 'key' ] ] = $setting[ 'key' ];
            }
        }

        $INFO = [];
        require \IPS\ROOT_PATH . '/conf_global.php';
        if ( $INFO ) {
            foreach ( $INFO as $key => $val ) {
                $this->globalSettings[ $key ] = $key;
            }
        }

        return $this;
    }


    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function check(): array
    {
        if ( $this->files === \null ) {
            return [];
        }

        $content = $this->getContent();
        $warning = [];
        foreach ( $this->appSettings as $find ) {
            preg_match_all( '#' . $find . '#u', $content, $match );
            if ( !count( $match[ 0 ] ) ) {
                $warning[] = $find;
            }
        }
        return $warning;
    }

    /**
     * @inheritdoc
     * @return array
     * @throws \RuntimeException
     */
    public function verify(): array
    {
        $warning = [];

        /**
         * @var SplFileInfo $file
         */
        foreach ( $this->files as $file ) {
            $data = $file->getContents();
            $lines = explode( "\n", $data );
            $line = 1;
            $name = $file->getRealPath();
            foreach ( $lines as $content ) {
                $path = $this->buildPath( $name, $line );
                $line++;
                if ( $file->getExtension() === 'phtml' ) {
                    $matches = [];
                    preg_match_all( "#\bsettings.([^\s|\W]+)#u", $content, $matches );
                    if ( isset( $matches[ 1 ] ) && count( $matches[ 1 ] ) ) {
                        /* @var array $found */
                        $found = $matches[ 1 ];
                        foreach ( $found as $key => $val ) {
                            $val = trim( $val );
                            if ( $val === 'base_url' || $val === 'changeValues(' || $val === 'changeValues' ) {
                                continue;
                            }
                            if ( $val && !isset( $this->globalSettings[ $val ] ) && ( !in_array( mb_substr( $val, 0, 1 ), [
                                        '$',
                                        '{',
                                    ] ) ) ) {
                                $warning[] = [ 'file' => $name, 'key' => $val, 'line' => $line, 'path' => $path ];
                            }
                        }
                    }
                }

                if ( $file->getExtension() === 'php' ) {
                    $matches = [];
                    preg_match_all( '#Settings::i\(\)->([^\s|\W]+)#u', $content, $matches );
                    if ( isset( $matches[ 1 ] ) && count( $matches[ 1 ] ) ) {
                        /* @var array $found */
                        $found = $matches[ 1 ];
                        foreach ( $found as $key => $val ) {
                            $val = trim( $val );
                            if ( $val === 'base_url' || $val === 'changeValues(' || $val === 'changeValues' ) {
                                continue;
                            }
                            if ( $val && !isset( $this->globalSettings[ $val ] ) && ( !in_array( mb_substr( $val, 0, 1 ), [
                                        '$',
                                        '{',
                                    ] ) ) ) {
                                $warning[] = [ 'file' => $name, 'key' => $val, 'line' => $line, 'path' => $path ];
                            }
                        }
                    }
                }
            }
        }
        return $warning;
    }
}
