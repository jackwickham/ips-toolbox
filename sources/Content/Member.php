<?php

/**
 * @brief       Member Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Content Generator
 * @since       1.0.3
 * @version     -storm_version-
 */

namespace IPS\toolbox\Content;

use IPS\Db;
use IPS\Lang;
use IPS\Member;
use IPS\Settings;
use UnderflowException;
use function count;
use function defined;
use function header;
use function is_int;
use function iterator_to_array;
use function random_int;
use function sha1;
use function time;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Member extends Generator
{
    /**
     * @var array
     */
    protected static $availableClubIds = [];
    /**
     * @var bool
     */
    protected static $thereAreNoClubs = \false;
    public $start;
    public $end;
    /**
     * The new members first name
     *
     * @var string
     */
    protected $first = '';

    /**
     * The new members last name
     *
     * @var string
     */
    protected $last = '';

    /**
     * the new members username
     *
     * @var string
     */
    protected $user = '';

    /**
     * the new members email
     *
     * @var string
     */
    protected $email = '';

    /**
     * the new members password
     *
     * @var string
     */
    protected $pass = '';

    /**
     * _Member constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->first();
        $this->last();
        $this->username();
        $this->email();
        $this->type = 'member';
    }

    /**
     * selects the members first name
     *
     * @throws \Exception
     */
    protected function first()
    {
        $rand = random_int( 0, count( Data::$firstNames ) - 1 );
        $this->first = Data::$firstNames[ $rand ];
    }

    /**
     * selects the members lastname
     *
     * @throws \Exception
     */
    protected function last()
    {
        $rand = random_int( 0, count( Data::$lastNames ) - 1 );
        $this->last = Data::$lastNames[ $rand ];
    }

    /**
     * selects the members username
     *
     * @throws \Exception
     */
    protected function username()
    {
        $rand = random_int( 0, count( Data::$userNames ) );
        $dividedBy = 3;
        $num = $rand / $dividedBy;
        if ( is_int( $num ) ) {
            $placeHolder = [ '.', '-', '_', ' ' ];
            $p = random_int( 0, 3 );
            $placeHolder = $placeHolder[ $p ];
            $this->user = $this->first . $placeHolder . $this->last;
        }
        else {
            $rand = random_int( 0, count( Data::$userNames ) - 1 );
            $this->user = Data::$userNames[ $rand ];
        }
    }

    /**
     * selects the members email
     *
     * @throws \Exception
     */
    protected function email()
    {
        $rand = random_int( 0, count( Data::$domains ) - 1 );
        $domain = Data::$domains[ $rand ];
        $this->email = $this->user . '@' . $domain;
    }

    /**
     * @return \IPS\Member
     */
    public static function get()
    {
        try {
            $db = Db::i()->select( '*', 'core_members', [], 'RAND()' )->first();
            return Member::constructFromData( $db );
        } catch ( UnderflowException $e ) {
            return Member::load( 0 );
        }
    }

    /**
     * builds the user and stores to db to be remove later
     *
     * @param null $password
     * @param null $group
     * @param null $club
     *
     * @throws Db\Exception
     * @throws \Exception
     */
    public function build( $password = \null, $group = \null, $club = \null )
    {
        $existing = Member::load( $this->user, 'name' );

        if ( !$existing->member_id ) {

            if ( !$group || !is_int( $group ) ) {
                $group = Settings::i()->getFromConfGlobal( 'member_group' );
            }

            if ( $password ) {
                $this->pass = sha1( random_int( 1, 8800000083 ) );
            }
            else {
                $this->pass = $this->user;
            }

            $start = $this->start;
            $end = $this->end ?? time();

            try {
                $where = [];
                if ( $start !== \null ) {
                    $where = [ 'joined >= ?', $start ];
                }
                $sql = Db::i()->select( '*', 'core_members', $where, 'joined DESC' )->first();
                $start = $sql[ 'joined' ] + 60;
            } catch ( UnderflowException $e ) {
            }

            $time = $this->getTime( $start, $end );
            $member = new Member;
            $member->name = $this->user;
            $member->member_group_id = $group;
            $member->email = $this->email;
            $member->joined = $time;
            $member->language = Lang::defaultLanguage();
            $member->skin = 0;

            $member->setLocalPassword( $this->pass );
            $member->members_bitoptions[ 'coppa_user' ] = \false;
            $member->save();

            /* Add member to club? */
            if ( $club ) {
                if ( static::$thereAreNoClubs === \false && !count( static::$availableClubIds ) ) {
                    static::$availableClubIds = iterator_to_array( Db::i()->select( 'id', 'core_clubs' ) );

                    if ( !count( static::$availableClubIds ) ) {
                        static::$thereAreNoClubs = \true;
                    }
                }

                if ( static::$thereAreNoClubs === \false ) {
                    $joinedAClub = \false;

                    foreach ( static::$availableClubIds as $clubId ) {
                        $areWeJoining = random_int( 0, 1 ); /* 50% chance of joinging the club */

                        if ( $areWeJoining === 1 ) {
                            Db::i()->insert( 'core_clubs_memberships', [
                                'club_id'    => $clubId,
                                'member_id'  => $member->member_id,
                                'joined'     => time(),
                                'status'     => 'member',
                                'added_by'   => \null,
                                'invited_by' => \null,
                            ] );

                            $joinedAClub = \true;
                        }
                    }

                    if ( $joinedAClub ) {
                        $member->rebuildPermissionArray();
                    }
                }
            }

            $this->gid = $member->member_id;
            $this->save();
        }
        else {
            $this->first();
            $this->last();
            $this->username();
            $this->email();
            $this->build( $password, $group );
        }
    }

}
