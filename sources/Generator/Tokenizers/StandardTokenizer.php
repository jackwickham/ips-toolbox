<?php

/**
 * @brief       StandardTokenizer Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace Generator\Tokenizers;

use Generator\Builders\ClassGenerator;

class StandardTokenizer extends ClassGenerator
{

    use Shared, ClassTrait;

    protected function writeBody()
    {

        $this->rebuildMethods();
        parent::writeBody();
    }

}
