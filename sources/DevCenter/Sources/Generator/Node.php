<?php

/**
 * @brief       Node Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Dev Center Plus
 * @since       1.0.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\DevCenter\Sources\Generator;

use Exception;
use IPS\Content\ClubContainer;
use IPS\Helpers\Form;
use IPS\Node\Model;
use IPS\Node\Permissions;
use IPS\Node\Ratings;
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

class _Node extends GeneratorAbstract
{
    /**
     * @inheritdoc
     */
    protected function bodyGenerator()
    {
        $this->brief = 'Node';
        $this->extends = Model::class;

        if ( $this->useImports ) {
            $this->generator->addUse( Model::class );
        }

        $dbColumns = [
            'order',
            'parent',
            'enabled',
            'seoTitle',
        ];

        $this->databaseColumnParent();
        $this->databaseColumnParentRootValue();
        $this->databaseColumnOrder();
        $this->databaseColumnEnabledDisabled();
        $this->seoTitleColumn();
        $this->nodeTitle();
        $this->nodeSortable();
        $this->titleSearchPrefix();
        $this->urlTemplate();
        $this->urlBase();
        $this->_url();

        if ( $this->content_item_class !== \null ) {
            $this->nodeItemClass();
        }

        if ( is_array( $this->implements ) ) {
            $this->permissions();
            $this->ratings( $dbColumns );
        }

        if ( is_array( $this->traits ) ) {
            $this->clubs( $dbColumns );
        }

        $methodDocBlock = new DocBlockGenerator( '[Node] Add/Edit Form', \null, [
            new ParamTag( 'form', Form::class, 'The form' ),
            new ReturnTag( [ 'dataType' => 'void' ] ),
        ] );

        $this->methods[] = MethodGenerator::fromArray( [
            'name'       => 'form',
            'parameters' => [
                new ParameterGenerator( 'form', \null, \null, 0, \true ),
            ],
            'body'       => '',
            'docblock'   => $methodDocBlock,
            'static'     => \false,
        ] );

        //formatValues
        $methodDocBlock = new DocBlockGenerator( '[Node] Format form values from add/edit form for save', \null, [
            new ParamTag( 'values', 'array', 'Values from the form' ),
            new ReturnTag( [ 'dataType' => 'array' ] ),
        ] );

        $this->methods[] = MethodGenerator::fromArray( [
            'name'       => 'formatFormValues',
            'parameters' => [
                new ParameterGenerator( 'values' ),
            ],
            'body'       => 'return $values;',
            'docblock'   => $methodDocBlock,
            'static'     => \false,
        ] );

        $this->db->addBulk( $dbColumns );
        $this->_addToLangs( $this->app . '_' . $this->classname_lower . '_node', $this->classname, $this->application );
    }

    protected function databaseColumnParent()
    {
        try {
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Node] Parent ID Database Column' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'databaseColumnParent',
                'value'  => new PropertyValueGenerator( 'parent', PropertyValueGenerator::TYPE_STRING, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function databaseColumnParentRootValue()
    {
        try {
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Node] Parent ID Root Value' ],
                    [
                        'name'        => 'note',
                        'description' => 'This normally doesn\'t need changing though some legacy areas use -1 to indicate a root node',
                    ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'databaseColumnParentRootValue',
                'value'  => new PropertyValueGenerator( 0, PropertyValueGenerator::TYPE_INT ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function databaseColumnOrder()
    {
        try {
            $doc = [
                'tags' => [

                    [ 'name' => 'brief', 'description' => '[Node] Order Database Column' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'databaseColumnOrder',
                'value'  => new PropertyValueGenerator( 'order', PropertyValueGenerator::TYPE_STRING, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function databaseColumnEnabledDisabled()
    {
        try {
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Node] Enabled/Disabled Column' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'databaseColumnEnabledDisabled',
                'value'  => new PropertyValueGenerator( 'enabled', PropertyValueGenerator::TYPE_STRING, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function nodeTitle()
    {
        try {
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Node] Node Title' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'nodeTitle',
                'value'  => new PropertyValueGenerator( $this->app . '_' . $this->classname_lower . '_node', PropertyValueGenerator::TYPE_STRING ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function nodeSortable()
    {
        try {
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Node] Sortable?' ],
                    [ 'name' => 'var', 'description' => 'bool' ],
                ],
            ];

            $config = [
                'name'   => 'nodeSortable',
                'value'  => new PropertyValueGenerator( \true, PropertyValueGenerator::TYPE_BOOL ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function titleSearchPrefix()
    {
        try {
            $doc = [
                'tags' => [
                    [
                        'name'        => 'brief',
                        'description' => '[Node] Title search prefix.  If specified, searches for \'_title\' will be done against the language pack.',
                    ],
                    [ 'name' => 'var', 'description' => 'array' ],
                ],
            ];

            $config = [
                'name'   => 'titleSearchPrefix',
                'value'  => new PropertyValueGenerator( \null, PropertyValueGenerator::TYPE_NULL ),
                'vis'    => 'protected',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function nodeItemClass()
    {
        try {
            //nodeItemClass
            $this->content_item_class = mb_ucfirst( $this->content_item_class );

            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => 'Content Item Class' ],
                    [ 'name' => 'var', 'description' => '\\IPS\\' . $this->app . '\\' . $this->content_item_class ],
                ],
            ];

            $contentItemClass = '\\IPS\\' . $this->app . '\\' . $this->content_item_class . '::class';

            if ( $this->useImports ) {
                $this->generator->addUse( $this->content_item_class );
                $contentItemClass = $this->content_item_class . '::class';
            }

            $config = [
                'name'   => 'contentItemClass',
                'value'  => new PropertyValueGenerator( $contentItemClass, PropertyValueGenerator::TYPE_CONSTANT ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );

            //moderator permissions
            $doc = [
                'tags' => [
                    [ 'name' => 'brief', 'description' => '[Node] Moderator Permission' ],
                    [ 'name' => 'var', 'description' => 'string' ],
                ],
            ];

            $config = [
                'name'   => 'modPerm',
                'value'  => new PropertyValueGenerator( $this->app . '_' . $this->classname_lower, PropertyValueGenerator::TYPE_STRING ),
                'vis'    => 'public',
                'doc'    => $doc,
                'static' => \true,
            ];

            $this->addProperty( $config );
        } catch ( Exception $e ) {
        }
    }

    protected function permissions()
    {
        try {
            if ( in_array( Permissions::class, $this->implements, \true ) ) {

                //index
                $doc = [
                    'tags' => [
                        [ 'name' => 'brief', 'description' => '[Node] App for permission index' ],
                        [ 'name' => 'var', 'description' => 'string' ],
                    ],
                ];

                $config = [
                    'name'   => 'permApp',
                    'value'  => new PropertyValueGenerator( $this->app, PropertyValueGenerator::TYPE_STRING ),
                    'vis'    => 'public',
                    'doc'    => $doc,
                    'static' => \true,
                ];

                $this->addProperty( $config );

                //type
                $doc = [
                    'tags' => [
                        [ 'name' => 'brief', 'description' => '[Node] Type for permission index' ],
                        [ 'name' => 'var', 'description' => 'string' ],
                    ],
                ];

                $config = [
                    'name'   => 'permType',
                    'value'  => new PropertyValueGenerator( $this->classname_lower, PropertyValueGenerator::TYPE_STRING ),
                    'vis'    => 'public',
                    'doc'    => $doc,
                    'static' => \true,
                ];

                $this->addProperty( $config );

                //perms map
                $doc = [
                    'tags' => [
                        [ 'name' => 'brief', 'description' => 'The map of permission columns' ],
                        [ 'name' => 'var', 'description' => 'array' ],
                    ],
                ];

                $map = [
                    'view'   => 'view',
                    'read'   => 2,
                    'add'    => 3,
                    'delete' => 4,
                    'reply'  => 5,
                    'review' => 6,
                ];

                $config = [
                    'name'   => 'permissionMap',
                    'value'  => new PropertyValueGenerator( $map, PropertyValueGenerator::TYPE_ARRAY_LONG ),
                    'vis'    => 'public',
                    'doc'    => $doc,
                    'static' => \true,
                ];

                $this->addProperty( $config );

                //lang prefix
                $doc = [
                    'tags' => [
                        [
                            'name'        => 'brief',
                            'description' => '[Node] Prefix string that is automatically prepended to permission matrix language strings',
                        ],
                        [ 'name' => 'var', 'description' => 'string' ],
                    ],
                ];

                $config = [
                    'name'   => 'permissionLangPrefix',
                    'value'  => new PropertyValueGenerator( $this->app . '_' . $this->classname_lower, PropertyValueGenerator::TYPE_STRING ),
                    'vis'    => 'public',
                    'doc'    => $doc,
                    'static' => \true,
                ];

                $this->addProperty( $config );
            }
        } catch ( Exception $e ) {
        }
    }

    protected function ratings( &$dbColumns )
    {
        try {
            if ( in_array( Ratings::class, $this->implements, \true ) ) {

                //index
                $doc = [
                    'tags' => [
                        [
                            'name'        => 'brief',
                            'description' => '[Node] By mapping appropriate columns (rating_average and/or rating_total + rating_hits) allows to cache rating values',
                        ],
                        [ 'name' => 'var', 'description' => 'string' ],
                    ],
                ];

                $map = [
                    'rating_average' => 'rating_average',
                    'rating_total'   => 'rating_total',
                    'rating_hits'    => 'rating_hits',
                ];

                foreach ( $map as $m ) {
                    $dbColumns[] = $m;
                }

                $config = [
                    'name'   => 'ratingColumnMap',
                    'value'  => new PropertyValueGenerator( $map, PropertyValueGenerator::TYPE_ARRAY_LONG ),
                    'vis'    => 'public',
                    'doc'    => $doc,
                    'static' => \true,
                ];

                $this->addProperty( $config );
            }
        } catch ( Exception $e ) {
        }
    }

    protected function clubs( &$dbColumns )
    {
        try {
            if ( in_array( ClubContainer::class, $this->traits, \false ) ) {
                $dbColumns[] = 'club_id';
                $methodDocBlock = new DocBlockGenerator( 'Get the database column which stores the club ID', \null, [ new ReturnTag( [ 'dataType' => 'string' ] ) ] );

                $this->methods[] = MethodGenerator::fromArray( [
                    'name'     => 'clubIdColumn',
                    'body'     => "return 'club_id';",
                    'docblock' => $methodDocBlock,
                    'static'   => \true,
                ] );
            }
        } catch ( Exception $e ) {
        }
    }
}
