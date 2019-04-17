<?php

/**
 * @brief       DTFileGenerator Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.3.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\Generator;

use Exception;
use IPS\Application;
use IPS\dtproxy\Proxyclass;
use Symfony\Component\Filesystem\Filesystem;
use Zend\Code\Generator\FileGenerator;
use function defined;
use function header;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

\IPS\toolbox\Application::loadAutoLoader();

class _DTFileGenerator extends FileGenerator
{
    public $isProxy = \false;

    /**
     * @return FileGenerator
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     */
    public function write(): FileGenerator
    {
        if ( $this->filename !== '' ) {
            $path = \pathinfo( $this->filename );
            try {
                $dir = $path[ 'dirname' ];
                $fs = new Filesystem();

                if ( !$fs->exists( $dir ) ) {
                    $fs->mkdir( $dir, \IPS\IPS_FOLDER_PERMISSION );
                    $fs->chmod( $dir, \IPS\IPS_FOLDER_PERMISSION );
                }
            } catch ( Exception $e ) {
            }
        }

        $parent = parent::write();

        if ( $this->isProxy === \false && Application::appIsEnabled( 'dtproxy' ) ) {
            Proxyclass::i()->buildAndMake( $this->filename );
        }

        return $parent;
    }
}
