<?php

namespace IPS\toolbox\Generator\Tokenizers;

use IPS\toolbox\Generator\Builders\ClassGenerator;
use IPS\toolbox\sources\Generator\Tokenizers\ClassTrait;

class _StandardTokenizer extends ClassGenerator
{

    use Shared, ClassTrait;

    protected function writeBody()
    {

        $this->normalizeMethods();
        parent::writeBody();
    }

}
