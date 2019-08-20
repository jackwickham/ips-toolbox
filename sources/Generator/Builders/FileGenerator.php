<?php

namespace Generator\Builders;

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
