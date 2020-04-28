<?php

/**
 * @brief       ContentRouter Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.1
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Extensions;

use function count;
use function defined;
use function header;
use function implode;
use function is_array;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _ContentRouter
 *
 * @package IPS\toolbox\DevCenter\Extensions
 * @mixin \IPS\toolbox\DevCenter\Extensions\ExtensionsAbstract
 */
class _ContentRouter extends ExtensionsAbstract
{

    /**
     * @inheritdoc
     */
    public function elements()
    {

        $this->form->add( 'module' );
        $this->form->add( 'classRouter', 'stack' )->prefix( '\\IPS\\' . $this->application->directory . '\\' );

    }

    /**
     * @inheritdoc
     */
    protected function _content()
    {

        if ( is_array( $this->classRouter ) && count( $this->classRouter ) ) {
            $new = [];
            foreach ( $this->classRouter as $class ) {
                $new[] = '\\IPS\\' . $this->application->directory . '\\' . $class;
            }
            $this->classRouter = implode( "','", $new );
        }
        else {
            $this->classRouter = \null;
        }

        return $this->_getFile( $this->extension );
    }
}
