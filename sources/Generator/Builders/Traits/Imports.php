<?php

namespace Generator\Builders\Traits;

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

        if ( $this->checkForImportConstant( $import ) ) {
            throw new \InvalidArgumentException( 'This constant exist as a import!' );
        }
        $this->importsConst[ $import ] = [ 'class' => $import ];
    }

    public function checkForImportConstant( $import )
    {

        return isset( $this->importsConst[ $import ] );
    }

    public function addImportFunction( string $import, $alias = null )
    {

        if ( $alias !== null ) {
            $hash = $alias;
        }
        else {
            $parts = explode( '\\', $import );
            $class = array_pop( $parts );
            $hash = $class;
        }
        if ( $this->checkForImportFunction( $class ) || $this->checkForImportFunction( $alias ) ) {
            throw new \InvalidArgumentException( 'This function exist as a import!' );
        }
        $this->importsFunctions[ $hash ] = [ 'class' => $import, 'alias' => $alias ];
    }

    public function checkForImportFunction( $import )
    {

        return isset( $this->importsFunctions[ $import ] );
    }

    public function addImport( string $import, string $alias = null )
    {

        $parts = explode( '\\', $import );
        $class = array_pop( $parts );
        $hash = $class;

        if ( $alias !== null ) {
            $hash = $alias;
        }

        $continue = true;

        if ( $this->getNameSpace() . '\\' . $class === $import ) {
            $continue = false;
        }

        if ( $continue === true && $this->checkForImport( $class ) === false && $this->checkForImport( $alias ) === false ) {
            $this->imports[ $hash ] = [ 'class' => $import, 'alias' => $alias ];
        }

    }

    public function checkForImport( $import )
    {

        return isset( $this->imports[ $import ] );
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

        $this->output( "\nuse " );
        if ( $type !== null ) {
            $this->output( $type . ' ' );
        }
        $this->output( $import[ 'class' ] );
        if ( isset( $import[ 'alias' ] ) && $import[ 'alias' ] ) {
            $this->output( " as {$import['alias']}" );
        }
        $this->output( ';' );
    }

}
