<?php

define('REPORT_EXCEPTIONS', TRUE);
require_once '../../../../init.php';

if ( \IPS\IN_DEV !== true AND ! \IPS\Theme::designersModeEnabled() )
{
    exit();
}

/* The CSS is parsed by the theme engine, and the theme engine has plugins, and those plugins need to now which theme ID we're using */
if ( \IPS\Theme::designersModeEnabled() )
{
    \IPS\Session\Front::i();
}

$needsParsing = true;

if( strstr( \IPS\Request::i()->css, ',' ) )
{
    $contents = '';
    foreach( explode( ',', \IPS\Request::i()->css ) as $css )
    {
        if ( mb_substr( $css, -4 ) !== '.css' )
        {
            continue;
        }

        $css	= str_replace( array( '../', '..\\' ), array( '&#46;&#46;/', '&#46;&#46;\\' ), $css );
        $file	= file_get_contents( \IPS\ROOT_PATH . '/' . $css );
        $params	= processFile( $file );

        if ( $params['hidden'] === 1 )
        {
            continue;
        }

        $contents .= "\n" . $file;

    }
}
else
{
    if ( mb_substr( \IPS\Request::i()->css, -4 ) !== '.css' )
    {
        exit();
    }

    $contents  = file_get_contents( \IPS\ROOT_PATH . '/' . str_replace( array( '../', '..\\' ), array( '&#46;&#46;/', '&#46;&#46;\\' ), \IPS\Request::i()->css ) );

    $params = processFile( $contents );

    if ( $params['hidden']  === 1 )
    {
        exit;
    }

}

    $id = DT_THEME_ID;

if( isset( \IPS\Request::i()->admin) && \IPS\Request::i()->admin === 1){
    $id = DT_THEME_ID_ADMIN;
}

    \IPS\Theme::$memberTheme = \IPS\Theme\Advanced\Theme::load( $id );

    $functionName = 'css_' . mt_rand();
    $contents = str_replace( '\\', '\\\\', $contents );
    /* If we have something like `{expression="\IPS\SOME_CONSTANT"}` we cannot double escape it, however we do need to escape font icons and similar. */
    $contents = preg_replace_callback( "/{expression=\"(.+?)\"}/ms", function( $matches ) {
        return '{expression="' . str_replace( '\\\\', '\\', $matches[1] ) . '"}';
    }, $contents );
    \IPS\Theme::makeProcessFunction( $contents, $functionName );
    $functionName = "IPS\Theme\\{$functionName}";
    \IPS\Output::i()->sendOutput( $functionName(), 200, 'text/css' );



/**
 * Process the file to extract the header tag params
 *
 * @return array
 */
function processFile( $contents )
{
    $return = array( 'module' => '', 'app' => '', 'pos' => '', 'hidden' => 0 );

    /* Parse the header tag */
    preg_match_all( '#^/\*<ips:css([^>]+?)>\*/\n#', $contents, $params, PREG_SET_ORDER );
    foreach( $params as $id => $param )
    {
        preg_match_all( '#([\d\w]+?)=\"([^"]+?)"#i', $param[1], $items, PREG_SET_ORDER );

        foreach( $items as $id => $attr )
        {
            switch( trim( $attr[1] ) )
            {
                case 'module':
                    $return['module'] = trim( $attr[2] );
                    break;
                case 'app':
                    $return['app'] = trim( $attr[2] );
                    break;
                case 'position':
                    $return['pos'] = \intval( $attr[2] );
                    break;
                case 'hidden':
                    $return['hidden'] = \intval( $attr[2] );
                    break;
            }
        }
    }

    return $return;
}
