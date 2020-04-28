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
use Generator\Builders\ClassGenerator;
use IPS\Patterns\Singleton;
use IPS\toolbox\Proxy\Proxyclass;

//use Zend\Code\Generator\ClassGenerator;
//use Zend\Code\Generator\FileGenerator;
//use Zend\Code\Generator\MethodGenerator;
use function header;
use function implode;

\IPS\toolbox\Application::loadAutoLoader();

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

    /**
     * @var Cache
     */
    protected $cache;

    public function __construct()
    {

        $this->cache = Cache::i();
        $this->save = \IPS\ROOT_PATH . '/' . Proxyclass::i()->save;
    }

    protected function writeClass( $class, $implements, $body, $ns = 'dtProxy', $funcName = 'get' )
    {

        try {
            $newClass = new ClassGenerator();
            $newClass->addNameSpace( $ns );
            $newClass->addClassName( $class );
            if ( $body ) {
                $newClass->addInterface( [ 'dtProxy', $implements ] );
                $newClass->addMethod( $funcName, 'return [\'' . implode( "','", $body ) . '\'];', [], [ 'static' => true ] );

            }
            else {
                $newClass->addExtends( [ $ns, $implements ] );
            }

            $newClass->addPath( $this->save );
            $newClass->addFileName( $implements );
            $newClass->save();
        } catch ( Exception $e ) {
        }
    }
}

