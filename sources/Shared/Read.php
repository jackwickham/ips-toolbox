<?php

/**
 * @brief       Read Trait
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Shared;

use function file_get_contents;
use function is_file;

trait Read
{
    /**
     * base directory to look in
     *
     * @var
     */
    protected $blanks;

    /**
     * extension of the file to look up
     *
     * @var string
     */
    protected $ext = '.txt';

    /**
     * retrieves the blanks from the fs
     *
     * @param $file
     *
     * @return bool|string
     */
    protected function _getFile( $file )
    {
        if ( is_file( $this->blanks . $file . $this->ext ) ) {
            return file_get_contents( $this->blanks . '/' . $file . $this->ext );
        }

        return \null;
    }

    protected function _getFileByFullPath( $path )
    {
        if ( is_file( $path ) ) {
            return file_get_contents( $path );
        }

        return \null;
    }
}
