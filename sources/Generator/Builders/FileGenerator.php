<?php

/**
 * @brief       FileGenerator Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace Generator\Builders;

use function implode;

/**
 * Class _ClassGenerator
 *
 * @package IPS\toolbox\Generator
 * @mixin ClassGenerator
 */
class FileGenerator extends GeneratorAbstract
{

    protected const HASCLASS = false;

    protected $body = [];

    public function addBody( $body )
    {

        $this->body[] = $body;
    }

    protected function writeSourceType()
    {

    }

    protected function writeBody()
    {

        $body = implode( "\n", $this->body );
        $this->output( $body );
    }

}
