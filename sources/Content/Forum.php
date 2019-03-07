<?php

/**
 * @brief       Forums Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtcontent
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Content;

use Exception;
use InvalidArgumentException;
use IPS\Db;
use IPS\forums\Forum as IPSForum;
use IPS\toolbox\Text;
use function array_rand;
use function defined;
use function header;
use function is_int;
use function mb_strtolower;
use function random_int;
use function str_replace;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Forum extends Generator
{
    /**
     * @return \IPS\forums\Forum
     * @throws Exception
     */
    public static function get(): IPSForum
    {
        try {
            $db = Db::i()->select( '*', 'forums_forums', [ 'parent_id = ?', -1 ], 'RAND()' )->first();
            $db = IPSForum::constructFromData( $db );
        } catch ( Exception $e ) {
            ( new static )->build();
            $db = static::get();
            $db = IPSForum::constructFromData( $db );
        }

        try {
            $db = Db::i()->select( '*', 'forums_forums', [ 'parent_id = ?', $db->_id ], 'RAND()' )->first();
            $rand = random_int( 1, 10 );
            $rand /= 3;
            if ( is_int( $rand ) ) {
                try {
                    $db = Db::i()->select( '*', 'forums_forums', [ 'parent_id = ?', $db->_id ], 'RAND()' )->first();
                } catch ( Exception $e ) {
                }
            }
            $db = IPSForum::constructFromData( $db );
        } catch ( Exception $e ) {
        }

        return $db;
    }

    /**
     * builds a forum
     *
     * @throws Exception
     */
    public function build()
    {
        $parent = \null;
        $category = \false;
        try {
            $count = Db::i()->select( '*', 'forums_forums' )->count();

            if ( $count < 5 ) {
                $category = \false;
            }
            else {
                $parent = Db::i()->select( '*', 'forums_forums', [], 'RAND()' )->first();
                $parent = IPSForum::constructFromData( $parent );
                try {
                    $rand = random_int( 1, 10 );
                    if ( Db::i()->select( '*', 'forums_forums', [ 'parent_id = ?', $parent->id ] )->count() > $rand ) {
                        throw new InvalidArgumentException();
                    }
                } catch ( Exception $e ) {
                    $category = \false;
                }
            }
        } catch ( Exception $e ) {
            $this->build();
        }

        if ( $parent === \null ) {
            $category = \true;
        }

        $rand = array_rand( Data::$adjective, 1 );
        $rand2 = array_rand( Data::$noun, 1 );
        $name = str_replace( '_', ' ', Data::$adjective[ $rand ] . ' ' . Data::$noun[ $rand2 ] );
        $name = mb_ucfirst( mb_strtolower( $name ) );
        $desc = Data::$adjectiveGloss[ $rand ] . '; ' . Data::$nounGloss[ $rand2 ];
        $findType = ( $rand + $rand2 ) / random_int( 1, 20 );
        $type = 'normal';

        if ( !$category ) {
            if ( $parent === \null ) {
                try {
                    $parent = static::get();
                } catch ( Exception $e ) {
                    $this->build();
                }
            }

            $parent = $parent->id;
            if ( is_int( $findType ) ) {
                $type = 'qa';
            }

        }
        else {
            $parent = -1;
            $type = 'category';
        }

        $toSave = [
            'forum_name'        => $name,
            'forum_description' => $desc,
            'forum_type'        => $type,
            'forum_parent_id'   => $parent,
        ];

        if ( $type === 'qa' ) {
            $toSave[ 'forum_preview_posts_qa' ] = [];
            $toSave[ 'forum_can_view_others_qa' ] = 1;
            $toSave[ 'forum_sort_key_qa' ] = 'last_post';
            $toSave[ 'forum_permission_showtopic_qa' ] = 1;
        }
        else {
            $toSave[ 'forum_sort_key' ] = 'last_post';
        }

        $f = new IPSForum;
        $f->saveForm( $f->formatFormValues( $toSave ) );

        $insert = [
            'app'          => 'forums',
            'perm_type'    => 'forum',
            'perm_type_id' => $f->id,
            'perm_view'    => '*',
            'perm_2'       => '*',
            'perm_3'       => '*',
            'perm_4'       => '*',
            'perm_5'       => '*',
        ];

        try {
            Db::i()->insert( 'core_permission_index', $insert );
        } catch ( Exception $e ) {
        }

        $this->type = 'forum';
        $this->gid = $f->id;
        $this->save();
    }

}
