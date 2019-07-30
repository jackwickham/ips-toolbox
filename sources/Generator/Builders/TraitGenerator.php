<?php

namespace IPS\toolbox\Generator\Builders;

use IPS\toolbox\Generator\Builders\Traits\ClassMethods;
use IPS\toolbox\Generator\Builders\Traits\Constants;
use IPS\toolbox\Generator\Builders\Traits\Imports;
use IPS\toolbox\Generator\Builders\Traits\Properties;

/**
 * Class _TraitGenerator
 *
 * @package IPS\toolbox\Generator\Builders
 * @mixin TraitGenerator
 */
class _TraitGenerator extends GeneratorAbstract
{

    use Properties, Constants, ClassMethods, Imports;

    /**
     * class type, final/abstract
     *
     * @var string
     */
    protected $type;

    public function writeSourceType()
    {

        $this->toWrite .= "\ntrait {$this->className}";
        $this->toWrite .= "\n{";
    }

    protected function writeBody()
    {

        $this->writeConst();
        $this->writeProperties();
        $this->writeMethods();
    }

}
