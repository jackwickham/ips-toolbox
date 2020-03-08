<?php

/**
 * @brief       Dtproxy Proxyhelpers extension: Profiler
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Profiler
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\extensions\toolbox\ProxyHelpers;

use function defined;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER[ 'SERVER_PROTOCOL' ]) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * profiler
 */
class _profiler
{

    /**
     * add property to \IPS\Data\Store DocComment
     *
     * @param array $classDoc
     */
    public function store(&$classDoc)
    {
        $classDoc[] = ['pt' => 'p', 'prop' => 'dtprofiler_css', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'dtprofiler_js', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'dtprofiler_js_vars', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'dtprofiler_templates', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'dtprofiler_bt_cache', 'type' => 'array'];
        $classDoc[] = ['pt' => 'p', 'prop' => 'dtprofiler_bt', 'type' => 'array'];
    }

    /**
     * add property to \IPS\Request proxy DocComment
     *
     * @param attay $classDoc
     */
    public function request(&$classDoc)
    {
        $classDoc[] = ['pt' => 'p', 'prop' => 'bt', 'type' => 'string'];
    }
}
