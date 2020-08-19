<?php

/**
 * @brief       Dumper Singleton
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler;

use IPS\Theme;
use IPS\toolbox\Application;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use function defined;
use function header;
use function is_bool;
use function is_numeric;


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

Application::loadAutoLoader();

class _Dumper extends HtmlDumper
{
    public function add( $value )
    {
        $cloner = new VarCloner;
        return $this->dump( $cloner->cloneVar( $value ), \true );
    }

    public function type( $value )
    {

        if ( is_numeric( $value ) ) {
            $key = 'Number';
        }
        else if ( is_bool( $value ) ) {
            if ( (bool)$value === \true ) {
                $value = 'true';
            }
            else {
                $value = 'false';
            }
            $key = 'Bool';

        }
        else {
            $key = 'String';
        }

        return Theme::i()->getTemplate( 'generic', 'toolbox', 'front' )->genericVal( $value, $key );
    }
}
