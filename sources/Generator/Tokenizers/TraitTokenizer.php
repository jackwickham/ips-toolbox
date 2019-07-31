<?php

namespace IPS\toolbox\Generator\Tokenizers;

use IPS\toolbox\Generator\Builders\TraitGenerator;
use IPS\toolbox\sources\Generator\Tokenizers\ClassTrait;
use const IPS\ROOT_PATH;

class _TraitTokenizer extends TraitGenerator
{

    use Shared, ClassTrait;

    protected function writeBody()
    {

        $this->rebuildMethods();
        parent::writeBody();
    }
}
