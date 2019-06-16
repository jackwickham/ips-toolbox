<?php

/**
 * @brief       Generator Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Content Generator
 * @since       1.0.3
 * @version     -storm_version-
 */

namespace IPS\toolbox\Content;

use Exception;
use IPS\forums\Forum;
use IPS\forums\Topic;
use IPS\forums\Topic\Post;
use IPS\Member;
use IPS\Patterns\ActiveRecord;
use IPS\Settings;
use function defined;
use function header;
use function random_int;
use function time;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * @brief      _Generator Class
 * @mixin \IPS\dtcontent\Generator
 */
class _Generator extends ActiveRecord
{
    /**
     * @brief    [ActiveRecord] Database Prefix
     */
    public static $databasePrefix = 'generator_';
    /**
     * @brief    [ActiveRecord] Database table
     */
    public static $databaseTable = 'toolbox_generator';
    /**
     * @brief   Bitwise keys
     */
    protected static $bitOptions = [];
    /**
     * @brief    [ActiveRecord] Multiton Store
     */
    protected static $multitons;
    protected $loops = 0;

    /**
     * this is called when the records the dtcontent generates need to be removed :)
     */
    public function process()
    {
        try {
            switch ( $this->type ) {
                case 'member':
                    $d = Member::load( $this->gid );
                    $d->delete();
                    break;
                case 'forum':
                    $d = Forum::load( $this->gid );
                    $d->delete();
                    break;
                case 'topic':
                    $d = Topic::load( $this->gid );
                    $d->delete();
                    break;
                case 'post':
                    $d = Post::load( $this->gid );
                    $d->delete();
                    break;
            }
        } catch ( Exception $e ) {
        }

        $this->delete();
    }

    /**
     * gets a timestamp that is newer than board start date, so we don't have timetravellers
     *
     * @param null $date
     *
     * @return float|int|mixed|null
     * @throws Exception
     */
    protected function getTime( $start = \null, $end = \null )
    {
        $this->loops++;
        $rand = random_int( 1, 3 );
        $time = 60;

        if ( $start === \null ) {
            $start = Settings::i()->getFromConfGlobal( 'board_start' );
        }

        if ( $end === \null ) {
            $end = time();
        }

        switch ( $rand ) {
            case 1:
                $time = 60;
                break;
            case 2:
                $time = 3600;
                break;
            case 3:
                $time = 84000;
                break;
        }

        $foo = random_int( 1, 1000 );
        $time = $start + ( $foo * $time );

        if ( $time > $end && $this->loops < 10 ) {
            $time = $this->getTime();
        }
        else {
            $time = $start;
        }

        return $time;
    }

}
