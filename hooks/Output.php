//<?php namespace abf5451658ee3e1f7e1b28a471debc86a;

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
