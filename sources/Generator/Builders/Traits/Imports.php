<?php

/**
 * @brief       Imports Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace Generator\Builders\Traits;

use function array_pop;
use function class_exists;
use function explode;
use function mb_strtolower;
use function mb_substr;
use function str_replace;

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

        $skipOn = [
            'array'    => 1,
            'self'     => 1,
            'callable' => 1,
            'bool'     => 1,
            'float'    => 1,
            'int'      => 1,
            'string'   => 1,
            'iterable' => 1,
            'object'   => 1,
        ];

        if ( isset( $skipOn[ mb_strtolower( $import ) ] ) ) {
            return $import;
        }
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
        $return = $this->canMakeImport( $import );
        if ( $continue === true && $return !== $import && $this->checkForImport( $class ) === false && $this->checkForImport( $alias ) === false ) {
            $this->imports[ $hash ] = [ 'class' => $import, 'alias' => $alias ];
        }

        if ( $return === $import && \count( explode( '\\', $import ) ) >= 2 ) {
            $check = mb_substr( $return, 0, 1 );
            if ( $check !== '\\' ) {
                $return = '\\' . $return;
            }
        }

        return $return;

    }

    public function canMakeImport( $class )
    {

        $nsClass = explode( '\\', $class );

        $newClass = array_pop( $nsClass );
        $testClass = '\\' . $this->getNameSpace() . '\\' . $newClass;

        try {
            if ( class_exists( $testClass ) ) {
                return $class;
            }
        } catch ( \Exception $e ) {
        }
        
        foreach ( $this->imports as $import ) {
            $nsImport = explode( '\\', $import[ 'class' ] );
            $importClass = array_pop( $nsImport );
            if ( $import[ 'class' ] !== $class && $newClass === $importClass ) {
                if ( $import[ 'alias' ] === null ) {
                    $newClass = $importClass;
                }
                else {
                    $newClass = $import[ 'alias' ];
                }
            }
        }

        return $newClass;

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

    public function wrapUp()
    {

        parent::wrapUp();
        $replacement = '';
        if ( empty( $this->imports ) !== true ) {
            foreach ( $this->imports as $import ) {
                $replacement .= $this->buildImport( $import );
            }
        }

        if ( empty( $this->importsFunctions ) !== true ) {
            foreach ( $this->importsFunctions as $import ) {
                $replacement .= $this->buildImport( $import, 'function' );

            }
        }

        if ( empty( $this->importsConst ) !== true ) {
            foreach ( $this->importsConst as $import ) {
                $replacement .= $this->buildImport( $import, 'const' );
            }
        }

        $this->toWrite = str_replace( '#generator_token_imports#', $replacement, $this->toWrite );
    }

    /**
     * @param      $import
     * @param null $type
     */
    protected function buildImport( $import, $type = null )
    {

        $output = '';
        $output .= "\nuse ";

        if ( $type !== null ) {
            $output .= $type . ' ';
        }
        $output .= $import[ 'class' ];

        if ( isset( $import[ 'alias' ] ) && $import[ 'alias' ] ) {
            $output .= ' as ' . $import[ 'alias' ];
        }

        $output .= ';';

        return $output;
    }

}
