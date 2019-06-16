//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    exit;
}

class toolbox_hook_Output extends _HOOK_CLASS_
{

    public $dtContentType = \null;

    public function sendOutput( $output = '', $httpStatusCode = 200, $contentType = 'text/html', $httpHeaders = [], $cacheThisPage = \true, $pageIsCached = \false, $parseFileObjects = \true, $parseEmoji = \true )
    {
        $this->dtContentType = $contentType;
        parent::sendOutput( $output, $httpStatusCode, $contentType, $httpHeaders, $cacheThisPage, $pageIsCached, $parseFileObjects, $parseEmoji );
    }

}
