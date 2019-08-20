<?php

namespace Generator\Tokenizers;

use Generator\Builders\ClassGenerator;
use IPS\babble\Profiler\Debug;

trait ClassTrait
{

    protected $preppedMethod = [];

    /**
     * @param string $name
     * @param string $body
     *
     * @return $this
     */
    public function replaceMethod( string $name, string $body )
    {

        $this->replaceMethods[ $name ] = $body;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function removemethod( $name )
    {

        $this->removeMethods[ trim( $name ) ] = 1;
    }

    public function beforeLine( int $line, string $content )
    {

        $this->beforeLines[ $line ][] = $content;
    }

    public function afterLine( int $line, string $content )
    {

        $this->afterLines[ $line ][] = $content;
    }

    public function replaceLine( int $line, string $content )
    {

        $this->replaceLines[ $line ][] = $content;
    }

    public function startOfMethod( $method, $content )
    {

        $this->startOfMethods[ $method ][] = $content;
    }

    public function endOfMethod( $method, $content )
    {

        $this->endofMethods[ $method ][] = $content;
    }

    public function afterMethod( $method, $content )
    {

        $this->afterMethod[ $method ][] = $content;
    }

    public function getMethods()
    {

        return $this->methods;
    }

    protected function prepImport( $import, $type, $use = false )
    {

        $alias = null;

        if ( $use !== true || $type !== 'const' ) {
            if ( in_array( 'as', $import, false ) ) {
                $as = false;
                foreach ( $import as $key => $item ) {
                    if ( $as === true && $item ) {
                        $alias[] = $item;
                        unset( $import[ $key ] );
                    }
                    if ( $item === 'as' ) {
                        unset( $import[ $key ] );
                        $as = true;
                    }
                }

                $alias = implode( '\\', $alias );
            }
        }

        $import = implode( '\\', $import );

        if ( $use === true ) {
            $this->addUse( $import );
        }
        else if ( $type === 'use' ) {
            $this->addImport( $import, $alias );
        }
        else if ( $type === 'function' ) {
            $this->addImportFunction( $import, $alias );
        }
        else if ( $type === 'const' ) {
            $this->addImportConstant( $import );
        }
    }

    protected function prepMethod( $data )
    {

        $this->preppedMethod[] = $data;

        $this->buildMethod( $data );
    }

    protected function buildMethod( $data )
    {

        $newParams = [];
        $abstract = $data[ 'abstract' ] ?? null;
        $name = $data[ 'name' ];
        $static = $data[ 'static' ];
        $final = $data[ 'final' ];
        $visibility = 'public';
        $params = $data[ 'params' ] ?? null;
        $document = $data[ 'document' ] ?? null;
        $returnType = $data[ 'returnType' ] ?? null;
        $body = $data[ 'body' ] ?? '';
        if ( $data[ 'visibility' ] === T_PRIVATE ) {
            $visibility = 'private';
        }
        else if ( $data[ 'visibility' ] === T_PROTECTED ) {
            $visibility = 'protected';
        }

        if ( empty( $params ) !== true ) {
            //            if ( trim( $name ) === 'featured' ) {
            //                _p( $params );
            //            }
            $params = ClassGenerator::paramsFromString( $params );
            if ( trim( $name ) === 'monkeyPatch' ) {
                //_d( $params );
            }
        }

        $params = $params ?? [];

        if ( $body !== null ) {
            $nb = '';

            foreach ( $body as $item ) {
                if ( isset( $item[ 'content' ] ) ) {
                    if ( isset( $this->beforeLines[ $item[ 'line' ] ] ) ) {
                        foreach ( $this->beforeLines[ $item[ 'line' ] ] as $content ) {
                            $nb .= $this->tab2space( $content );
                            $nb .= "\n" . $this->tab . $this->tab;
                        }
                        unset( $this->beforeLines[ $item[ 'line' ] ] );
                    }

                    if ( isset( $this->replaceLines[ $item[ 'line' ] ] ) ) {

                        foreach ( $this->replaceLines[ $item[ 'line' ] ] as $content ) {
                            $nb .= $this->tab2space( $content );
                            $nb .= "\n" . $this->tab . $this->tab;
                        }
                        unset( $this->replaceLines[ $item[ 'line' ] ] );
                    }
                    else {
                        $nb .= $this->tab2space( $item[ 'content' ] );
                    }

                    if ( isset( $this->afterLines[ $item[ 'line' ] ] ) ) {
                        foreach ( $this->afterLines[ $item[ 'line' ] ] as $content ) {
                            $nb .= $this->tab2space( $content );
                            $nb .= "\n" . $this->tab . $this->tab;
                        }
                        unset( $this->afterLines[ $item[ 'line' ] ] );
                    }

                }
            }

            $nb = trim( $nb );
            if ( mb_substr( $nb, 0, 1 ) === '{' ) {
                $nb = mb_substr( $nb, 1 );
            }
            //
            //            if ( mb_substr( $nb, -1 ) === '}' ) {
            //                $nb = mb_substr( $nb, 0, -1 );
            //            }

            if ( isset( $this->startOfMethods[ $name ] ) ) {
                $first = '';
                foreach ( $this->startOfMethods[ $name ] as $content ) {
                    $first .= "\n" . $this->tab . $this->tab;
                    $first .= $content;
                    $first .= "\n" . $this->tab . $this->tab;
                }
                $nb = $first . $nb;
            }

            if ( isset( $this->endofMethods[ $name ] ) ) {

                foreach ( $this->endofMethods[ $name ] as $content ) {
                    $nb .= "\n" . $this->tab . $this->tab;
                    $nb .= $content;
                    $nb .= "\n" . $this->tab . $this->tab;
                }
            }

            $body = $nb;
        }
        $extra = [
            'static'     => (bool)$static,
            'abstract'   => $abstract,
            'visibility' => $visibility,
            'final'      => (bool)$final,
            'document'   => $document,
            'returnType' => $returnType,
        ];

        $this->addMethod( $name, $body, $params, $extra );
    }

    protected function rebuildMethods()
    {

        foreach ( $this->preppedMethod as $data ) {
            $this->buildMethod( $data );
        }
    }

    protected function addToExtra( $data )
    {

        $this->extra[] = $data;
    }

}
