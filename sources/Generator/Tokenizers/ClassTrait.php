<?php

namespace IPS\toolbox\sources\Generator\Tokenizers;

trait ClassTrait
{

    /**
     * an array of methods to replace in class
     *
     * @var array
     */
    protected $replaceMethods = [];

    /**
     * an array of methods to remove from class
     *
     * @var array
     */
    protected $removeMethods = [];

    protected $beforeLines = [];

    protected $afterLines = [];

    protected $replaceLines = [];

    protected $startOfMethods = [];

    protected $endofMethods = [];

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
        $name = $data[ 'name' ];
        $static = $data[ 'static' ];
        $final = $data[ 'final' ];
        $visibility = 'public';
        $params = $data[ 'params' ] ?? [];
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
            //            $params = [];
            //            $params[] = 'int $extra = null';
            foreach ( $params as $param ) {

                $sliced = <<<EOF
<?php

{$param}
EOF;
                $tokens = token_get_all( $sliced );
                $p = [];
                $hint = null;
                foreach ( $tokens as $token ) {
                    if ( isset( $tokens[ 0 ] ) && $tokens[ 0 ] !== T_OPEN_TAG ) {
                        $type = $token[ 0 ] ?? null;
                        $value = $token[ 1 ] ?? $token;
                        if ( $value ) {
                            if ( $type === T_STRING || $type === T_ARRAY ) {
                                if ( $type === T_ARRAY || ( !isset( $p[ 'hint' ] ) && !isset( $p[ 'value' ] ) && !isset( $p[ 'name' ] ) ) ) {
                                    $hint[] = $value;
                                }
                                else {
                                    if ( $hint !== null ) {
                                        $p[ 'hint' ] = implode( '\\', $hint );
                                        $hint = null;
                                    }
                                    $p[ 'value' ] = $value;
                                }

                            }
                            else if ( $type === T_VARIABLE ) {
                                if ( $hint !== null ) {
                                    $p[ 'hint' ] = implode( '\\', $hint );
                                    $hint = null;
                                }
                                $p[ 'name' ] = ltrim( trim( $value ), '$' );
                            }
                        }
                    }
                }
                $newParams[] = $p;

            }
            $params = $newParams;
        }

        if ( $returnType !== null ) {
            $returnType = implode( '\\', $returnType );
        }

        if ( $body !== null ) {
            $nb = '';

            foreach ( $body as $item ) {
                if ( isset( $item[ 'content' ] ) ) {
                    if ( isset( $this->beforeLines[ $item[ 'line' ] ] ) ) {
                        foreach ( $this->beforeLines[ $item[ 'line' ] ] as $content ) {
                            $nb .= $content;
                            $nb .= "\n" . $this->tab . $this->tab;
                        }
                        unset( $this->beforeLines[ $item[ 'line' ] ] );
                    }

                    if ( isset( $this->replaceLines[ $item[ 'line' ] ] ) ) {

                        foreach ( $this->replaceLines[ $item[ 'line' ] ] as $content ) {
                            $nb .= $content;
                            $nb .= "\n" . $this->tab . $this->tab;
                        }
                        unset( $this->replaceLines[ $item[ 'line' ] ] );
                    }
                    else {
                        $nb .= $item[ 'content' ];
                    }

                    if ( isset( $this->afterLines[ $item[ 'line' ] ] ) ) {
                        foreach ( $this->afterLines[ $item[ 'line' ] ] as $content ) {
                            $nb .= $content;
                            $nb .= "\n" . $this->tab . $this->tab;
                        }
                        unset( $this->afterLines[ $item[ 'line' ] ] );
                    }

                }
            }
            $nb = trim( $nb );
            if ( isset( $this->startOfMethods[ $name ] ) ) {
                $nb = mb_substr( $nb, 1 );
                $first = '';
                foreach ( $this->startOfMethods[ $name ] as $content ) {
                    $first .= "\n" . $this->tab . $this->tab;
                    $first .= $content;
                    $first .= "\n" . $this->tab . $this->tab;
                }
                $nb = "\n{\n" . $first . $nb;
            }

            if ( isset( $this->endofMethods[ $name ] ) ) {
                $nb = mb_substr( $nb, 0, -1 );
                $last = '';
                foreach ( $this->endofMethods[ $name ] as $content ) {
                    $last .= "\n" . $this->tab . $this->tab;
                    $last .= $content;
                    $last .= "\n" . $this->tab . $this->tab;
                }
                $nb .= $last . "\n{$this->tab}}\n";
            }

            $body = $nb;
        }
        $extra = [
            'static'     => (bool)$static,
            'visibility' => $visibility,
            'final'      => (bool)$final,
            'document'   => $document,
            'returnType' => $returnType,
        ];

        $this->addMethod( $name, $body, $params, $extra );
    }

    protected function normalizeMethods()
    {

        $this->rebuildMethods();
        $methods = $this->methods;
        $last = array_pop( $methods );
        $newlast = '';
        $extra = null;
        $body = $last[ 'body' ];
        $body = explode( "\n", $body );
        $func = 'function foo()';
        $end = false;
        foreach ( $body as $b ) {
            if ( $end === true ) {
                $extra .= $b . "\n";
            }
            else {
                $func .= $b;
                try {
                    eval( $func );
                    $end = true;
                    $newlast .= $b . "\n";

                } catch ( \ParseError $e ) {
                    $newlast .= $b . "\n";
                }
            }
        }

        if ( $extra !== null ) {
            $last[ 'body' ] = $newlast;
            $methods[ $last[ 'name' ] ] = $last;
            $this->extra = $extra;
        }
        $this->methods = $methods;

    }

    protected function rebuildMethods()
    {

        foreach ( $this->preppedMethod as $data ) {
            $this->buildMethod( $data );
        }
    }
}
