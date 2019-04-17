<?php

/**
 * @brief       Git Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Profiler
 * @since       1.4.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\Profiler\Profiler;

use function basename;
use function dirname;
use function preg_match;
use function realpath;

class _Git
{

    protected $path;

    /**
     * _Git constructor.
     *
     * @param $path
     *
     * @throws \InvalidArgumentException
     */
    public function __construct( $path )
    {
        if ( basename( $path ) === '.git' ) {
            $path = dirname( $path );
        }

        $this->path = realpath( $path );

        if ( $this->path === \false ) {
            throw new \InvalidArgumentException( "Repository '$path' not found." );
        }
    }

    public function getLastCommitId()
    {
        $lastLine = [];
        $this->exec( "log --pretty=format:%H -n 1 2>&1", $lastLine );
        if ( isset( $lastLine[ 0 ] ) && preg_match( '/^[0-9a-f]{40}$/i', $lastLine[ 0 ] ) ) {
            return $lastLine[ 0 ];
        }
        return \null;
    }

    public function exec( $command, &$output = \null )
    {
        $cwd = \getcwd();
        \chdir( $this->path );
        \exec( "git $command", $output );
        \chdir( $cwd );
    }

    public function getLastCommitMessage()
    {
        $msg = [];
        $this->exec( 'log -1 --pretty=%B', $msg );
        return $msg;
    }

    public function getCurrentBranchName()
    {
        $branches = [];
        $this->exec( 'branch', $branches );
        if ( !empty( $branches ) ) {
            foreach ( $branches as $branch ) {
                if ( \mb_strpos( $branch, '*' ) !== \false ) {
                    return \trim( \mb_substr( $branch, 1 ) );
                }
            }
        }
        return \null;
    }

    public function getBranches()
    {
        $branches = [];
        $this->exec( 'branch -a', $branches );
        return $branches;
    }
    //    public function checkout( $name ){
    //        print_r('checkout '.$name.' 2>&1');exit;
    //        $this->exec( 'checkout '.$name.' 2>&1' );
    //    }

    public function hasChanges()
    {
        // Make sure the `git status` gets a refreshed look at the working tree.
        $this->exec( 'update-index -q --refresh' );
        $output = \null;
        $this->exec( 'git status --porcelain', $output );
        return !empty( $output );
    }
}


