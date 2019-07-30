<?php

namespace IPS\toolbox\Generator\Tokenizers;

use const IPS\ROOT_PATH;

class _InterfaceTokenizer extends \IPS\toolbox\Generator\Builders\InterfaceGenerator
{

    use Shared;

    protected function writeBody()
    {

        $this->normalizeMethods();
        parent::writeMethods();
    }

    protected function normalizeMethods()
    {
    }
}
