<?php

/**
 * @brief       Item Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Sources\Generator;

use IPS\Content\EditHistory;
use IPS\Content\Featurable;
use IPS\Content\Hideable;
use IPS\Content\Item;
use IPS\Content\Lockable;
use IPS\Content\Pinnable;
use IPS\Content\Polls;
use IPS\Content\Ratings;
use IPS\Content\Reactable;
use IPS\Content\ReadMarkers;
use IPS\Content\Reportable;
use IPS\Content\Views;
use SplObserver;
use SplSubject;
use function defined;
use function header;
use function in_array;
use function is_array;
use function file_put_contents;
use function file_get_contents;
use const IPS\ROOT_PATH;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Item extends GeneratorAbstract
{

    /**
     * @inheritdoc
     */
    protected function bodyGenerator()
    {

        $this->brief = 'Content Item Class';
        $this->extends = 'Item';
        $this->generator->addUse( Item::class );

        $dbColumns = [
            'author',
            'title',
            'start_date',
            'ip_address',
            'seoTitle',
        ];

        $columnMap = [
            'author'     => 'author',
            'title'      => 'title',
            'date'       => 'start_date',
            'ip_address' => 'ip_address',
        ];

        $this->application();
        $this->module();
        $this->title();
        $this->itemNodeClass( $dbColumns, $columnMap );
        $this->urlBase();
        $this->urlTemplate();
        $this->_url();
        $this->seoTitleColumn();
        $this->commentClass( $dbColumns, $columnMap );
        $this->reviewClass( $dbColumns, $columnMap );
        $this->buildImplementsAndTraits( $dbColumns, $columnMap );
        $this->columnMap( $columnMap );
        $this->db->addBulk( $dbColumns );
    }

    /**
     * adds the application property
     */
    protected function application()
    {

        $doc = [
            '@brief Application',
            '@var string',
        ];

        $this->generator->addProperty( 'application', $this->app, [ 'static' => true, 'document' => $doc ] );
    }

    /**
     * adds the module property
     */
    protected function module()
    {

        $doc = [
            '@brief Module',
            '@var string',
        ];

        $this->generator->addProperty( 'module', $this->app, [ 'static' => true, 'document' => $doc ] );
    }

    /**
     * adds the title property
     *
     * @param string $extra
     */
    protected function title( $extra = '_title' )
    {

        $doc = [
            '@brief Title',
            '@var string',
        ];

        $this->generator->addProperty( 'application', $this->app . '_' . $this->classname_lower . $extra, [
            'static'   => true,
            'document' => $doc,
        ] );
    }

    /**
     * adds the containerNodeClass property
     */
    protected function itemNodeClass( &$dbColumns, &$columnMap )
    {

        if ( $this->item_node_class !== \null ) {
            $this->item_node_class = mb_ucfirst( $this->item_node_class );
            $itemNodeClass = 'IPS\\' . $this->app . '\\' . $this->item_node_class;
            $this->generator->addUse( $itemNodeClass );
            $itemNodeClass = $this->item_node_class;
            $itemNodeClass .= '::class';
            $dbColumns[] = 'container_id';
            $columnMap[ 'container' ] = 'container_id';
            $doc = [
                '@brief Node Class',
                '@var string',
            ];

            $extra = [
                'static'   => true,
                'document' => $doc,
            ];
            $this->generator->addProperty( 'containerNodeClass', $itemNodeClass, $extra );
        }
    }

    /**
     * adds the commentClass property and adds the database columns and then their relation to the columnmap
     *
     * @param $dbColumns
     * @param $columnMap
     */
    protected function commentClass( &$dbColumns, &$columnMap )
    {

        if ( $this->comment_class !== \null ) {
            $dbColumns[] = 'num_comments';
            $dbColumns[] = 'last_comment';
            $dbColumns[] = 'last_comment_by';
            $dbColumns[] = 'last_comment_name';
            $columnMap[ 'num_comments' ] = 'num_comments';
            $columnMap[ 'last_comment' ] = 'last_comment';
            $columnMap[ 'last_comment_by' ] = 'last_comment_by';
            $columnMap[ 'last_comment_name' ] = 'last_comment_name';
            $this->comment_class = mb_ucfirst( $this->comment_class );
            $commentClass = 'IPS\\' . $this->app . '\\' . $this->classname . '\\' . $this->comment_class;
            $this->generator->addUse( $commentClass );
            $commentClass = $this->comment_class;
            $commentClass .= '::class';

            $doc = [
                '@brief Comment Class',
                '@var string',
            ];

            $extra = [
                'static'   => true,
                'document' => $doc,
            ];
            $this->generator->addProperty( 'commentClass', $commentClass, $extra );
        }
    }

    /**
     * adds the reviewClass property and adds the database columns and then their relation to the columnmap
     *
     * @param $dbColumns
     * @param $columnMap
     */
    protected function reviewClass( &$dbColumns, &$columnMap )
    {

        if ( $this->review_class !== \null ) {
            $dbColumns[] = 'num_reviews';
            $dbColumns[] = 'last_review';
            $dbColumns[] = 'last_review_by';
            $dbColumns[] = 'unapproved_reviews';
            $dbColumns[] = 'last_review_name';
            $columnMap[ 'num_reviews' ] = 'num_reviews';
            $columnMap[ 'unapproved_reviews' ] = 'unapproved_reviews';
            $columnMap[ 'last_review' ] = 'last_review';
            $columnMap[ 'last_review_by' ] = 'last_review_by';
            $columnMap[ 'last_review_name' ] = 'last_review_name';

            //review class
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => 'Review Class' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $this->review_class = mb_ucfirst( $this->review_class );
            $reviewClass = 'IPS\\' . $this->app . '\\' . $this->classname . '\\' . $this->review_class;
            $this->generator->addUse( $reviewClass );
            $reviewClass = $this->review_class;
            $reviewClass .= '::class';
            $doc = [
                '@brief Review Class',
                '@var string',
            ];

            $extra = [
                'static'   => true,
                'document' => $doc,
            ];
            $this->generator->addProperty( 'reviewClass', $reviewClass, $extra );

            //reviews per page
            $doc = [
                '@brief [Content\Item]  Number of reviews to show per page',
                '@var int',
            ];

            $extra = [
                'static'   => true,
                'document' => $doc,
            ];
            $this->generator->addProperty( 'reviewsPerPage', 25, $extra );
        }
    }

    /**
     * this is mainly for items/comments/reviews to add implements/traits db columns and columnmap or class props
     *
     * @param $dbColumns
     * @param $columnMap
     */
    protected function buildImplementsAndTraits( &$dbColumns, &$columnMap )
    {

        if ( is_array( $this->implements ) ) {
            //edit history
            if ( in_array( EditHistory::class, $this->implements, \false ) ) {
                $dbColumns[] = 'edit_time';
                $dbColumns[] = 'edit_show';
                $dbColumns[] = 'edit_member_name';
                $dbColumns[] = 'edit_reason';
                $dbColumns[] = 'edit_member_id';
                $columnMap[ 'edit_time' ] = 'edit_time';
                $columnMap[ 'edit_show' ] = 'edit_show';
                $columnMap[ 'edit_member_name' ] = 'edit_member_name';
                $columnMap[ 'edit_reason' ] = 'edit_reason';
                $columnMap[ 'edit_member_id' ] = 'edit_member_id';
            }

            //featurable
            if ( in_array( Featurable::class, $this->implements, \false ) ) {
                $dbColumns[] = 'featured';
                $columnMap[ 'featured' ] = 'featured';
            }

            //Pinnable
            if ( in_array( Pinnable::class, $this->implements, \false ) ) {
                $dbColumns[] = 'pinned';
                $columnMap[ 'pinned' ] = 'pinned';
            }

            //Lockable
            if ( in_array( Lockable::class, $this->implements, \false ) ) {
                $dbColumns[] = 'locked';
                $columnMap[ 'locked' ] = 'locked';
            }

            //Hideable
            if ( in_array( Hideable::class, $this->implements, \false ) ) {
                $dbColumns[] = 'approved';
                $dbColumns[] = 'approved_by';
                $dbColumns[] = 'approved_date';
                $columnMap[ 'approved' ] = 'approved';
                $columnMap[ 'approved_by' ] = 'approved_by';
                $columnMap[ 'approved_date' ] = 'approved_date';
            }

            //Views
            if ( in_array( Views::class, $this->implements, \false ) ) {
                $dbColumns[] = 'views';
                $columnMap[ 'views' ] = 'views';
            }

            //ReadMarkers
            if ( in_array( ReadMarkers::class, $this->implements, \false ) ) {
                $dbColumns[] = 'updated_date';
                $columnMap[ 'updated' ] = 'updated_date';

                if ( $this->comment_class !== \null ) {
                    $dbColumns[] = 'last_comment';
                    $columnMap[ 'last_comment' ] = 'last_comment';
                }
            }

            //Polls
            if ( in_array( Polls::class, $this->implements, \false ) ) {
                $dbColumns[] = 'poll';
                $columnMap[ 'poll' ] = 'poll';
            }

            $find[] = '{polls}';
            $replace[ 'polls' ] = \null;
            //SplObserver - aka Polls well more polls or something like that
            if ( in_array( Polls::class, $this->implements, \false ) && in_array( SplObserver::class, $this->implements, \false ) ) {
                $poll = SplSubject::class;
                $this->generator->addUse( $poll );
                $poll = 'SplSubject';

                $doc = [
                    'SplObserver notification that poll has been voted on',
                    '@param ' . $poll . ' $poll SplObserver notification that poll has been voted on',
                    '@return void',
                ];
                $this->generator->addMethod( 'update', '', [ [ 'name' => 'poll', 'hint' => $poll ] ], $doc );
            }

            //Ratings
            if ( in_array( Ratings::class, $this->implements, \false ) ) {
                $dbColumns[] = 'rating_average';
                $dbColumns[] = 'rating_total';
                $dbColumns[] = 'rating_hits';
                $columnMap[ 'rating_average' ] = 'rating_average';
                $columnMap[ 'rating_total' ] = 'rating_total';
                $columnMap[ 'rating_hits' ] = 'rating_hits';
            }
        }

        if ( is_array( $this->traits ) ) {
            if ( in_array( Reactable::class, $this->traits, \false ) ) {

                $doc = [
                    'Reaction Type',
                    '@return string',
                ];
                $body = 'return \'' . $this->app . '_' . $this->classname_lower . '\';';
                $params = [];
                $extra = [
                    'static'   => true,
                    'document' => $doc,
                ];
                $this->generator->addMethod( 'reactionType', $body, $params, $extra );
            }

            if ( in_array( Reportable::class, $this->traits, \false ) ) {

                $extra = [
                    'static'   => true,
                    'document' => [
                        '@brief Icon',
                        '@var string',
                    ],
                ];
                $this->generator->addProperty( 'icon', 'cubes', $extra );
            }
        }
    }

    /**
     * creates the column map for the items.
     *
     * @param array $columnMap
     */
    protected function columnMap( array $columnMap )
    {

        $extra = [
            'static'   => true,
            'document' => [
                '@brief Database Column Map',
                '@var array',
            ],
        ];
        $this->generator->addProperty( 'databaseColumnMap', $columnMap, $extra );
    }

    protected function addFurl( $value, $url )
    {

        $furlFile = ROOT_PATH . '/applications/' . $this->application->directory . '/data/furl.json';
        if ( file_exists( $furlFile ) ) {
            $furls = json_decode( file_get_contents( $furlFile ), true );
        }
        else {
            $furls = [
                'topLevel' => $this->app,
                'pages'    => [],
            ];
        }

        $furls[ 'pages' ][ $value ] = [
            'friendly' => $this->classname_lower . '/' . mb_strtolower( $this->item_node_class ) . '/{#project}-{?}',
            'real'     => $url,
        ];

        file_put_contents( $furlFile, json_encode( $furls, JSON_PRETTY_PRINT ) );
    }
}
