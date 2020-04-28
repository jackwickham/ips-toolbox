<?php

/**
 * @brief       Club Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  dtcontent
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Content;

use IPS\DateTime;
use IPS\Member\Club;
use IPS\toolbox\Text;
use function array_rand;
use function count;
use function defined;
use function explode;
use function header;
use function in_array;
use function is_array;
use function is_int;
use function mb_strtolower;
use function nl2br;
use function random_int;
use function str_replace;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _Club
 *
 * @mixin \IPS\dtcontent\Generator
 */
class _Club extends Generator
{
    /**
     * @throws \Exception
     */
    public function build()
    {

        $member = Member::get();
        $availableTypes = [];
        if ( $member->member_id ) // Guests can't create any type of clubs
        {
            foreach ( explode( ',', $member->group[ 'g_create_clubs' ] ) as $ctypes ) {
                if ( $ctypes !== '' ) {
                    $availableTypes[] = $ctypes;
                }
            }

            /**
             * @var DateTime $joined
             */
            $joined = $member->joined;
            $time = $this->getTime( $joined->getTimestamp() );
            if ( is_array( $availableTypes ) && count( $availableTypes ) ) {
                $type = array_rand( $availableTypes );
                $type = $availableTypes[ $type ];
            }
            else {
                $type = 'public';
            }

            if ( $type === 'private' ) {
                $rand = random_int( 0, 10 );
                if ( is_array( $availableTypes ) && !in_array( $rand, [
                        2,
                        6,
                        9,
                    ], \true ) && count( $availableTypes ) ) {
                    $type = array_rand( $availableTypes );
                    $type = $availableTypes[ $type ];
                }
            }

            $featured = 0;
            $featuredRandom = random_int( 1, 3000 ) / 23;
            if ( is_int( $featuredRandom ) ) {
                $featured = 1;
            }

            $rand = array_rand( Data::$adjective, 1 );
            $rand2 = array_rand( Data::$noun, 1 );
            $name = str_replace( '_', ' ', Data::$adjective[ $rand ] . ' ' . Data::$noun[ $rand2 ] );
            $name = mb_ucfirst( mb_strtolower( $name ) );
            $desc = Data::$adjectiveGloss[ $rand ] . '; ' . Data::$nounGloss[ $rand2 ];

            $club = new Club;
            $club->name = $name;
            $club->type = $type;
            $club->created = DateTime::ts( $time );
            $club->members = 1;
            $club->owner = $member;
            $club->featured = $featured;
            $club->about = nl2br( $desc );
            $club->last_activity = $time;
            $club->content = 0;
            $club->approved = 1;
            $club->location_json = \null;
            $club->location_lat = \null;
            $club->location_long = \null;
            $club->save();
            $club->addMember( $member, Club::STATUS_LEADER );

            $this->type = 'club';
            $this->gid = $club->id;
            $this->save();
        }
    }
}
