<?php

namespace Generator\Builders;

use Generator\Builders\Traits\ClassMethods;
use Generator\Builders\Traits\Constants;
use Generator\Builders\Traits\Imports;
use Generator\Builders\Traits\Properties;

/**
 * Class TraitGenerator
 *
 * @package IPS\toolbox\Generator\Builders
 * @mixin TraitGenerator
 */
class TraitGenerator extends GeneratorAbstract
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

        $this->output( "\ntrait {$this->className}" );
        $this->output( "\n{" );

    }

    protected function writeBody()
    {

        $this->writeConst();
        $this->writeProperties();
        $this->writeMethods();
        $this->output( "\n}" );

    }

}
