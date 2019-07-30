<?php

namespace IPS\toolbox\Generator\Builders;

use IPS\toolbox\Generator\Builders\Traits\ClassMethods;
use IPS\toolbox\Generator\Builders\Traits\Constants;
use IPS\toolbox\Generator\Builders\Traits\Properties;
use IPS\toolbox\Generator\Builders\Traits\Imports;
use function is_array;
use function count;

/**
 * Class _ClassGenerator
 *
 * @package IPS\toolbox\Generator
 * @mixin ClassGenerator
 */
class _ClassGenerator extends GeneratorAbstract
{

    use Properties, Constants, ClassMethods, Imports;

    /**
     * an array of implements
     *
     * @var array
     */
    protected $interfaces = [];

    /**
     * the parent class
     *
     * @var string
     */
    protected $extends;

    /**
     * class type, final/abstract
     *
     * @var string
     */
    protected $type;

    /**
     * an array of traits class uses
     *
     * @var array
     */
    protected $classUses = [];

    protected $doImports = true;

    public function getType()
    {

        return $this->type;
    }

    public function addType( $type )
    {

        $this->type = $type;
    }

    public function disableImports()
    {

        $this->doImports = false;
    }

    public function addUse( $class )
    {

        if ( is_array( $class ) ) {
            $og = $class;
            $class = implode( '\\', $class );
        }
        else {
            $og = explode( '\\', $class );
        }
        if ( $this->doImports === true && \count( $og ) >= 2 ) {
            $this->addImport( $class );
            $class = array_pop( $og );

        }
        $hash = $this->hash( $class );
        $this->classUses[ $hash ] = $class;
    }

    public function getClassUses()
    {

        return $this->classUses;
    }

    public function writeSourceType()
    {

        $type = null;

        if ( $this->type === T_ABSTRACT ) {
            $type = 'Abstract ';
        }
        else if ( $this->type === T_FINAL ) {
            $type = 'Final ';
        }

        $this->toWrite .= "\n{$type}class {$this->className}";

        if ( $this->extends ) {
            $this->toWrite .= " extends {$this->extends}";
        }

        if ( empty( $this->interfaces ) !== true ) {
            $this->toWrite .= " implements \n" . implode( ",\n", $this->interfaces );
        }
        $this->toWrite .= "\n{";
    }

    /**
     * @param $extends
     *
     * @return $this
     */
    public function addExtends( $extends, $import = true )
    {

        if ( is_array( $extends ) ) {
            $og = $extends;
            $extends = implode( '\\', $extends );
        }
        else {
            $og = explode( '\\', $extends );
        }
        if ( $import === true && $this->doImports === true && \count( $og ) >= 2 ) {
            $this->addImport( $extends );
            $extends = array_pop( $og );

        }

        $this->extends = $extends;
    }

    public function getExtends()
    {

        return $this->extends;
    }

    /**
     * @param $interface
     *
     * @return $this
     */
    public function addInterface( $interface )
    {

        if ( is_array( $interface ) ) {
            $og = $interface;
            $interface = implode( '\\', $interface );
        }
        else {
            $og = explode( '\\', $interface );
        }

        if ( $this->doImports === true && \count( $og ) >= 2 ) {
            $this->addImport( $interface );
            $interface = array_pop( $og );

        }
        $hash = $this->hash( $interface );
        $this->interfaces[ $hash ] = $interface;
    }

    protected function writeBody()
    {

        $tab = $this->tab;
        if ( is_array( $this->classUses ) && count( $this->classUses ) ) {
            $this->toWrite .= "\n\n{$tab}use ";
            $this->toWrite .= implode( ',', $this->classUses );
            $this->toWrite .= ";\n";
        }
        $this->writeConst();
        $this->writeProperties();
        $this->writeMethods();
    }

}
