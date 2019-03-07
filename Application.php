<?php
/**
 * @brief            Dev Toolbox: Base Application Class
 * @author           -storm_author-
 * @copyright        -storm_copyright-
 * @package          Invision Community
 * @subpackage       Dev Toolbox: Base
 * @since            02 Apr 2018
 * @version          -storm_version-
 */

namespace IPS\toolbox;

use IPS\Application;

/**
 * Dev Toolbox: Base Application Class
 */
class _Application extends Application
{
    public static $toolBoxApps = [
        'toolbox',
        'toolbox',
        'dtproxy',
        'dtprofiler',
    ];
    /**
     * @var string
     */
    protected static $baseDir = \IPS\ROOT_PATH . '/applications/toolbox/sources/vendor/';

    protected static $loaded = \false;

    public static function loadAutoLoader(): void
    {
        if ( static::$loaded === \false ) {
            static::$loaded = \true;
            require static::$baseDir . '/autoload.php';
        }
    }


    /**
     * @inheritdoc
     */
    protected function get__icon()
    {
        return 'wrench';
    }
}
