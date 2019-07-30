<?php
/**
 * @brief      HelperTemplate Class
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage dtdevplus
 * @since      -storm_since_version-
 * @version    -storm_version-
 */

namespace IPS\toolbox\DevCenter\Helpers;

use IPS\toolbox\DevCenter\Dev;
use IPS\toolbox\Generator\Builders\ClassGenerator;
use IPS\toolbox\Proxy\Helpers\HelpersAbstract;
use function header;
use function mb_strtolower;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * HelperTemplate Class
 *
 * @mixin  IPS\toolbox\Proxy\Helpers\HelpersAbstract;
 */
class _HelperCompilerAbstract implements HelpersAbstract
{

    public function process( $class, ClassGenerator $classGenerator, &$classExtends )
    {

        $classGenerator->addPropertyTag( 'location', [ 'hint' => 'string' ] );
        $classGenerator->addPropertyTag( 'group', [ 'hint' => 'string' ] );

    }
}

