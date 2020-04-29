//<?php namespace a07c1956658b900e092b61bceece595ab;

use IPS\Settings;
use IPS\toolbox\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_Theme extends _HOOK_CLASS_
{
    public static function runProcessFunction($content, $functionName):void
    {
        $path = \IPS\ROOT_PATH . '/toolbox_templates/';

        $filename = $path . $functionName . md5($content) . '.php';
        /* If it's already been built, we don't need to do it again */
        if (\function_exists('IPS\Theme\\' . $functionName)) {
            return;
        }

        if (\IPS\IN_DEV === true && \IPS\NO_WRITES === false && mb_strpos(
                $functionName,
                'css_'
            ) === false && Settings::i()->toolbox_debug_templates) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            if (!file_exists($filename)) {
                try {
                    Application::loadAutoLoader();
                    $finder = new Finder();
                    $finder->in($path)->files()->name($functionName . '*.php');
                    $fs = new Filesystem();
                    foreach ($finder as $f) {
                        $fs->remove($f->getRealPath());
                    }
                } catch (\Exception $e) {
                }

                $content = <<<EOF
<?php

namespace IPS\Theme;
use function count;
use function in_array;
use function is_array;
use function is_object;

{$content}
EOF;


                try {
                    \file_put_contents($filename, $content);
                } catch (\Exception $e) {
                }
            }

            include_once($filename);
        } else {
            parent::runProcessFunction($content, $functionName);
        }
    }
}
