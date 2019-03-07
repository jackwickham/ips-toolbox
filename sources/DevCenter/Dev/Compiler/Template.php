<?php

/**
 * @brief       Template Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Dev\Compiler;

use function count;
use function implode;

/**
 * Class _Template
 *
 * @package IPS\toolbox\DevCenter\Dev\Compiler
 * @mixin \IPS\toolbox\DevCenter\Dev\Compiler\\CompilerAbstract
 */
class _Template extends CompilerAbstract
{
    /**
     * @inheritdoc
     */
    public function content(): string
    {
        $this->filename .= '.phtml';
        $params = [];
        $arguments = $this->arguments ?: [];

        /* @var array $arguments */
        if ( !empty( $arguments ) ) {
            foreach ( $arguments as $argument ) {
                $params[] = "\${$argument}";
            }
        }

        $params = count( $params ) ? implode( ',', $params ) : \null;

        return $this->_replace( '{params}', $params, $this->_getFile( 'template' ) );
    }
}
