//<?php namespace aa3f261250b082f37a2ca684a12bc5106;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_BuilderIterator extends _HOOK_CLASS_
{
    /**
     * @inheritdoc
     */
    public function current()
    {
        $file = $this->key();
        $file = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/' . $file;
        $path = new \SplFileInfo($this->key());
        if (\is_file($file) && (\mb_strpos($file, '3rdparty') === \false || \mb_strpos(
            $file,
            '3rd_party'
        ) === \false || \mb_strpos($file, 'vendor') === \false)) {
            if ($path->getExtension() === 'php') {
                $temporary = \tempnam(\IPS\TEMP_DIRECTORY, 'IPS');
                if (\mb_strpos($path->getPath(), 'hooks') !== \false) {
                    $contents = \IPS\Plugin::addExceptionHandlingToHookFile($file);
                    if (\is_array($this->application->extensions('toolbox', 'SpecialHooks'))) {
                        $appDir = \IPS\ROOT_PATH . '/applications/' . $this->application->directory;
                        $dir = $appDir . '/data/hooks.json';
                        $hooks = json_decode(\file_get_contents($dir), true);
                        foreach ($hooks as $file => $data) {
                            if (isset($data['type']) && $data['type'] === 'C') {
                                $newContent = '';
                                $i=0;
                                foreach (explode(PHP_EOL, $contents) as $line) {
                                    if ($i === 0) {
                                        $newContent .= '//<?php' . PHP_EOL;
                                        $i++;
                                    } else {
                                        $newContent .= $line . PHP_EOL;
                                    }
                                }
                                $contents = $newContent;
                            }
                        }
                    }
                } else {
                    $contents = \file_get_contents($file);
                }
                if (\IPS\toolbox\DevCenter\Headerdoc::i()->can($this->application)) {
                /* @var \IPS\toolbox\DevCenter\extensions\toolbox\DevCenter\Headerdoc\Headerdoc $class */
                    foreach ($this->application->extensions('toolbox', 'Headerdoc', \true) as $class) {
                        if (\method_exists($class, 'finalize')) {
                            $contents = $class->finalize($contents, $this->application);
                        }
                    }
                }
                \file_put_contents($temporary, $contents);
                \register_shutdown_function(
                    function ($temporary) {
                        \unlink($temporary);
                    },
                    $temporary
                );

                return $temporary;
            }
        }

        return $file;
    }
}
