<?php
/**
 * @brief      GeneratorAbstract Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox\Proxy
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Proxy\Generator;

use Exception;
use IPS\Patterns\Singleton;
use IPS\toolbox\Proxy\Proxyclass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use function header;
use function implode;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * GeneratorAbstract Class
 *
 * @mixin \IPS\toolbox\Proxy\Generator\GeneratorAbstract
 */
class _GeneratorAbstract extends Singleton
{
    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;

    protected $save;

    public function __construct()
    {
        \IPS\toolbox\Application::loadAutoLoader();
        $this->save = \IPS\ROOT_PATH . '/' . Proxyclass::i()->save;
    }

    protected function writeClass( $class, $implements, $body, $ns = 'dtProxy', $funcName = 'get' )
    {
        try {
            $newClass = new ClassGenerator;
            $newClass->setNamespaceName( $ns );
            $newClass->setName( $class );
            if ( $body ) {
                $newClass->setImplementedInterfaces( [ 'dtProxy\\' . $implements ] );
                $method = [
                    MethodGenerator::fromArray( [
                        'name'   => $funcName,
                        'body'   => 'return [\'' . implode( "','", $body ) . '\'];',
                        'static' => \true,
                    ] ),
                ];

                $newClass->addMethods( $method );
            }
            else {
                $newClass->setExtendedClass( $ns . '\\' . $implements );
            }
            $content = new FileGenerator;
            $content->setClass( $newClass );
            $content->setFilename( $this->save . '/' . $implements . '.php' );
            $content->write();
        } catch ( Exception $e ) {
        }
    }
}

