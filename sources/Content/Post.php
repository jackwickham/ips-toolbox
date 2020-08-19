<?php

/**
 * @brief       Post Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtcontent
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Content;

use IPS\DateTime;
use IPS\forums\Topic\Post;
use function array_rand;
use function defined;
use function header;
use function is_int;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Post extends Generator
{
    /**
     * builds a post
     *
     * @param \IPS\forums\Topic|null $topic
     * @param \IPS\Member|null       $member
     * @param bool                   $first
     *
     * @return Post|null
     * @throws \Exception
     */
    public function build( \IPS\forums\Topic $topic = \null, \IPS\Member $member = \null, $first = \false )
    {
        $rand = array_rand( Data::$postData, 1 );
        $content = '<p>' . Data::$postData[ $rand ] . '</p>';
        $double = $rand / 17;

        if ( is_int( $double ) ) {
            //have no idea why i chose 421 here? lol
            if ( $rand === 421 ) {
                $cur = 0;
            }
            else {
                $cur = $rand + 1;
            }

            $content .= '<p>' . Data::$postData[ $cur ] . '</p>';
        }

        if ( !$member ) {
            $member = Member::get();
        }

        if ( !$topic ) {
            $topic = Topic::get();
            /* @var \IPS\forums\Topic\Post $comment */
            $comment = $topic->comments( 1, 0, 'date', 'desc' );
            $time = $comment->post_date;

            /**
             * @var DateTime $joined
             */
            $joined = $member->joined;
            if ( $time > $joined->getTimestamp() ) {
                $time = $joined->getTimestamp();
            }

            $time = $this->getTime( $time );
        }
        else {
            $time = $topic->start_date;
            if ( !$first ) {
                $time = $topic->last_post;

                /**
                 * @var DateTime $joined
                 */
                $joined = $member->joined;
                if ( $time > $joined->getTimestamp() ) {
                    $time = $joined->getTimestamp();
                }

                $time = $this->getTime( $time );
            }
        }

        /* @var \IPS\forums\Topic\Post $post */
        $post = Post::create( $topic, $content, $first, \null, \true, $member, DateTime::ts( $time ) );

        $this->type = 'post';
        $this->gid = $post->pid;
        $this->save();

        if ( $first ) {
            return $post;
        }

        return \null;
    }

}
