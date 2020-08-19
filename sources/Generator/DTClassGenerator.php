<?php

/**
 * @brief       DTClassGenerator Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.2.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\Generator;
use function array_diff;

\IPS\toolbox\Application::loadAutoLoader();

use ReflectionClassConstant;
use ReflectionException;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\ClassReflection;
use function defined;
use function header;
use function preg_replace;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _DTClassGenerator
 *
 * @package IPS\toolbox\DevCenter\Sources\Generator
 * @mixin \IPS\toolbox\DevCenter\Sources\Generator\DTClassGenerator
 */
class _DTClassGenerator extends ClassGenerator
{
    public static function fromReflection( ClassReflection $classReflection )
    {
        $cg = new static( $classReflection->getName() );

        $cg->setSourceContent( $cg->getSourceContent() );
        $cg->setSourceDirty( \false );

        if ( $classReflection->getDocComment() !== '' ) {
            $cg->setDocBlock( DocBlockGenerator::fromReflection( $classReflection->getDocBlock() ) );
        }

        $cg->setAbstract( $classReflection->isAbstract() );

        // set the namespace
        if ( $classReflection->inNamespace() ) {
            $cg->setNamespaceName( $classReflection->getNamespaceName() );
        }

        /* @var \Zend\Code\Reflection\ClassReflection $parentClass */
        $parentClass = $classReflection->getParentClass();
        $interfaces = $classReflection->getInterfaces();

        if ( $parentClass ) {
            $cg->addUse( $parentClass->getName() );
            $cg->setExtendedClass( $parentClass->getName() );

            $interfaces = array_diff( $interfaces, $parentClass->getInterfaces() );
        }

        $interfaceNames = [];
        foreach ( $interfaces as $interface ) {
            $cg->addUse( $interface );
            /* @var \Zend\Code\Reflection\ClassReflection $interface */
            $interfaceNames[] = $interface->getName();
        }

        $cg->setImplementedInterfaces( $interfaceNames );

        $properties = [];

        foreach ( $classReflection->getProperties() as $reflectionProperty ) {
            if ( $reflectionProperty->getDeclaringClass()->getName() === $classReflection->getName() ) {
                $properties[] = PropertyGenerator::fromReflection( $reflectionProperty );
            }
        }

        $cg->addProperties( $properties );

        $constants = [];

        foreach ( $classReflection->getConstants() as $name => $value ) {
            try {
                $cc = new ReflectionClassConstant( $classReflection->getName(), $name );

                if ( $cc->getDeclaringClass()->getName() === $classReflection->getName() ) {

                    $constants[] = [
                        'name'  => $name,
                        'value' => $value,
                    ];
                }
            } catch ( ReflectionException $e ) {
            }
        }

        $cg->addConstants( $constants );

        $methods = [];

        foreach ( $classReflection->getMethods() as $reflectionMethod ) {
            $className = $cg->getNamespaceName() ? $cg->getNamespaceName() . '\\' . $cg->getName() : $cg->getName();

            if ( $reflectionMethod->getDeclaringClass()->getName() === $className ) {
                $methods[] = MethodGenerator::fromReflection( $reflectionMethod );
            }
        }

        $cg->addMethods( $methods );

        return $cg;
    }

    public function generate()
    {
        $this->addUse( 'function defined' );
        $this->addUse( 'function header' );
        $parent = parent::generate();
        $addIn = <<<'eof'
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}
eof;

        $parent = preg_replace( '/namespace(.+?)([^\n]+)/', 'namespace $2' . self::LINE_FEED . self::LINE_FEED . $addIn, $parent );

        return $parent;
    }

}
