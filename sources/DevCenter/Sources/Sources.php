<?php
/**
 * @brief      Elements Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage dtdevplus
 * @since      -storm_since_version-
 * @version    -storm_version-
 */

namespace IPS\toolbox\DevCenter;

use InvalidArgumentException;
use IPS\Application;
use IPS\Content\ClubContainer;
use IPS\Content\EditHistory;
use IPS\Content\Embeddable;
use IPS\Content\Featurable;
use IPS\Content\Followable;
use IPS\Content\FuturePublishing;
use IPS\Content\Hideable;
use IPS\Content\Lockable;
use IPS\Content\MetaData;
use IPS\Content\Pinnable;
use IPS\Content\Polls;
use IPS\Content\Reactable;
use IPS\Content\ReadMarkers;
use IPS\Content\Reportable;
use IPS\Content\Searchable;
use IPS\Content\Shareable;
use IPS\Content\Tags;
use IPS\Content\Views;
use IPS\Http\Url;
use IPS\Member;
use IPS\Node\Colorize;
use IPS\Node\Permissions;
use IPS\Node\Ratings;
use IPS\Output;
use IPS\Request;
use IPS\Theme;
use IPS\toolbox\DevCenter\Sources\Generator\GeneratorAbstract;
use IPS\toolbox\DevCenter\Sources\SourcesFormAbstract;
use IPS\toolbox\ReservedWords;
use SplObserver;
use function array_keys;
use function class_exists;
use function count;
use function header;
use function in_array;
use function interface_exists;
use function mb_ucfirst;
use function is_array;
use function trait_exists;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Elements Class
 *
 * @mixin \IPS\toolbox\DevCenter\Sources\Elements
 */
class _Sources
{

    /**
     * @var \IPS\Helpers\Form
     */
    public $form;

    public $type;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $types;

    /**
     * _Elements constructor.
     *
     * @param Application $application
     */
    public function __construct( Application $application = null )
    {

        $this->application = $application;
    }

    /**
     * @throws \UnexpectedValueException
     */
    public static function menu()
    {

        if ( Request::i()->controller === 'sources' || Request::i()->controller === 'devFolder' ) {
            Output::i()->sidebar[ 'actions' ][ 'devcenter' ] = [
                'icon'  => \null,
                'title' => 'dtdevplus_devcenter',
                'link'  => (string)Url::internal( 'app=core&module=applications&controller=developer&appKey=' . Request::i()->appKey ),
            ];
        }
        Output::i()->sidebar[ 'actions' ][ 'sources' ] = [
            'icon'  => 'arrow-down',
            'title' => 'dtdevplus_sources',
            'link'  => '#adminMenu_button',
            'id'    => 'adminMenu_button',
            'data'  => [
                'ipsMenu' => 1,
            ],
        ];

        Output::i()->sidebar[ 'actions' ][ 'dev' ] = [
            'icon'  => 'code',
            'title' => 'dtdevplus_dev',
            'link'  => '#adminMenuDev_button',
            'id'    => 'adminMenuDev_button',
            'data'  => [
                'ipsMenu' => 1,
            ],
        ];

        Output::i()->sidebar[ 'mobilenav' ] = static::subMenus();
    }

    public static function subMenus()
    {

        $types = [
            'standard',
            'cinterface',
            'ctraits',
            'singleton',
            'ar',
            'api',
            'node',
            'item',
            'comment',
            'review',
            'debug',
            'memory',
            //            'form',
        ];

        $dev = [
            'template',
            'widget',
            'module',
            'controller',
            'jstemplate',
            'jsmixin',
        ];

        return Theme::i()->getTemplate( 'dtdpmenu', 'toolbox', 'admin' )->menu( $types, $dev, Request::i()->appKey );
    }

    /**
     * @param array  $config
     * @param string $type
     */
    public function buildForm( array $config, string $type )
    {

        $this->type = $type;

        foreach ( $config as $func ) {
            $method = 'el' . $func;
            $this->{$method}();
        }

        $this->form = Form::buildForm( $this->elements, 'dtdevplus_class_' );
    }

    /**
     * create file
     */
    public function create()
    {

        if ( $values = $this->form->values() ) {
            $this->generate( $values );
        }
    }

    public function generate( array $values = [] )
    {

        /* @var Application $app */
        foreach ( Application::allExtensions( 'toolbox', 'SourcesFormAbstract' ) as $app ) {
            /* @var SourcesFormAbstract $extension */
            foreach ( $app->extensions( 'toolbox', 'SourcesFormAbstract' ) as $extension ) {
                $extension->formProcess( $values );
            }
        }
        /* @var GeneratorAbstract $class */
        $class = 'IPS\\toolbox\DevCenter\\Sources\\Generator\\';
        //$class = 'IPS\\toolbox\DevCenter\\Sources\\Compiler\\';
        $type = $this->type;
        $values[ 'type' ] = mb_ucfirst( $type );
        switch ( $type ) {
            case 'Memory':
            case 'Debug':
                $class .= 'Profiler';
                $values[ 'dtdevplus_class_className' ] = mb_ucfirst( $type );
                $values[ 'dtdevplus_class_namespace' ] = 'Profiler';
                break;
            case 'Form':
                $class .= 'Form';
                $values[ 'dtdevplus_class_className' ] = 'Form';
                $values[ 'dtdevplus_class_namespace' ] = '';
                break;
            default:
                $class .= mb_ucfirst( $type );
                break;
        }
        $class = new $class( $values, $this->application );
        $class->process();

        if ( !$class->error ) {
            $msg = Member::loggedIn()->language()->addToStack( 'dtdevplus_class_created', \false, [
                'sprintf' => [
                    $type,
                    $class->classname,
                ],
            ] );
        }
        else {
            $msg = Member::loggedIn()->language()->addToStack( 'dtdevplus_class_db_error', \false, [
                'sprintf' => [
                    'type',
                    $class->classname,
                    $class->database,
                ],
            ] );
        }
    }

    /**
     * checks to see if the class doesn't exist and the classname is good
     *
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     */
    public function classCheck( $data )
    {

        $ns = mb_ucfirst( Request::i()->dtdevplus_class_namespace );
        $class = mb_ucfirst( $data );
        $class = $ns ? '\\IPS\\' . $this->application->directory . '\\' . $ns . '\\' . $class : '\\IPS\\' . $this->application->directory . '\\' . $class;

        if ( $data !== 'Form' && \class_exists( $class ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_exists' );
        }

        if ( ReservedWords::check( $data ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_reserved' );
        }
    }

    /**
     * checks to see if the trait doesn't exist and the trait name is good!
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function traitClassCheck( $data )
    {

        $ns = mb_ucfirst( Request::i()->dtdevplus_class_namespace );
        $class = mb_ucfirst( $data );
        if ( $ns ) {
            $class = '\\IPS\\' . $this->application->directory . '\\' . $ns . '\\' . $class;
        }
        else {
            $class = '\\IPS\\' . $this->application->directory . '\\' . $class;
        }

        if ( trait_exists( $class ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_trait_exists' );
        }

        if ( ReservedWords::check( $data ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_reserved' );
        }
    }

    /**
     * checks to see if the interface doesn't exist and the name is good!
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function interfaceClassCheck( $data )
    {

        $ns = mb_ucfirst( Request::i()->dtdevplus_class_namespace );
        $class = mb_ucfirst( $data );
        if ( $ns ) {
            $class = "\\IPS\\" . $this->application->directory . "\\" . $ns . "\\" . $class;
        }
        else {
            $class = "\\IPS\\" . $this->application->directory . "\\" . $class;
        }

        if ( interface_exists( $class ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_interface_exists' );
        }

        if ( ReservedWords::check( $data ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_reserved' );
        }
    }

    /**
     * checks to see if the Class/Trait/Interface name isn't blank!
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function noBlankCheck( $data )
    {

        if ( !$data ) {
            throw new InvalidArgumentException( 'dtdevplus_class_no_blank' );
        }
    }

    /**
     * checks the parent class exist if one is provided
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function extendsCheck( $data )
    {

        if ( $data && !class_exists( $data, \true ) ) {
            throw new InvalidArgumentException( 'dtdevplus_class_extended_class_no_exist' );
        }
    }

    /**
     * Checks to make sure the interface files exist
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function implementsCheck( $data )
    {

        if ( is_array( $data ) && count( $data ) ) {
            foreach ( $data as $implement ) {

                if ( !interface_exists( $implement ) ) {
                    $lang = Member::loggedIn()->language()->addToStack( 'dtdevplus_class_implemented_no_interface', \false, [ 'sprintf' => $implement ] );
                    Member::loggedIn()->language()->parseOutputForDisplay( $lang );
                    throw new InvalidArgumentException( $lang );
                }
            }
        }
    }

    /**
     * checks to make sure the traits being used exists
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function traitsCheck( $data )
    {

        if ( \is_array( $data ) && \count( $data ) ) {
            foreach ( $data as $trait ) {
                if ( !trait_exists( $trait ) ) {
                    $lang = Member::loggedIn()->language()->addToStack( 'dtdevplus_class_no_trait', \false, [ 'sprintf' => [ $trait ] ] );
                    Member::loggedIn()->language()->parseOutputForDisplay( $lang );
                    throw new InvalidArgumentException( $lang );
                }
            }
        }
    }

    /**
     * checks to make sure the node exist for the content item class.
     *
     * @param $data
     *
     * @throws InvalidArgumentException
     *
     */
    public function itemNodeCheck( $data )
    {

        if ( $data ) {
            $class = "IPS\\{$this->application->directory}\\{$data}";
            if ( !class_exists( $class ) ) {
                throw new InvalidArgumentException( 'dtdevplus_class_node_item_missing' );
            }

            if ( ReservedWords::check( $data ) ) {
                throw new InvalidArgumentException( 'dtdevplus_class_reserved' );
            }
        }
    }

    /**
     * namespace element
     */
    protected function elNamespace()
    {

        $tabs = [
            'node',
            'item',
            'comment',
            'review',
        ];

        $elements = [
            'name'    => 'namespace',
            'prefix'  => "IPS\\{$this->application->directory}\\",
            'options' => [
                'placeholder'  => 'Namespace',
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findNamespace&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];

        if ( in_array( $this->type, $tabs ) ) {
            $elements[ 'tab' ] = 'general';
        }

        $this->elements[] = $elements;
    }

    /**
     * classname element
     */
    protected function elClassName()
    {

        if ( $this->type === 'interfacing' ) {
            $this->elements[] = [
                'name'     => 'interfaceName',
                'validate' => [ $this, 'interfaceClassCheck' ],
            ];
        }
        else if ( $this->type === 'traits' ) {
            $this->elements[] = [
                'name'     => 'traitName',
                'validate' => [ $this, 'traitClassCheck' ],
            ];
        }
        else {
            $this->elements[] = [
                'name'       => 'className',
                'required'   => \true,
                'options'    => [
                    'placeholder' => 'Class Name',
                ],
                'validation' => [ $this, 'classCheck' ],
                'prefix'     => '_',
            ];
        }
    }

    /**
     * abstract element
     */
    protected function elAbstract()
    {

        $this->elements[] = [
            'class'   => 'yn',
            'name'    => 'abstract',
            'default' => \false,
        ];
    }

    /**
     * extends element
     */
    protected function elExtends()
    {

        $this->elements[] = [
            'name'       => 'extends',
            'options'    => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
            'validation' => [ $this, 'extendsCheck' ],
        ];
    }

    /**
     * imports element
     *
     * @deprecated no longer gonna support non-imports
     */
    protected function elImports()
    {

        //        $this->elements[] = [
        //            'name'    => 'useImports',
        //            'class'   => 'yn',
        //            'default' => \true,
        //        ];
    }

    /**
     * database element
     */
    protected function elDatabase()
    {

        $this->elements[] = [
            'name'   => 'database',
            'prefix' => $this->application->directory . '_',
        ];
    }

    /**
     * prefix element
     */
    protected function elPrefix()
    {

        $this->elements[] = [
            'name'   => 'prefix',
            'suffix' => '_',
        ];
    }

    /**
     * scaffolding element
     */
    protected function elScaffolding()
    {

        $this->elements[] = [
            'class'   => 'yn',
            'name'    => 'scaffolding_create',
            'default' => \true,
            'options' => [
                'togglesOn' => [
                    'scaffolding_type',
                ],
            ],
        ];

        $sc[ 'db' ] = 'Database';

        if ( !in_array( $this->type, [ 'activerecord', 'review', 'comment' ] ) ) {
            $sc[ 'modules' ] = 'Module';
        }

        $this->elements[] = [
            'class'   => 'checkboxset',
            'name'    => 'scaffolding_type',
            'default' => array_keys( $sc ),
            'ops'     => [
                'options' => $sc,
            ],
        ];
    }

    /**
     * subnode element
     */
    protected function elSubNode()
    {

        $this->elements[] = [
            'name'    => 'subnode',
            'class'   => 'yn',
            'options' => [
                'togglesOn'    => [
                    'subnode_class',
                ],
                'togglesOff'   => [
                    'parentnode_class',
                ],
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];

        $this->elements[] = [
            'name'    => 'parentnode_class',
            'prefix'  => '\\IPS\\' . $this->application->directory . '\\',
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];

        $this->elements[] = [
            'name'    => 'subnode_class',
            'prefix'  => '\\IPS\\' . $this->application->directory . '\\',
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];

    }

    /**
     * Item Class element
     */
    protected function elItemClass()
    {

        $this->elements[] = [
            'name'    => 'item_class',
            'prefix'  => "IPS\\{$this->application->directory}\\",
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];
    }

    /**
     * interfaces tab for nodes
     */
    protected function elNodeInterfaces()
    {

        $interfacesNode = [
            Permissions::class => Permissions::class,
            Ratings::class     => Ratings::class,
        ];

        $this->elements[] = $e[] = [
            'class'   => 'checkboxset',
            'name'    => 'ips_implements',
            'label'   => 'interface_implements_node',
            'default' => array_keys( $interfacesNode ),
            'ops'     => [
                'options' => $interfacesNode,
            ],
            'tab'     => 'interfaces',
        ];
        $this->elInterfaces();
    }

    /**
     * interface  element
     */
    protected function elInterfaces()
    {

        $this->elements[] = [
            'class'      => 'stack',
            'name'       => 'implements',
            'validation' => [ $this, 'implementsCheck' ],
        ];
    }

    /**
     * traits tab for nodes
     */
    protected function elNodeTraits()
    {

        $traitsNode = [
            ClubContainer::class => ClubContainer::class,
            Colorize::class      => Colorize::class,
        ];

        $this->elements[] = [
            'class'   => 'checkboxset',
            'name'    => 'ips_traits',
            'label'   => 'ips_traits_node',
            'default' => array_keys( $traitsNode ),
            'ops'     => [
                'options' => $traitsNode,
            ],
            'tab'     => 'traits',
        ];

        $this->elTraits();

    }

    /**
     * traits element
     */
    protected function elTraits()
    {

        $this->elements[] = [
            'class'      => 'stack',
            'name'       => 'traits',
            'validation' => [ $this, 'traitsCheck' ],
        ];
    }

    /**
     * traits for items/comments/reviews
     */
    protected function elItemTraits()
    {

        $traitsItems = [
            Reactable::class  => Reactable::class,
            Reportable::class => Reportable::class,
        ];

        $this->elements[] = [
            'class'   => 'checkboxset',
            'name'    => 'ips_traits',
            'label'   => 'ips_traits_item',
            'default' => array_keys( $traitsItems ),
            'ops'     => [
                'options' => $traitsItems,
            ],
            'tab'     => 'traits',
        ];

        $this->elTraits();
    }

    /**
     * interfaces tab for items
     */
    protected function elItemInterfaces()
    {

        $interfacesItem = [
            EditHistory::class              => EditHistory::class,
            Embeddable::class               => Embeddable::class,
            Featurable::class               => Featurable::class,
            Followable::class               => Followable::class,
            FuturePublishing::class         => FuturePublishing::class,
            Hideable::class                 => Hideable::class,
            Lockable::class                 => Lockable::class,
            MetaData::class                 => MetaData::class,
            \IPS\Content\Permissions::class => \IPS\Content\Permissions::class,
            Pinnable::class                 => Pinnable::class,
            Polls::class                    => Polls::class,
            SplObserver::class              => SplObserver::class,
            \IPS\Content\Ratings::class     => \IPS\Content\Ratings::class,
            ReadMarkers::class              => ReadMarkers::class,
            Searchable::class               => Searchable::class,
            Shareable::class                => Shareable::class,
            Tags::class                     => Tags::class,
            Views::class                    => Views::class,
        ];

        $this->elements[] = [
            'class'   => 'checkboxset',
            'name'    => 'ips_implements',
            'label'   => 'interface_implements_item',
            'default' => array_keys( $interfacesItem ),
            'ops'     => [
                'options' => $interfacesItem,
            ],
            'tab'     => 'interfaces',
        ];

        $this->elInterfaces();
    }

    /**
     * Item's node class
     */
    protected function elItemNodeClass()
    {

        $this->elements[] = [
            'name'    => 'item_node_class',
            'prefix'  => "IPS\\{$this->application->directory}\\",
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];
    }

    /**
     * item's comment class
     */
    protected function elItemCommentClass()
    {

        $this->elements[] = [
            'name'    => 'comment_class',
            'prefix'  => "IPS\\{$this->application->directory}\\",
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];
    }

    /**
     * item's review class
     */
    protected function elItemReviewClass()
    {

        $this->elements[] = [
            'name'    => 'review_class',
            'prefix'  => "IPS\\{$this->application->directory}\\",
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];
    }

    /**
     * interfaces tab for comments/reviews
     */
    protected function elCommentInterfaces()
    {

        $interfacesComment = [
            Hideable::class    => Hideable::class,
            Embeddable::class  => Embeddable::class,
            Searchable::class  => Searchable::class,
            Lockable::class    => Lockable::class,
            EditHistory::class => EditHistory::class,
        ];
        $this->elements[] = [
            'class'   => 'checkboxset',
            'name'    => 'interface_implements_comment',
            'default' => array_keys( $interfacesComment ),
            'ops'     => [
                'options' => $interfacesComment,
            ],
            'tab'     => 'interfaces',
        ];

        $this->elInterfaces();
    }

    /**
     * Comment/review item's class
     */
    protected function elContentItemClass()
    {

        $this->elements[] = [
            'name'    => 'content_item_class',
            'prefix'  => "IPS\\{$this->application->directory}\\",
            'options' => [
                'autocomplete' => [
                    'source'               => 'app=toolbox&module=devcenter&controller=sources&do=findClass2&appKey=' . $this->application->directory,
                    //                    'resultItemTemplate'   => 'core.autocomplete.memberItem',
                    'minimized'            => \false,
                    'commaTrigger'         => \false,
                    'unique'               => \true,
                    'minAjaxLength'        => 3,
                    'disallowedCharacters' => [],
                    'maxItems'             => 1,
                ],
            ],
        ];
    }

    protected function elApiType()
    {

        $this->elements[] = [
            'name'    => 'apiType',
            'class'   => 'select',
            'options' => [
                'options' => [
                    's' => 'Standard',
                    'i' => 'Content/Item',
                    'c' => 'Comments',
                    'n' => 'Node',
                ],
            ],
        ];
    }
}

