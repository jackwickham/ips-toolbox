<?php

/**
 * @brief       DTTraitGenerator Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.2.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Generator;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

\IPS\toolbox\Application::loadAutoLoader();

use Zend\Code\Generator\TraitGenerator;
use function defined;
use function header;
use function preg_replace;

/**
 * Class _DTClassGenerator
 *
 * @package IPS\toolbox\DevCenter\Sources\Generator
 * @mixin \IPS\toolbox\DevCenter\Sources\Generator\DTTraitGenerator
 */
class _DTTraitGenerator extends TraitGenerator
{
    public function generate()
    {
        $parent = parent::generate();
        $addIn = <<<'eof'
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}
eof;

        $parent = preg_replace( '/namespace(.+?)([^\n]+)/', 'namespace $2' . self::LINE_FEED . self::LINE_FEED . $addIn, $parent );

        return $parent;
    }
}
