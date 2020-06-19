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
        $module = null;
        $fname = null;
        $tsn = null;
        $replace = true;
        $options = [];
        $data = $this->_getFile($this->type);
        $fn = mb_ucfirst(mb_strtolower($this->filename));
        $widgetName = $this->app . $this->widgetname;
        if ($this->type === 'widget') {
            $module = 'ips.ui.' . $this->app . '.' . $this->filename;
            if (empty($this->options) !== true) {
                foreach ($this->options as $option) {
                    $options[] = $option;
                }
            }
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

            $replace = false;
            $data = implode("\n", $store);
        } elseif ($this->type === 'jsmixin') {
            $module = $this->app . '.' . $this->filename;
            $fname = 'ips.' . $module;
        }

        if ($fname === null) {
            $fname = $module;
        }
        $options = str_replace('"', "'", json_encode($options));

        $this->filename = $fname . '.js';
        if ($this->type === 'jstemplate') {
            $type = 'templates';
        } elseif ($this->type === 'jsmixin') {
            $type = 'mixin';
        } else {
            $type = 'controllers';
        }
        $this->location .= '/' . $type;

        if ($replace === true) {
            $find = [
                '{module}',
                '{widgetname}',
                '{tsn}',
                '{controller}',
                '{fn}',
                '{options}'
            ];
            $replace = [
                $module,
                $widgetName,
                $tsn,
                $this->mixin,
                $fn,
                $options
            ];

            return $this->_replace($find, $replace, $data);
        }

        return $data;
    }
}
