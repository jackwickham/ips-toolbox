<?php

namespace Generator\Tokenizers;

use Generator\Builders\ClassGenerator;

class StandardTokenizer extends ClassGenerator
{

    use Shared, ClassTrait;

    protected function writeBody()
    {

        $this->rebuildMethods();
        parent::writeBody();
    }

}
