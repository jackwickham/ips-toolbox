<?php

/**
 * @brief       Plugins Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Folders
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevFolder;

use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Patterns\Singleton;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Shared\Write;
use IPS\Xml\XMLReader;
use function base64_decode;
use function count;
use function defined;
use function header;
use function is_file;
use function json_encode;
use function mb_strtolower;
use function preg_replace;
use function unlink;
use function var_export;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Plugins extends Singleton
{
    use Write;

    public static $instance;

    /**
     * @param bool $file
     *
     * @throws \RuntimeException
     */
    public function finish( $file = \false )
    {
        $return = $this->build( $file );
        @unlink( $file );

        $message = Member::loggedIn()->language()->addToStack( $return[ 'msg' ], \false, [ 'sprintf' => [ $return[ 'name' ] ] ] );
        $url = Url::internal( 'app=toolbox&module=devfolder&controller=plugins' );
        Output::i()->redirect( $url, $message );
    }

    /**
     * @param $plugin
     *
     * @return array
     * @throws \RuntimeException
     */
    public function build( $plugin ): array
    {
        \IPS\toolbox\Application::loadAutoLoader();
        $xml = new XMLReader;
        $xml->open( $plugin );
        $xml->read();
        $plugins = \IPS\ROOT_PATH . '/plugins/';
        $versions = [];
        $lang = [];
        $langJs = [];
        $settings = [];
        $return = 'dtdevfolder_plugins_done';
        $oriName = $xml->getAttribute( 'name' );
        $xml->getAttribute( 'author' );
        $name = mb_strtolower( preg_replace( '#[^a-zA-Z0-9_]#', '', $oriName ) );
        $pluginName = $oriName;
        $folder = $plugins . $name . '/dev/';
        $html = $folder . 'html/';
        $css = $folder . 'css/';
        $js = $folder . 'js/';
        $resources = $folder . 'resources/';
        $setup = $folder . 'setup/';
        $hooks = $plugins . $name . '/hooks/';
        $widgets = [];

        /**
         * create folders with a blank index.html first
         */
        $this->_createDir( $folder );
        $this->_createDir( $html );
        $this->_createDir( $css );
        $this->_createDir( $js );
        $this->_createDir( $resources );
        $this->_createDir( $setup );
        $this->_writeFile( 'index.html', '', $hooks );

        if ( !is_file( $plugins . $name . '/settings.php' ) ) {
            $settingsBlank = <<<'EOF'
//<?php

$form->add( new \IPS\Helpers\Form\Text( 'plugin_example_setting', \IPS\Settings::i()->plugin_example_setting ) );

if ( $values = $form->values() )
{
	$form->saveAsSettings();
	return TRUE;
}

return $form;
EOF;

            $this->_writeFile( 'settings.rename.php', $settingsBlank, $plugins . $name );
        }
        //        $this->_writeFile

        while ( $xml->read() ) {
            if ( $xml->nodeType !== XMLReader::ELEMENT ) {
                continue;
            }
            if ( $xml->name === 'html' ) {
                $filename = $xml->getAttribute( 'filename' );
                $content = base64_decode( $xml->readString() );
                $content = \IPS\toolbox\Application::templateSlasher( $content );
                $this->_writeFile( $filename, $content, $html );
            }

            if ( $xml->name === 'css' ) {
                $filename = $xml->getAttribute( 'filename' );
                $content = base64_decode( $xml->readString() );
                $this->_writeFile( $filename, $content, $css );
            }

            if ( $xml->name === 'js' ) {
                $filename = $xml->getAttribute( 'filename' );
                $content = base64_decode( $xml->readString() );
                $this->_writeFile( $filename, $content, $js );
            }

            if ( $xml->name === 'resources' ) {
                $filename = $xml->getAttribute( 'filename' );
                $content = base64_decode( $xml->readString() );
                $this->_writeFile( $filename, $content, $resources );
            }

            if ( $xml->name === 'version' ) {
                $versions[ $xml->getAttribute( 'long' ) ] = $xml->getAttribute( 'human' );
                $content = $xml->readString();
                if ( $xml->getAttribute( 'long' ) === '10000' ) {
                    $name = 'install.php';
                }
                else {
                    $name = $xml->getAttribute( 'long' ) . '.php';
                }
                $this->_writeFile( $name, $content, $setup );
            }

            if ( $xml->name === 'setting' ) {
                $xml->read();
                $key = $xml->readString();
                $xml->next();
                $value = $xml->readString();
                $settings[] = [ 'key' => $key, 'default' => $value ];
            }

            if ( $xml->name === 'word' ) {
                $key = $xml->getAttribute( 'key' );
                $value = $xml->readString();
                $jsW = (int)$xml->getAttribute( 'js' );

                if ( $jsW ) {
                    $langJs[ $key ] = $value;
                }
                else {
                    $lang[ $key ] = $value;
                }
            }

            if ( $xml->name === 'widget' ) {
                $widgets[ $xml->getAttribute( 'key' ) ] = [
                    'class'        => $xml->getAttribute( 'class' ),
                    'restrict'     => $xml->getAttribute( 'restrict' ),
                    'default_area' => $xml->getAttribute( 'default_area' ),
                    'allow_reuse'  => $xml->getAttribute( 'allow_reuse' ) === 1,
                    'menu_style'   => $xml->getAttribute( 'menu_style' ),
                    'embeddable'   => $xml->getAttribute( 'embeddable' ) === 1,
                ];
            }
        }

        if ( count( $widgets ) ) {
            $content = json_encode( $widgets, \JSON_PRETTY_PRINT );
            $this->_writeFile( 'widgets.json', $content, $folder );
        }

        $content = json_encode( $settings, \JSON_PRETTY_PRINT );
        $this->_writeFile( 'settings.json', $content, $folder );
        $content = json_encode( $versions, \JSON_PRETTY_PRINT );
        $this->_writeFile( 'versions.json', $content, $folder );

        $langFile = new DTFileGenerator;
        $langFile->setFilename( $folder . '/lang.php' );
        $langFile->setBody( '$lang=' . var_export( $lang, \true ) . ";" );
        $langFile->write();

        $langFile->setFilename( $folder . '/jslang.php' );
        $langFile->setBody( '$lang=' . var_export( $langJs, \true ) . ";" );
        $langFile->write();

        return [ 'msg' => $return, 'name' => $pluginName ];
    }
}
