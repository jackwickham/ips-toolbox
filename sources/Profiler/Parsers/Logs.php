<?php

/**
 * @brief       Logs Standard
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  toolbox
 * @since       -storm_since_version-
 * @version     -storm_version-
 */

namespace IPS\toolbox\Profiler\Parsers;

use IPS\cms\Theme;
use IPS\DateTime;
use IPS\Db;
use IPS\Http\Url;
use IPS\Log;
use IPS\Patterns\ActiveRecordIterator;
use IPS\Patterns\Singleton;
use IPS\Settings;
use function count;
use function defined;
use function header;
use function htmlentities;
use function nl2br;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * Class _Logs
 *
 * @package IPS\toolbox\Parsers
 * @mixin \IPS\toolbox\Parsers\Logs
 */
class _Logs extends Singleton
{

    /**
     * @brief   Singleton Instances
     * @note    This needs to be declared in any child class.
     */
    protected static $instance;

    /**
     * Builds the logs button
     *
     * @throws \UnexpectedValueException
     * @return string|null
     */
    public function build()
    {
        if ( !Settings::i()->dtprofiler_enabled_logs ) {
            return \null;
        }

        $sql = Db::i()->select( '*', 'core_log', \null, 'id DESC', Settings::i()->dtprofiler_logs_amount );
        $logs = new ActiveRecordIterator( $sql, Log::class );
        $list = [];

        /* @var \IPS\Log $log */
        foreach ( $logs as $log ) {
            $url = Url::internal( 'app=toolbox&module=bt&controller=bt', 'front' )->setQueryString( [
                'do' => 'log',
                'id' => $log->id,
            ] );
            $data = DateTime::ts( $log->time );
            $name = 'Date: ' . $data;
            if ( $log->category !== \null ) {
                $name .= '<br> Type: ' . $log->category;
            }

            if ( $log->url !== \null ) {
                $name .= '<br> URL: ' . $log->url;
            }

            $name .= '<br>' . nl2br( htmlentities( $log->message ) );
            $list[] = Theme::i()->getTemplate( 'generic', 'toolbox', 'front' )->anchor( $name, $url, \true );
        }

        return Theme::i()->getTemplate( 'generic', 'toolbox', 'front' )->button( 'Logs', 'logs', 'list of logs', $list, count( $list ), 'list', \true, \false );

    }
}
