<?php


namespace IPS\toolbox\Shared;

use IPS\toolbox\Proxy\Generator\Writer;

interface Providers
{
    public function meta( array &$jsonMeta );

    public function writeProvider( Writer $generator );
}
