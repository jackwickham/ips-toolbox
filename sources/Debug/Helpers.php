<?php
$upOne = dirname( __DIR__ );
require_once $upOne . '/Editor/Editor.php';

use IPS\toolbox\Editor;

class Helpers
{
    protected $args = [];
    protected $title;
    protected $body;
    protected $style;
    protected $editor;

    public function __construct( $args )
    {
        $this->args = $args;
        $this->editor = new Editor;

        $this->style();
    }

    protected function style()
    {
        $style = <<<'EOF'
<style>
body {
    color:#fff;
    background:#000;
    font-size:18px;
}
a {
    color:#18C0DF;
}
.helpersTitle{
    padding:0px 15px;
    font-weight:bold;
    margin-top:15px;
    border:1px #fff solid;
}

.helpersBackTraceRowContainer,
.helpersFileLine,
.helpersRow {
    padding: 10px 15px;
    border:1px #fff solid;
}

.helpersBackTraceRow div {
    display:inline-block;
}

.helpersPrintRowInt{
    color:#c93054;
}

.helpersPrintRowArray {
    color: #1DC116;
}

.helpersPrintRowBool {
    color:#6c71c4;
}


.helpersPrintRowString{
    color:#d67814;
}

.helpersPrintRowDump {
    color:#c9e2b3;
}

.helpersPrintRowExport {
    color:#30aabc;
}
pre {
    margin:0;
}
</style>
EOF;
        $this->style = $style;
    }

    public function __destruct()
    {
        $document = <<<'EOF'
<!DOCTYPE html>
<html lang="en-US" dir="ltr">
	<head>
        <title>#title#</title>
        #style#
    </head>
    <body>
    #body#
    </body>
</html>
EOF;

        $title = $this->title;
        $style = $this->style;
        $body = $this->body;
        echo str_replace( [ '#title#', '#style#', '#body#' ], [ $title, $style, $body ], $document );
        exit;
    }

    public function _method( $func = 'print' )
    {
        $this->title = mb_ucfirst( $func ) . '()';
        $html = [];
        $body = [];
        $body[] = $this->fileLine();
        $container = <<<'EOF'
<div class="helpersTitle">#func# ...$arguments ##count#</div>       
<div class="helpersPrintBody">
#body#
</div>
EOF;
        $row = <<<'EOF'
<div class="helpersRow helpersPrintRow#type#">
#count#
#row#
</div>
EOF;
        $i = 1;
        $count = count( $this->args );
        foreach ( $this->args as $arg ) {
            $c = '';
            if ( $count > 1 ) {
                $c = '#' . $i . '<br>';
                $i++;
            }

            if ( $func === 'print' ) {
                if ( is_array( $arg ) || is_object( $arg ) ) {
                    $val = '<pre>' . print_r( $arg, \true );
                    $type = 'Array';
                }
                else if ( is_numeric( $arg ) ) {
                    $val = $arg;
                    $type = 'Int';
                }
                else if ( is_bool( $arg ) ) {
                    $val = (bool)$arg;
                    if ( $val === \false ) {
                        $val = 'false';
                    }
                    else {
                        $val = 'true';
                    }

                    $type = 'Bool';
                }
                else {
                    $val = '"' . $arg . '"';
                    $type = 'String';
                }
            }
            else if ( $func === 'var_dump' ) {
                ob_start();
                var_dump( $arg, \true );
                $val = ob_get_contents();
                ob_end_clean();
                $type = 'Dump';
            }
            else if ( $func === 'var_export' ) {
                $type = 'Export';
                $val = var_export( $arg, \true );
            }

            $html[] = str_replace( [ '#row#', '#type#', '#count#' ], [ $val, $type, $c ], $row );
        }

        $body[] = str_replace( [ '#body#', '#count#', '#func#' ], [
            implode( "\n", $html ),
            $count,
            $this->title,
        ], $container );
        $body[] = $this->backTrace();
        $this->body = implode( "\n", $body );
    }

    protected function fileLine()
    {
        $backtrace = debug_backtrace( 0 );
        $not = __FILE__;
        $file = '';
        $line = '';

        foreach ( $backtrace as $bt ) {
            if ( isset( $bt[ 'file' ] ) && $not !== $bt[ 'file' ] ) {
                $file = $bt[ 'file' ];
                $line = $bt[ 'line' ];
                break;
            }
        }

        $echo = <<<'EOF'
<div class="helpersTitle">File::Line</div>
<div class="helpersFileLine">
    <div>File: #file#</div>
    <div>Line: #line#</div>
</div>
EOF;

        $html = str_replace( [ '#file#', '#line#' ], [ $file, $line ], $echo );
        return $html;
    }

    protected function backTrace()
    {
        $not = __FILE__;
        $backtraces = debug_backtrace( \DEBUG_BACKTRACE_IGNORE_ARGS );
        $container = <<<'EOF'
<div class="helpersTitle">Backtrace</div>
<div class="helpersBackTrace">
    #body#
</div>
EOF;
        $row = <<<'EOF'
<div class="helpersBackTraceRow">
    <div>#type#</div>
    <div>#val#</div>
</div>
EOF;

        $html = [];

        foreach ( $backtraces as $backtrace ) {
            if ( is_array( $backtrace ) ) {
                if ( isset( $backtrace[ 'file' ] ) && $backtrace[ 'file' ] === $not ) {
                    continue;
                }
                $html[] = '<div class="helpersBackTraceRowContainer">';
                $quickStore = [];
                foreach ( $backtrace as $key => $val ) {
                    $key = mb_ucfirst( $key );
                    if ( $key === 'File' ) {
                        $quickStore = [
                            'key' => $key,
                            'val' => $val,
                        ];
                    }
                    else if ( $key === 'Line' ) {
                        $file = $quickStore;
                        $quickStore = [];
                        $a = '<a href="' . $this->editor->replace( $file[ 'val' ], $val ) . '">' . $file[ 'val' ] . '</a>';
                        $html[] = str_replace( [ '#type#', '#val#' ], [ $file[ 'key' ], $a ], $row );
                        $html[] = str_replace( [ '#type#', '#val#' ], [ $key, $val ], $row );
                    }
                    else {
                        $html[] = str_replace( [ '#type#', '#val#' ], [ $key, $val ], $row );
                    }
                }
                $html[] = '</div>';
            }
        }

        $container = str_replace( '#body#', implode( "\n", $html ), $container );
        return $container;
    }
}

function _p()
{
    ( new Helpers( func_get_args() ) )->_method();
}

function _print()
{
    ( new Helpers( func_get_args() ) )->_method();
}

function _d()
{
    ( new Helpers( func_get_args() ) )->_method( 'var_dump' );
}

function _dump()
{
    ( new Helpers( func_get_args() ) )->_method( 'var_dump' );
}

function _e()
{
    ( new Helpers( func_get_args() ) )->_method( 'var_export' );
}

function _export()
{
    ( new Helpers( func_get_args() ) )->_method( 'var_export' );
}
