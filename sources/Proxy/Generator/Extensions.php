<?php
/**
 * @brief      Extensions Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Proxy\Generator;

use Exception;
use IPS\Application;
use IPS\Data\Store;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Zend\Code\Generator\FileGenerator;
use function date;
use function defined;
use function header;
use function is_dir;
use function str_replace;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Extensions Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\Extensions
 */
class _Extensions extends GeneratorAbstract
{

    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;

    /**
     * creates the jsonMeta for the json file and writes the provider to disk
     */
    public function create()
    {
        $name = [];
        $lookup = [];
        $oldSave = $this->save;
        foreach ( Application::roots() AS $key ) {
            $path = \IPS\ROOT_PATH . '/applications/' . $key->directory . '/data/defaults/extensions/';
            if ( is_dir( $path ) ) {
                try {

                    $files = ( new Finder() )->in( $path )->files()->name( '*.txt' );

                    /**
                     * @var SplFileInfo $file
                     */
                    foreach ( $files as $file ) {
                        $baseName = $file->getBasename( '.txt' );
                        $name[] = $baseName;
                        $find = [
                            '{subpackage}',
                            '{date}',
                            '{app}',
                            '{class}',
                            '<?php',
                        ];
                        $replace = [
                            $key->directory,
                            date( 'd M Y' ),
                            $key->directory,
                            $file->getBasename( '.txt' ),
                            '',
                        ];

                        $ns = 'IPS\\' . $key->directory . '\\extensions\\' . $key->directory . '\\' . $baseName;
                        $content = str_replace( $find, $replace, $file->getContents() );

                        $file = new FileGenerator;
                        $file->setBody( $content );
                        $this->save = $oldSave;
                        $file->setFilename( $this->save . '/extensions/' . $baseName . '.php' )->write();

                        $this->save .= '/extensions';
                        $this->writeClass( $baseName, '_' . $baseName, \null, $ns );
                        $lookup[ $baseName ] = [
                            'lookup_string' => $baseName,
                            'type'          => $ns . '\\' . $baseName,
                        ];

                    }
                } catch ( Exception $e ) {
                }
            }
        }

        $this->save = $oldSave;
        $jsonMeta = [];
        if ( isset( Store::i()->dt_json ) ) {
            $jsonMeta = Store::i()->dt_json;
        }

        $jsonMeta[ 'registrar' ][] = [
            'signature'  => [
                'IPS\\Application::extensions:1',
                'IPS\\Application::allExtensions:1',
            ],
            'signatures' => [
                [
                    'class'  => Application::class,
                    'method' => 'extensions',
                    'index'  => 1,
                    'type'   => 'type',
                ],

            ],
            'provider'   => 'extensionLookup',
            'language'   => 'php',
        ];

        $jsonMeta[ 'providers' ][] = [
            'name'   => 'ExtensionsNameProvider',
            'source' => [
                'contributor' => 'return_array',
                'parameter'   => 'dtProxy\\ExtensionsNameProvider::get',
            ],
        ];

        Store::i()->dt_json = $jsonMeta;

        $this->writeClass( 'Extensions', 'ExtensionsNameProvider', $name );
    }
}

