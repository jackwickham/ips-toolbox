<?php

/**
 * @brief       Topic Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtcontent
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Content;

use Exception;
use IPS\DateTime;
use IPS\Db;
use IPS\forums\Topic;
use IPS\toolbox\Text;
use UnderflowException;
use function array_rand;
use function count;
use function defined;
use function header;
use function is_array;
use function mb_strtolower;
use function random_int;
use function str_replace;
use function time;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Topic extends Generator
{

    public $start;

    public $end;

    /**
     * @return \IPS\forums\Topic
     * @throws Exception
     * @throws UnderflowException
     */
    public static function get(): Topic
    {

        try {
            $dbFirst = Db::i()->select( '*', 'forums_topics', [], 'id ASC' )->first();
            $dbLast = Db::i()->select( '*', 'forums_topics', [], 'id DESC' )->first();
            $range = random_int( $dbFirst[ 'id' ], $dbLast[ 'id' ] );
            $db = Db::i()->select( '*', 'forums_topics', [ 'id = ?', $range ], 'id DESC' )->first();
        } catch ( Exception $e ) {
            $db = Db::i()->select( '*', 'forums_topics', [], 'RAND()' )->first();
        }

        if ( !is_array( $db ) && !count( $db ) ) {
            ( new static )->build();

            return static::get();
        }

        return Topic::constructFromData( $db );
    }

    /**
     * build a topic
     *
     * @throws Exception
     */
    public function build()
    {

        $forum = Forum::get();
        $member = Member::get();
        $rand = array_rand( Data::$adjective, 1 );
        $rand2 = array_rand( Data::$noun, 1 );
        $name = str_replace( '_', ' ', Data::$adjective[ $rand ] . ' ' . Data::$noun[ $rand2 ] );
        $name = mb_ucfirst( mb_strtolower( $name ) );
        $start = $this->start;
        $end = $this->end ?? time();

        try {
            $sql = Db::i()->select( '*', 'forums_posts', [], 'post_date DESC' )->first();
            $time = $sql[ 'post_date' ] + 60;

        } catch ( UnderflowException $e ) {
            $time = $start;
            /**
             * @var DateTime $joined
             */
            $joined = $member->joined;
            if ( $time > $joined->getTimestamp() ) {
                $time = $joined->getTimestamp();
            }
        }

        $topic = Topic::createItem( $member, $member->ip_address, DateTime::ts( $time ), $forum );
        $topic->title = $name;
        $topic->save();
        $post = ( new Post )->build( $topic, $member, \true );
        $topic->topic_firstpost = $post->pid;
        $topic->save();

        $this->type = 'topic';
        $this->gid = $topic->tid;
        $this->save();
        $rand = random_int( 1, 30 );
        for ( $i = 0; $i < $rand; $i++ ) {
            ( new Post )->build( $topic );
        }
    }

}
