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

use function defined;
use function file_get_contents;
use function file_put_contents;
use function header;
use function in_array;
use function is_array;

use const IPS\ROOT_PATH;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
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
        $this->urlBase();
        $this->urlTemplate();
        $this->_url();

        if ($this->content_item_class !== \null) {
            $this->nodeItemClass();
        }

        if (is_array($this->implements)) {
            $this->permissions();
            $this->ratings($dbColumns);
        }

        if (is_array($this->traits)) {
            $this->clubs($dbColumns);
        }

        $doc = [
            '[Node] Add/Edit Form',
            '@param \\' . Form::class . ' $form',
            '@return void',
        ];

        $params = [
            ['name' => 'form', 'reference' => true],
        ];

        $this->generator->addMethod('form', '', $params, ['document' => $doc]);

        //formatValues
        $doc = [
            '[Node] Format form values from add/edit form for save',
            '@param array $values',
            '@return array',
        ];

        $params = [
            ['name' => 'values'],
        ];

        $this->generator->addMethod('formatFormValues', 'return $values;', $params, ['document' => $doc]);

        $this->db->addBulk($dbColumns);
        $this->_addToLangs($this->app . '_' . $this->classname_lower . '_node', $this->classname, $this->application);
    }

    protected function databaseColumnParent()
    {
        $doc = [
            '@brief [Node] Parent ID Database Column',
            '@var string',
        ];

        $this->generator->addProperty(
            'databaseColumnParent',
            'parent',
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function databaseColumnParentRootValue()
    {
        $doc = [
            '@brief [Node] Parent ID Root Value',
            '@note This normally doesn\'t need changing, though some legacy areas use -1 indicate a root node',
            '@var int',
        ];

        $this->generator->addProperty(
            'databaseColumnParentRootValue',
            0,
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function databaseColumnOrder()
    {
        $doc = [
            '@brief [Node] Order Database Column',
            '@var string',
        ];

        $this->generator->addProperty(
            'databaseColumnOrder',
            'order',
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function databaseColumnEnabledDisabled()
    {
        $doc = [
            '@brief [Node] Enabled/Disabled Column',
            '@var string',
        ];

        $this->generator->addProperty(
            'databaseColumnEnabledDisabled',
            'enabled',
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function nodeTitle()
    {
        $doc = [
            '@brief [Node] Node Title',
            '@var string',
        ];

        $this->generator->addProperty(
            'nodeTitle',
            $this->app . '_' . $this->classname_lower . '_node',
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function nodeSortable()
    {
        $doc = [
            '@brief [Node] Sortable?',
            '@var bool',
        ];

        $this->generator->addProperty(
            'nodeSortable',
            false,
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function nodeItemClass()
    {
        //nodeItemClass
        $this->content_item_class = mb_ucfirst($this->content_item_class);
        $contentItemClass = '\\IPS\\' . $this->app . '\\' . $this->content_item_class . '::class';
        $this->generator->addImport($contentItemClass);
        $contentItemClass = $this->content_item_class . '::class';

        $doc = [
            '@brief Content Item Class',
            '@var ' . $contentItemClass,
        ];

        $this->generator->addProperty(
            'contentItemClass',
            $contentItemClass,
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );

        //moderator permissions
        $doc = [
            '@brief [Node] Moderator Permission',
            '@var string',
        ];

        $this->generator->addProperty(
            'modPerm',
            $this->app . '_' . $this->classname_lower,
            [
                'visibility' => T_PUBLIC,
                'static' => true,
                'document' => $doc,
            ]
        );
    }

    protected function permissions()
    {
        try {
            if (in_array(Permissions::class, $this->implements, \true)) {
                //index
                $doc = [
                    '@brief [Node] App for permission index',
                    '@var string',
                ];

                $this->generator->addProperty(
                    'permApp',
                    $this->app,
                    [
                        'visibility' => T_PUBLIC,
                        'static' => true,
                        'document' => $doc,
                    ]
                );

                //type
                $doc = [
                    '@brief [Node] Type for permission index',
                    '@var string',
                ];

                $this->generator->addProperty(
                    'permApp',
                    $this->classname_lower,
                    [
                        'visibility' => T_PUBLIC,
                        'static' => true,
                        'document' => $doc,
                    ]
                );

                //perms map
                $map = [
                    'view' => 'view',
                    'read' => 2,
                    'add' => 3,
                    'delete' => 4,
                    'reply' => 5,
                    'review' => 6,
                ];

                $doc = [
                    '@brief The map of permission columns',
                    '@var array',
                ];

                $this->generator->addProperty(
                    'permissionMap',
                    $map,
                    [
                        'visibility' => T_PUBLIC,
                        'static' => true,
                        'document' => $doc,
                    ]
                );

                //lang prefix
                $doc = [
                    '@brief [Node] Prefix string that is automatically prepended to permission matrix language strings',
                    '@var string',
                ];

                $this->generator->addProperty(
                    'permissionLangPrefix',
                    $this->app . '_' . $this->classname_lower . '_',
                    [
                        'visibility' => T_PUBLIC,
                        'static' => true,
                        'document' => $doc,
                    ]
                );
            }
        } catch (Exception $e) {
        }
    }

    protected function ratings(&$dbColumns)
    {
        if (in_array(Ratings::class, $this->implements, \true)) {
            $map = [
                'rating_average' => 'rating_average',
                'rating_total' => 'rating_total',
                'rating_hits' => 'rating_hits',
            ];

            foreach ($map as $m) {
                $dbColumns[] = $m;
            }

            $doc = [
                '@brief [Node] By mapping appropriate columns (rating_average and/or rating_total + rating_hits) allows to cache rating values',
                '@var array',
            ];

            $this->generator->addProperty(
                'ratingColumnMap',
                $map,
                [
                    'visibility' => T_PUBLIC,
                    'static' => true,
                    'document' => $doc,
                ]
            );
        }
    }

    protected function clubs(&$dbColumns)
    {
        if (in_array(ClubContainer::class, $this->traits, \false)) {
            $doc = [
                'Get the database column which stores the club ID',
                '@return string',
            ];

            $this->generator->addMethod(
                'clubIdColumn',
                'return \'club_id\';',
                [],
                [
                    'static' => true,
                    'visibility' => T_PUBLIC,
                    'document' => $doc,
                ]
            );
        }
    }

    protected function addFurl($value, $url)
    {
        $furlFile = ROOT_PATH . '/applications/' . $this->application->directory . '/data/furl.json';
        if (file_exists($furlFile)) {
            $furls = json_decode(file_get_contents($furlFile), true);
        } else {
            $furls = [
                'topLevel' => $this->app,
                'pages' => [],
            ];
        }

        $furls['pages'][$value] = [
            'friendly' => $this->classname_lower . '/{#project}-{?}',
            'real' => $url,
        ];

        file_put_contents($furlFile, json_encode($furls, JSON_PRETTY_PRINT));
    }
}
