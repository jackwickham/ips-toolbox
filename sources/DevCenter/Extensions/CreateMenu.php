<?php

/**
 * @brief       FileStorage Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtdevplus
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Extensions;

use function defined;
use function header;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _CreateMenu
 *
 * @package IPS\toolbox\DevCenter\Extensions
 * @mixin \IPS\toolbox\DevCenter\Extensions\ExtensionsAbstract
 */
class _CreateMenu extends ExtensionsAbstract
{

    /**
     * @inheritdoc
     */
    public function elements()
    {

        $this->form->add( 'key' )->required();
        $this->form->add( 'link' )->required()->prefix( 'app=' . $this->application->directory . '&' );
        $this->form->add( 'seo' );
        $this->form->add( 'seoTitle' );
    }

    /**
     * @inheritdoc
     */
    protected function _content()
    {

        $this->link = 'app=' . $this->application->directory . '&' . $this->link;
        $this->seo = $this->seo ? "'" . $this->seo . "'" : \null;
        $this->seoTitle = $this->seoTitle ? "'" . $this->seoTitle . "'" : \null;

        return $this->_getFile( $this->extension );
    }
}
