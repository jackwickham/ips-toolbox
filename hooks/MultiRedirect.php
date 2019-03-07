//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Http\Url;
use IPS\Output;
use IPS\Request;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_MultiRedirect extends _HOOK_CLASS_
{

    public function __construct( $url, $callback, $finished, $finalRedirect = \true )
    {
        if ( isset( Request::i()->storm ) && Request::i()->storm ) {
            $url = $url->setQueryString( [ 'storm' => Request::i()->storm ] );
            $finished = function ()
            {
                $path = 'app=toolbox&module=devfolder&controller=plugins';
                $url = Url::internal( $path )->setQueryString( [
                    'storm' => Request::i()->storm,
                    'do'    => "doDev",
                ] );
                Output::i()->redirect( $url );
            };
        }

        parent::__construct( $url, $callback, $finished, $finalRedirect );

    }

}
