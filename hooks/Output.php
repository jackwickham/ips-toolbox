//<?php namespace toolbox_IPS_Output_a9d653a44c272efc6c73d7ef28b27b960;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_Output extends _HOOK_CLASS_
{
    public $dtContentType;

    public function sendOutput(
        $output = '',
        $httpStatusCode = 200,
        $contentType = 'text/html',
        $httpHeaders = array(),
        $cacheThisPage = true,
        $pageIsCached = false,
        $parseFileObjects = true,
        $parseEmoji = true
    ) {
        $this->dtContentType = $contentType;

        return parent::sendOutput(
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
