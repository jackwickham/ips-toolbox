<?php

/**
 * @brief       IPSDataStore Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox\Proxy
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Proxy\Helpers;

use IPS\Application;
use IPS\toolbox\Generator\Builders\ClassGenerator;
use function defined;
use function header;
use function method_exists;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class _Store implements HelpersAbstract
{

    /**
     * @inheritdoc
     */
    public function process( $class, ClassGenerator $classGenerator, &$classExtends )
    {

        $classDoc[] = [ 'prop' => 'acpBulletin', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'administrators', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'applications', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'bannedIpAddresses', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'cms_databases', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'cms_fieldids', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'emoticons', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'furl_configuration', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'groups', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'languages', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'maxAllowedPacket', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'moderators', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'modules', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'nexusPackagesWithReviews', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'profileSteps', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'rssFeeds', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'settings', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'storageConfigurations', 'hint' => 'array' ];
        $classDoc[] = [ 'prop' => 'themes', 'hint' => 'array' ];

        foreach ( $classDoc as $doc ) {
            $classGenerator->addPropertyTag( $doc[ 'prop' ], [ 'hint' => $doc[ 'hint' ] ] );
        }
        /* @var Application $app */
        foreach ( Application::appsWithExtension( 'toolbox', 'ProxyHelpers' ) as $app ) {
            $extensions = $app->extensions( 'toolbox', 'ProxyHelpers', \true );
            foreach ( $extensions as $extension ) {
                if ( method_exists( $extension, 'store' ) ) {
                    $extension->store( $classGenerator );
                }
            }
        }
    }
}
