<?php

namespace Generator\Tokenizers;

use Generator\Builders\TraitGenerator;

class TraitTokenizer extends TraitGenerator
{

    use Shared, ClassTrait;

    protected function writeBody()
    {

        $this->rebuildMethods();
        parent::writeBody();
    }
}
