<?php

/**
 * @brief       FileStorage Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtdevplus
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Extensions;

use function defined;
use function header;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Class _FileStorage
 *
 * @package IPS\toolbox\DevCenter\Extensions
 * @mixin \IPS\toolbox\DevCenter\Extensions\ExtensionsAbstract
 */
class _Headerdoc extends ExtensionsAbstract
{

    /**
     * @return array
     * @throws \Exception
     */
    public function elements()
    {
        $this->form->element('use_default')->toggles(['table', 'field'], true);
        $this->form->add('enabled', 'yn');
        $this->form->add('indexEnabled', 'yn');
        $this->form->add('dirSkip', 'stack');
        $this->form->add('fileSkip', 'stack');
        $this->form->add('exclude', 'stack');
        return $this->elements;
    }

    /**
     * @inheritdoc
     */
    protected function _content()
    {
        $content = $this->_getFile($this->extension);

        $find = [
            '{enabled}',
            '{indexEnabled}',
            '{dirSkip}',
            '{fileSkip}',
            '{exclude}'
        ];

        $dirSkip = '';
        if (empty($this->dirSkip) === false) {
            foreach ($this->dirSkip as $skipped) {
                $dirSkip .= "        \$skip[] = '{$skipped}';\n";
            }
        }


        $fileSkip = '';
        if (empty($this->fileSkip) === false) {
            foreach ($this->fileSkip as $skipped) {
                $fileSkip .= "        \$skip[] = '{$skipped}';\n";
            }
        }
        $exclude = '';
        if (empty($this->exclude) === false) {
            foreach ($this->exclude as $skipped) {
                $exclude .= "        \$skip[] = '{$skipped}';\n";
            }
        }
        $replace = [
            $this->enabled ? 'true' : 'false',
            $this->indexEnabled ? 'true' : 'false',
            $dirSkip,
            $fileSkip,
            $exclude
        ];
        unset($this->enabled, $this->indexEnabled, $this->dirSkip, $this->fileSkip, $this->exclude);
        return str_replace($find, $replace, $content);
    }
}
