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
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use function defined;
use function header;
use function in_array;
use function is_array;

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
        $this->extends = Item::class;

        if ( $this->useImports ) {
            $this->generator->addUse( Item::class );
        }

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
        $this->urlTemplate();
        $this->urlBase();
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
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Application' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'application',
            'value'  => new PropertyValueGenerator( $this->app, PropertyValueGenerator::TYPE_STRING ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    /**
     * adds the module property
     */
    protected function module()
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Module' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'module',
            'value'  => new PropertyValueGenerator( $this->app, PropertyValueGenerator::TYPE_STRING ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    /**
     * adds the title property
     *
     * @param string $extra
     */
    protected function title( $extra = '_title' )
    {

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Title' ],
                [ 'name' => 'var', 'description' => 'string' ],
            ],
        ];

        $config = [
            'name'   => 'title',
            'value'  => new PropertyValueGenerator( $this->app . '_' . $this->classname_lower . $extra, PropertyValueGenerator::TYPE_STRING ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }

    /**
     * adds the containerNodeClass property
     */
    protected function itemNodeClass( &$dbColumns, &$columnMap )
    {

        if ( $this->item_node_class !== \null ) {
            $this->item_node_class = mb_ucfirst( $this->item_node_class );

            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => 'Node Class' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $itemNodeClass = 'IPS\\' . $this->app . '\\' . $this->item_node_class;

            if ( $this->useImports ) {
                $this->generator->addUse( $itemNodeClass );
                $itemNodeClass = $this->item_node_class;
            }

            $itemNodeClass .= '::class';
            $dbColumns[] = 'container_id';
            $columnMap[ 'container' ] = 'container_id';
            $config = [
                'name'   => 'containerNodeClass',
                'value'  => new PropertyValueGenerator( $itemNodeClass, PropertyValueGenerator::TYPE_CONSTANT ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
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
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => 'Comment Class' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $this->comment_class = mb_ucfirst( $this->comment_class );
            $commentClass = 'IPS\\' . $this->app . '\\' . $this->classname . '\\' . $this->comment_class;

            if ( $this->useImports ) {
                $this->generator->addUse( $commentClass );
                $commentClass = $this->comment_class;
            }
            $commentClass .= '::class';

            $config = [
                'name'   => 'commentClass',
                'value'  => new PropertyValueGenerator( $commentClass, PropertyValueGenerator::TYPE_CONSTANT ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
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

            if ( $this->useImports ) {
                $this->generator->addUse( $reviewClass );
                $reviewClass = $this->review_class;
            }

            $reviewClass .= '::class';

            $config = [
                'name'   => 'reviewClass',
                'value'  => new PropertyValueGenerator( $reviewClass, PropertyValueGenerator::TYPE_CONSTANT ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );

            //reviews per page
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Content\Item]  Number of reviews to show per page' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'reviewsPerPage',
                'value'  => new PropertyValueGenerator( 25, PropertyValueGenerator::TYPE_INT ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
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
                $methodDocBlock = new DocBlockGenerator( '	 * SplObserver notification that poll has been voted on
', \null, [
                    new ParamTag( 'poll', SplSubject::class, 'SplObserver notification that poll has been voted on' ),
                    new ReturnTag( [ 'dataType' => 'void' ] ),
                ] );

                $poll = SplSubject::class;

                if ( $this->useImports ) {
                    $this->generator->addUse( $poll );
                    $poll = 'SplSubject';
                }

                $this->methods[] = MethodGenerator::fromArray( [
                    'name'       => 'update',
                    'parameters' => [
                        new ParameterGenerator( 'poll', $poll ),
                    ],
                    'body'       => '',
                    'docblock'   => $methodDocBlock,
                    'static'     => \false,
                ] );
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

                $methodDocBlock = new DocBlockGenerator( 'Reaction Type', \null, [
                    new ReturnTag( [ 'dataType' => 'string' ] ),
                ] );

                $this->methods[] = MethodGenerator::fromArray( [
                    'name'     => 'reactionType',
                    'body'     => 'return \'' . $this->app . '_' . $this->classname_lower . '\';',
                    'docblock' => $methodDocBlock,
                    'static'   => \true,
                ] );
            }

            if ( in_array( Reportable::class, $this->traits, \false ) ) {
                $doc = [
                    'tags' => [
                        [ 'name' => 'brief', 'description' => 'Icon' ],
                        [ 'name' => 'var', 'description' => 'string' ],
                    ],
                ];

                $config = [
                    'name'   => 'icon',
                    'value'  => new PropertyValueGenerator( 'cubes', PropertyValueGenerator::TYPE_STRING ),
                    'vis'    => 'public',
                    'doc'    => $doc,
                    'static' => \true,
                ];

                $this->addProperty( $config );
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

        $doc = [
            'tags' => [
                [ 'name' => 'brief', 'description' => 'Database Column Map' ],
                [ 'name' => 'var', 'description' => 'array' ],
            ],
        ];

        $config = [
            'name'   => 'databaseColumnMap',
            'value'  => new PropertyValueGenerator( $columnMap, PropertyValueGenerator::TYPE_ARRAY_LONG ),
            'vis'    => 'public',
            'doc'    => $doc,
            'static' => \true,
        ];

        $this->addProperty( $config );
    }
}
