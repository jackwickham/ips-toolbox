<?php

/**
 * @brief       Apps Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Folders
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevFolder;

use IPS\Application;
use IPS\Member;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Shared\Write;
use IPS\Xml\XMLReader;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;
use function base64_decode;
use function closedir;
use function copy;
use function count;
use function defined;
use function header;
use function in_array;
use function is_array;
use function is_dir;
use function ksort;
use function mkdir;
use function opendir;
use function readdir;
use function set_time_limit;
use function sprintf;
use function var_export;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Applications
{
    use Write;

    /**
     * @var bool
     */
    public $addToStack = \false;

    /**
     * @var Application|null
     */
    protected $app;

    /**
     * @var null|string
     */
    protected $dir;

    /**
     * @var null|string
     */
    protected $dev;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * _Apps constructor.
     *
     * @param $app
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     * @throws \Exception
     */
    final public function __construct( $app )
    {
        set_time_limit( 0 );
        $fs = new Filesystem;
        if ( !( $app instanceof Application ) ) {
            $this->app = Application::load( $app );
        }
        else {
            $this->app = $app;
        }

        $this->dir = \IPS\ROOT_PATH . '/applications/' . $this->app->directory;
        $this->dev = $this->dir . '/dev/';

        if ( in_array( $app, Application::$ipsApps, \true ) ) {
            $fs->remove( $this->dev );
        }

        if ( !$fs->exists( $this->dev ) ) {
            $fs->mkdir( $this->dev, \IPS\IPS_FOLDER_PERMISSION );
        }

        $this->fs = $fs;
    }

    /**
     * @return static
     */
    public function javascript()
    {
        $order = [];
        $path = $this->dev . 'js/';
        $this->_writeFile( 'index.html', '', $path );

        $xml = new XMLReader;
        $xml->open( $this->dir . '/data/javascript.xml' );
        $xml->read();
        try {
            if ( !$this->fs->exists( $path ) ) {
                $this->fs->mkdir( $path, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( IOException $e ) {
        }

        while ( $xml->read() ) {
            if ( $xml->nodeType !== XMLReader::ELEMENT ) {
                continue;
            }
            $file = \null;
            if ( $xml->name === 'file' ) {
                $loc = $path . $xml->getAttribute( 'javascript_location' );
                $loc .= '/' . $xml->getAttribute( 'javascript_path' );
                $order[ $path ][ $xml->getAttribute( 'javascript_position' ) ] = $xml->getAttribute( 'javascript_name' );
                if ( $file === \null ) {
                    $file = $xml->getAttribute( 'javascript_name' );
                }

                $content = $xml->readString();
                $this->_writeFile( $file, $content, $loc );
            }
        }

        $txt = 'order.txt';

        if ( is_array( $order ) && count( $order ) ) {
            foreach ( $order as $key => $val ) {
                $content = '';
                if ( is_array( $val ) && count( $val ) ) {
                    ksort( $val );
                    foreach ( $val as $k => $v ) {
                        $content .= $v . \PHP_EOL;
                    }
                }

                $this->_writeFile( $txt, $content, $key );
            }
        }

        return $this;
    }

    /**
     * @return static
     * @throws \Exception
     */
    public function templates()
    {
        $cssDir = $this->dev . 'css';
        $html = $this->dev . 'html';
        $resources = $this->dev . 'resources';
        $this->_writeFile( 'index.html', '', $cssDir );
        $this->_writeFile( 'index.html', '', $html );
        $this->_writeFile( 'index.html', '', $resources );

        $xml = new XMLReader;
        $xml->open( $this->dir . '/data/theme.xml' );
        $xml->read();

        try {
            if ( !$this->fs->exists( $cssDir ) ) {
                $this->fs->mkdir( $cssDir, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( IOException $e ) {
        }

        try {
            if ( !$this->fs->exists( $html ) ) {
                $this->fs->mkdir( $html, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( IOException $e ) {
        }

        try {
            if ( !$this->fs->exists( $resources ) ) {
                $this->fs->mkdir( $resources, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( IOException $e ) {
        }

        while ( $xml->read() ) {
            if ( $xml->nodeType !== XMLReader::ELEMENT ) {
                continue;
            }

            if ( $xml->name === 'template' ) {
                $template = [
                    'group'     => $xml->getAttribute( 'template_group' ),
                    'name'      => $xml->getAttribute( 'template_name' ),
                    'variables' => $xml->getAttribute( 'template_data' ),
                    'content'   => $xml->readString(),
                    'location'  => $xml->getAttribute( 'template_location' ),
                ];

                $location = $html . '/' . $template[ 'location' ] . '/';
                $path = $location . $template[ 'group' ] . '/';
                $file = $template[ 'name' ] . '.phtml';
                $header = '<ips:template parameters="' . $template[ 'variables' ] . '" />' . \PHP_EOL;
                $content = $header . $template[ 'content' ];
                $content = \IPS\toolbox\Application::templateSlasher( $content );
                $this->_writeFile( $file, $content, $path );
            }
            else if ( $xml->name === 'css' ) {
                $css = [
                    'location' => $xml->getAttribute( 'css_location' ),
                    'path'     => $xml->getAttribute( 'css_path' ),
                    'name'     => $xml->getAttribute( 'css_name' ),
                    'content'  => $xml->readString(),
                ];

                $location = $cssDir . '/' . $css[ 'location' ] . '/';

                if ( $css[ 'path' ] === '.' ) {
                    $path = $location;
                }
                else {
                    $path = $location . $css[ 'path' ] . '/';
                }

                $file = $css[ 'name' ];
                $this->_writeFile( $file, $css[ 'content' ], $path );
            }
            else if ( $xml->name === 'resource' ) {
                $resource = [
                    'location' => $xml->getAttribute( 'location' ),
                    'path'     => $xml->getAttribute( 'path' ),
                    'name'     => $xml->getAttribute( 'name' ),
                    'content'  => base64_decode( $xml->readString() ),
                ];

                $location = $resources . '/' . $resource[ 'location' ] . '/';
                $path = $location . $resource[ 'path' ] . '/';
                $file = $resource[ 'name' ];
                $this->_writeFile( $file, $resource[ 'content' ], $path );
            }
        }
        return $this;
    }


    /**
     * @return static
     */
    public function email()
    {
        $email = $this->dev . 'email/';
        $this->_writeFile( 'index.html', '', $email );

        $xml = new XMLReader;
        $xml->open( $this->dir . '/data/emails.xml' );
        $xml->read();

        try {
            if ( !$this->fs->exists( $email ) ) {
                $this->fs->mkdir( $email, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( IOException $e ) {
        }

        while ( $xml->read() && $xml->name === 'template' ) {
            if ( $xml->nodeType !== XMLReader::ELEMENT ) {
                continue;
            }

            $insert = [];

            while ( $xml->read() && $xml->name !== 'template' ) {
                if ( $xml->nodeType !== XMLReader::ELEMENT ) {
                    continue;
                }

                switch ( $xml->name ) {
                    case 'template_name':
                        $insert[ 'template_name' ] = $xml->readString();
                        break;
                    case 'template_data':
                        $insert[ 'template_data' ] = $xml->readString();
                        break;
                    case 'template_content_html':
                        $insert[ 'template_content_html' ] = $xml->readString();
                        break;
                    case 'template_content_plaintext':
                        $insert[ 'template_content_plaintext' ] = $xml->readString();
                        break;
                }
            }

            $header = '<ips:template parameters="' . $insert[ 'template_data' ] . '" />' . \PHP_EOL;

            if ( isset( $insert[ 'template_content_plaintext' ] ) ) {
                $plainText = $header . $insert[ 'template_content_plaintext' ];
                $this->_writeFile( $insert[ 'template_name' ] . '.txt', $plainText, $email );
            }

            if ( isset( $insert[ 'template_content_html' ] ) ) {
                $plainText = $header . $insert[ 'template_content_html' ];
                $this->_writeFile( $insert[ 'template_name' ] . '.phtml', $plainText, $email );
            }
        }
        return $this;
    }

    /**
     * @return static
     */
    public function language()
    {
        $xml = new XMLReader;
        $xml->open( $this->dir . '/data/lang.xml' );
        $xml->read();
        $xml->read();
        $xml->read();
        $lang = [];
        $langJs = [];
        $member = Member::loggedIn()->language();
        /* Start looping through each word */
        while ( $xml->read() ) {
            if ( $xml->name !== 'word' || $xml->nodeType !== XMLReader::ELEMENT ) {
                continue;
            }

            $key = $xml->getAttribute( 'key' );
            $value = $xml->readString();
            $js = (int)$xml->getAttribute( 'js' );

            if ( $js ) {
                $langJs[ $key ] = $value;
            }
            else {
                $lang[ $key ] = $value;
            }
            if ( $this->addToStack ) {
                $member->words[ $key ] = $value;
            }
        }

        $langFile = new DTFileGenerator;
        $langFile->setFilename( $this->dev . '/lang.php' );
        $langFile->setBody( '$lang=' . var_export( $lang, \true ) . ";" );
        $langFile->write();

        $langFile->setFilename( $this->dev . '/jslang.php' );
        $langFile->setBody( '$lang=' . var_export( $langJs, \true ) . ";" );
        $langFile->write();

        return $this;
    }

    /**
     * @throws \Exception
     * @throws IOException
     * @throws RuntimeException
     */
    public function core()
    {
        $packageDir = \IPS\ROOT_PATH . '/dev/';
        $cke = \IPS\ROOT_PATH . '/applications/core/dev/ckeditor/';

        if ( !is_dir( $cke ) && !mkdir( $cke, 0777, \true ) && !is_dir( $cke ) ) {
            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $cke ) );
        }
        $this->recurseCopy( \IPS\ROOT_PATH . '/applications/core/interface/ckeditor/ckeditor/', $cke );

        $cm = \IPS\ROOT_PATH . '/applications/core/dev/codemirror/';

        if ( !is_dir( $cm ) && !mkdir( $cm, 0777, \true ) && !is_dir( $cm ) ) {
            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $cm ) );
        }

        $this->recurseCopy( \IPS\ROOT_PATH . '/applications/core/interface/codemirror/', $cm );
        if ( is_dir( $packageDir . 'Whoops/' ) ) {
            $fs = new Filesystem();
            $fs->remove( $packageDir . 'Whoops/' );
        }

        if ( !is_dir( $packageDir ) && !mkdir( $packageDir, 0777, \true ) && !is_dir( $packageDir ) ) {
            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $packageDir ) );
        }

        $download = 'https://github.com/filp/whoops/archive/master.zip';
        $file = \file_get_contents( $download );
        $newFile = \IPS\ROOT_PATH . '/dev/master.zip';
        \file_put_contents( $newFile, $file );
        $zip = new ZipArchive;
        $res = $zip->open( $newFile );
        if ( $res === \true ) {
            $zip->extractTo( $packageDir );
            $this->recurseCopy( $packageDir . '/whoops-master/src/Whoops/', $packageDir . '/Whoops/' );
            copy( \IPS\ROOT_PATH . '/applications/dtdevfolder/sources/Apps/function_overrides.php', $packageDir . '/function_overrides.php' );
            $fs->remove( $packageDir . '/whoops-master/' );

        }
        $zip->close();

        @\unlink( $newFile );
    }

    /**
     * @param $src
     * @param $dst
     *
     * @throws \Exception
     * @throws RuntimeException
     */
    protected function recurseCopy( $src, $dst )
    {
        $dir = opendir( $src );
        if ( !mkdir( $dst ) && !is_dir( $dst ) ) {
            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $dst ) );
        }
        while ( \false !== ( $file = readdir( $dir ) ) ) {
            if ( ( $file != '.' ) && ( $file != '..' ) ) {
                if ( is_dir( $src . '/' . $file ) ) {
                    $this->recurseCopy( $src . '/' . $file, $dst . '/' . $file );
                }
                else {
                    copy( $src . '/' . $file, $dst . '/' . $file );
                }
            }
        }
        closedir( $dir );
    }
}
