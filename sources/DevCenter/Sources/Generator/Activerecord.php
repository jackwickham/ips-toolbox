<?php

/**
 * @brief       ActiveRecord Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Sources\Generator;

use IPS\Patterns\ActiveRecord;

class _Activerecord extends GeneratorAbstract
{
    /**
     * @inheritdoc
     */
    protected function bodyGenerator()
    {
        $this->brief = 'Active Record';
        $this->extends = ActiveRecord::class;
    }
}
