//<?php namespace ab640658ea87576bd251e4e75ed69e81b;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_Output extends _HOOK_CLASS_
{
    public $dtContentType = '\null';


    public function sendOutput(
        $output = '',
        $httpStatusCode = 200,
        $contentType = 'text/html',
        $httpHeaders = [],
        $cacheThisPage = true,
        $pageIsCached = false,
        $parseFileObjects = true,
        $parseEmoji = true
    ) {
        $this->dtContentType = $contentType;
        parent::sendOutput(
            $output,
            $httpStatusCode,
            $contentType,
            $httpHeaders,
            $cacheThisPage,
            $pageIsCached,
            $parseFileObjects,
            $parseEmoji
        );
    }
}
