#!/bin/bash
<?php

use IPS\dtproxy\Proxyclass;

require_once str_replace('applications/dtproxy/interface/cli/cli.php', '',
        str_replace('\\', '/', __FILE__)) . 'init.php';

$fp = \null;
try {
    $start = microtime(\true);
    /**
     * @todo document how this works
     */
    if (isset($argv)) {
        $args = $argv;
        unset($args[ 0 ]);
        if (is_array($args) && count($args)) {
            $fp = array_pop($fp);
        }
    }


    Proxyclass::i()->cli($fp);

    $end = microtime(\true) - $start;
    $end = round($end, 0);
    Proxyclass::i()->console("Time: {$end}s");
    Proxyclass::i()->consoleClose();

} catch (\Exception $e) {
    Proxyclass::i()->consoleClose();
    echo $e->getMessage();
    echo $e->getTraceAsString();
}
