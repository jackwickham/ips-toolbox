<?php

/**
 * @brief       ModuleBuilder Trait
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Shared;

\IPS\toolbox\Application::loadAutoLoader();

use Exception;
use IPS\Application;
use IPS\Application\Module;
use IPS\Db;
use IPS\Member;
use IPS\Node\Controller;
use IPS\toolbox\Generator\DTClassGenerator;
use IPS\toolbox\Generator\DTFileGenerator;
use IPS\toolbox\Profiler\Debug;
use OutOfRangeException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use UnderflowException;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;
use function array_replace_recursive;
use function count;
use function defined;
use function file_exists;
use function file_get_contents;
use function header;
use function in_array;
use function is_array;
use function is_file;
use function json_decode;
use function mb_strtolower;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * ModuleBuilder Trait
 *
 * @mixin \IPS\toolbox\Shared\LanguageBuilder
 */
trait ModuleBuilder
{
    protected $classGenerator;

    /**
     * @param Application $application
     * @param             $classname
     * @param             $namespace
     * @param             $type
     */
    protected function _buildModule( Application $application, $classname, $namespace, $type, $useImports = \false )
    {
        $type = mb_strtolower( $type );
        if ( !in_array( $type, [ 'node', 'item' ] ) ) {
            return;
        }

        $classLower = mb_strtolower( $classname );

        try {
            $lang = Member::loggedIn()->language()->get( '__app_' . $application->directory );
        } catch ( UnderflowException $e ) {
            $lang = $application->directory;
        }

        $this->_addToLangs( 'menutab__' . $application->directory, $lang, $application );
        $methods = [];
        $this->classGenerator = new DTClassGenerator();
        $this->classGenerator->setName( '_' . $classLower );
        if ( $type === 'node' ) {
            try {
                $doc = [
                    'tags' => [
                        [ 'name' => 'brief', 'description' => 'Node Class' ],
                        [ 'name' => 'var', 'description' => '\\' . $namespace . '\\' . $classname ],
                    ],
                ];

                $config = [
                    'name'   => 'nodeClass',
                    'value'  => new PropertyValueGenerator( '\\' . $namespace . '\\' . $classname . '::class', PropertyValueGenerator::TYPE_CONSTANT, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
                    'vis'    => 'protected',
                    'doc'    => $doc,
                    'static' => \false,
                ];

                $this->addModuleProperty( $config );

            } catch ( \Exception $e ) {
            }
            $extends = Controller::class;
            $location = 'admin';

        }
        else {
            try {
                $doc = [
                    'tags' => [
                        [ 'name' => 'brief', 'description' => 'ContentModel Class' ],
                        [ 'name' => 'var', 'description' => '\\' . $namespace . '\\' . $classname ],
                    ],
                ];

                $config = [
                    'name'   => 'contentModel',
                    'value'  => new PropertyValueGenerator( '\\' . $namespace . '\\' . $classname . '::class', PropertyValueGenerator::TYPE_CONSTANT, PropertyValueGenerator::OUTPUT_SINGLE_LINE ),
                    'vis'    => 'protected',
                    'doc'    => $doc,
                    'static' => \true,
                ];
                $this->addModuleProperty( $config );
            } catch ( \Exception $e ) {
            }
            $extends = \IPS\Content\Controller::class;
            $location = 'front';
        }

        $this->classGenerator->setExtendedClass( $extends );
        if ( $useImports ) {
            $this->classGenerator->addUse( $extends );
        }

        $ns = 'IPS\\' . $application->directory . '\\modules\\' . $location . '\\' . $classLower;
        $this->classGenerator->setNamespaceName( $ns );
        $modules = $this->_getModules( $application );
        $key = $classLower;

        try {
            $module = Module::get( $application->directory, $key, $location );
        } catch ( OutOfRangeException $e ) {
            $module = new Module;
            $module->application = $application->directory;
            $module->area = $location;
        }

        $module->key = $key;
        $module->protected = 0;
        $module->default_controller = '';
        $module->save();
        $modules[ $location ][ $module->key ] = [
            'default_controller' => $module->default_controller,
            'protected'          => $module->protected,
        ];

        $this->_addToLangs( 'menu__' . $application->directory . '_' . $module->key, $module->key, $application );
        $this->_writeModules( $modules, $application );
        $targetDir = \IPS\ROOT_PATH . "/applications/{$application->directory}/modules/{$location}/{$module->key}/";
        $fs = new Filesystem();

        try {
            if ( !$fs->exists( $targetDir ) ) {
                $fs->mkdir( $targetDir, \IPS\IPS_FOLDER_PERMISSION );
                $fs->chmod( $targetDir, \IPS\IPS_FOLDER_PERMISSION );
            }
        } catch ( Exception $e ) {
        }

        $restriction = \null;

        if ( $location === 'admin' ) {
            /* Create a restriction? */
            $restrictions = [];
            if ( is_file( \IPS\ROOT_PATH . "/applications/{$application->directory}/data/acprestrictions.json" ) ) {
                $file = \IPS\ROOT_PATH . '/applications/' . $application->directory . '/data/acprestrictions.json';
                $restrictions = [];
                if ( file_exists( $file ) ) {
                    $restrictions = json_decode( file_get_contents( $file ), \true );
                }
            }
            $restrictions[ $module->key ][ $classname ][ "{$classLower}_manage" ] = "{$classLower}_manage";

            try {
                Application::writeJson( \IPS\ROOT_PATH . "/applications/{$application->directory}/data/acprestrictions.json", $restrictions );
            } catch ( RuntimeException $e ) {
            }
            $restriction = "{$classLower}_manage";

            try {
                $methodDocBlock = new DocBlockGenerator( 'Get the database column which stores the club ID', \null, [ new ReturnTag( [ 'dataType' => 'string' ] ) ] );
                $restrict = $restriction ? '\IPS\Dispatcher::i()->checkAcpPermission( \'' . $restriction . '\' );' : \null;
                $methods[] = MethodGenerator::fromArray( [
                    'name'     => 'execute',
                    'body'     => "{$restrict}\n\nparent::execute();",
                    'docblock' => $methodDocBlock,
                    'static'   => \false,
                ] );
            } catch ( \Exception $e ) {
            }
        }

        //        try {

        if ( !empty( $methods ) ) {
            $this->classGenerator->addMethods( $methods );
        }
        try {
            $package = Member::loggedIn()->language()->get( "__app_{$application->directory}" );
        } catch ( UnderflowException $e ) {
            $package = $application->directory;
        }
        $headerBlock = DocBlockGenerator::fromArray( [
            'tags' => [
                [ 'name' => 'brief', 'description' => $classLower . ' Controller' ],
                [ 'name' => 'copyright', 'description' => '-storm_copyright-' ],
                [ 'name' => 'package', 'description' => 'IPS Social Suite' ],
                [ 'name' => 'subpackage', 'description' => $package ],
                [ 'name' => 'since', 'description' => '-storm_since_version-' ],
                [ 'name' => 'version', 'description' => '-storm_version-' ],
            ],
        ] );

        $mixin = '\\' . $ns . '\\' . $classLower;
        $docBlock = DocBlockGenerator::fromArray( [
            'shortDescription' => $classLower . ' Class',
            'longDescription'  => \null,
            'tags'             => [ [ 'name' => 'mixin', 'description' => $mixin ] ],
        ] );
        $this->classGenerator->setDocBlock( $docBlock );
        $content = new DTFileGenerator;
        $content->setDocBlock( $headerBlock );
        $content->setClass( $this->classGenerator );
        $content->setFilename( $targetDir . $classLower . '.php' );
        $content->write();
        //        }
        //        catch( \Exception $e){}

        $this->_addToLangs( 'menu__' . $application->directory . '_' . $module->key . '_' . $module->key, $module->key, $application );

        if ( $location === 'admin' ) {
            /* Add to the menu */
            $file = \IPS\ROOT_PATH . '/applications/' . $application->directory . '/data/acpmenu.json';
            $menu = [];
            if ( file_exists( $file ) ) {
                $menu = json_decode( file_get_contents( $file ), \true );
            }

            $menu[ $module->key ][ $classLower ] = [
                'tab'         => $application->directory,
                'controller'  => $classLower,
                'do'          => '',
                'restriction' => $restriction,
            ];

            try {
                Application::writeJson( \IPS\ROOT_PATH . "/applications/{$application->directory}/data/acpmenu.json", $menu );
            } catch ( RuntimeException $e ) {
            }
        }
    }

    protected function addModuleProperty( array $config = [] )
    {
        try {
            if ( !isset( $config[ 'name' ] ) ) {
                throw new InvalidArgumentException( 'array missing name or name value is null' );
            }
            $config[ 'defaultvalue' ] = $config[ 'value' ] ?? \null;
            if ( !empty( $config[ 'doc' ] ) ) {
                $config[ 'docblock' ] = DocBlockGenerator::fromArray( $config[ 'doc' ] );
                unset( $config[ 'doc' ] );
            }
            $config[ 'visibility' ] = $config[ 'vis' ] ?? \false;
            $config[ 'static' ] = $config[ 'static' ] ?? \false;
            $prop = PropertyGenerator::fromArray( $config );
            $this->classGenerator->addPropertyFromGenerator( $prop );
        } catch ( \Exception $e ) {
            Debug::add( 'addProperty', $e );
        }
    }

    /**
     * gets the exist modules for an application/location.
     *
     * @param Application $application
     *
     * @return array
     */
    protected function _getModules( Application $application ): array
    {
        $file = \IPS\ROOT_PATH . "/applications/{$application->directory}/data/modules.json";
        $json = [];
        if ( file_exists( $file ) ) {
            $json = json_decode( file_get_contents( $file ), \true );
        }

        $modules = [];
        $extra = [];
        $db = [];

        foreach ( Db::i()->select( '*', 'core_modules', [
            'sys_module_application=?',
            $application->directory,
        ] ) as $row ) {
            $db[] = $row;
            $extra[ $row[ 'sys_module_area' ] ][ $row[ 'sys_module_key' ] ] = [
                'default'            => $row[ 'sys_module_default' ],
                'id'                 => $row[ 'sys_module_id' ],
                'default_controller' => $row[ 'sys_module_default_controller' ],
                'protected'          => $row[ 'sys_module_protected' ],
            ];
        }

        if ( is_array( $json ) && count( $json ) ) {
            $modules = $json;
        }
        else {
            foreach ( $db as $row ) {
                $modules[ $row[ 'sys_module_area' ] ][ $row[ 'sys_module_key' ] ] = [
                    'default_controller' => $row[ 'sys_module_default_controller' ],
                    'protected'          => $row[ 'sys_module_protected' ],
                ];
            }
        }

        try {
            if ( !is_file( $file ) ) {
                Application::writeJson( $file, $modules );
            }

            /* We get the ID and default flag from the local DB to prevent devs syncing defaults */
            return array_replace_recursive( $modules, $extra );
        } catch ( Exception $e ) {
            return $modules;
        }
    }

    /**
     * writes the modules to the apps json file.
     *
     * @param array       $json
     * @param Application $application
     */
    protected function _writeModules( array $json, Application $application )
    {
        foreach ( $json as $location => $module ) {
            /* @var array $module */
            foreach ( $module as $name => $data ) {
                /* @var array $data */
                foreach ( $data as $k => $v ) {
                    if ( !in_array( $k, [ 'protected', 'default_controller' ] ) ) {
                        unset( $json[ $location ][ $name ][ $k ] );
                    }
                }
            }
        }

        try {
            Application::writeJson( \IPS\ROOT_PATH . "/applications/{$application->directory}/data/modules.json", $json );
        } catch ( RuntimeException $e ) {
        }
    }
}
