<?php

namespace IPS\toolbox\Generator\Builders\Traits;

trait Imports
{

    /**
     * list of class import FQN's
     *
     * @var array
     */
    protected $imports = [];

    /**
     * list of function import FQN's
     *
     * @var array
     */
    protected $importsFunctions = [];

    protected $importsConst = [];

    public function addImportConstant( string $import )
    {

        $hash = $this->hash( $import );
        if ( $this->checkForImportConstant( $import ) ) {
            throw new \InvalidArgumentException( 'This constant exist as a import!' );
        }
        $this->importsConst[ $hash ] = [ 'class' => $import ];
    }

    public function checkForImportConstant( string $name )
    {

        $hash = $this->hash( $name );

        return isset( $this->importsConst[ $hash ] );
    }

    public function addImportFunction( string $import, $alias = null )
    {

        if ( $alias !== null ) {
            $hash = $this->hash( $alias );
        }
        else {
            $parts = explode( '\\', $import );
            $class = array_pop( $parts );
            $hash = $this->hash( $class );
        }
        if ( $this->checkForImportFunction( $class ) || $this->checkForImportFunction( $alias ) ) {
            throw new \InvalidArgumentException( 'This function exist as a import!' );
        }
        $this->importsFunctions[ $hash ] = [ 'class' => $import, 'alias' => $alias ];
    }

    public function checkForImportFunction( string $name )
    {

        $hash = $this->hash( $name );

        return isset( $this->importsFunctions[ $hash ] );
    }

    public function addImport( string $import, string $alias = null )
    {

        $parts = explode( '\\', $import );
        $class = array_pop( $parts );
        $hash = $this->hash( $class );

        if ( $alias !== null ) {
            $hash = $this->hash( $alias );
        }

        $continue = true;

        if ( $this->getNameSpace() . '\\' . $class === $import ) {
            $continue = false;
        }

        if ( $continue === true && $this->checkForImport( $class ) === false && $this->checkForImport( $alias ) === false ) {
            $this->imports[ $hash ] = [ 'class' => $import, 'alias' => $alias ];
        }

    }

    public function checkForImport( $name )
    {

        $hash = $this->hash( $name );

        return isset( $this->imports[ $hash ] );
    }

    public function getImportFunctions()
    {

        return $this->importsFunctions;
    }

    public function getImportConstants()
    {

        return $this->importsConst;
    }

    public function getImports()
    {

        return $this->imports;
    }

    protected function afterNameSpace()
    {

        if ( empty( $this->imports ) !== true ) {
            foreach ( $this->imports as $import ) {
                $this->buildImport( $import );
            }
        }

        if ( empty( $this->importsFunctions ) !== true ) {
            foreach ( $this->importsFunctions as $import ) {
                $this->buildImport( $import, 'function' );

            }
        }

        if ( empty( $this->importsConst ) !== true ) {
            foreach ( $this->importsConst as $import ) {
                $this->buildImport( $import, 'const' );
            }
        }
    }

    /**
     * @param      $import
     * @param null $type
     */
    protected function buildImport( $import, $type = null )
    {

        $this->toWrite .= "\nuse ";
        if ( $type !== null ) {
            $this->toWrite .= $type . ' ';
        }
        $this->toWrite .= $import[ 'class' ];
        if ( isset( $import[ 'alias' ] ) && $import[ 'alias' ] ) {
            $this->toWrite .= " as {$import['alias']}";
        }
        $this->toWrite .= ';';
    }

}
