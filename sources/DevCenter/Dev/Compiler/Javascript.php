<?php

/**
 * @brief       Javascript Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Dev\Compiler;

use function implode;

class _Javascript extends CompilerAbstract
{
    /**
     * @inheritdoc
     */
    public function content(): string
    {
        $module = \null;
        $fname = \null;
        $tsn = \null;
        $replace = \true;
        $data = $this->_getFile($this->type);
        if ($this->type === 'widget') {
            $module = 'ips.ui.' . $this->app . '.' . $this->filename;
        } elseif ($this->type === 'controller') {
            $module = $this->app . '.' . $this->location . '.' . $this->group . '.' . $this->filename;
            $fname = 'ips.' . $module;
        } elseif ($this->type === 'module') {
            $module = 'ips.' . $this->app . '.' . $this->filename;
        } elseif ($this->type === 'jstemplate') {
            $module = 'ips.templates.' . $this->filename;
            $store = [];
            foreach ($this->templateName as $name) {
                $tsn = 'templates.' . $this->filename . '.' . $name;
                $content = $this->_getFile($this->type);
                $store[] = $this->_replace('{tsn}', $tsn, $content);
            }

            $replace = \false;
            $data = implode("\n", $store);
        } elseif ($this->type === 'jsmixin') {
            $module = $this->app . '.' . $this->filename;
            $fname = 'ips.' . $module;
        }

        if ($fname === \null) {
            $fname = $module;
        }

        $this->filename = $fname . '.js';
        if ($this->type === 'jstemplate') {
            $type = 'templates';
        } elseif ($this->type === 'jsmixin') {
            $type = 'mixin';
        } else {
            $type = 'controllers';
        }
        $this->location .= '/' . $type;

        if ($replace === \true) {
            $find = ['{module}', '{widgetname}', '{tsn}', '{controller}'];
            $replace = [$module, $this->widgetname, $tsn, $this->mixin];

            return $this->_replace($find, $replace, $data);
        }

        return $data;
    }
}
