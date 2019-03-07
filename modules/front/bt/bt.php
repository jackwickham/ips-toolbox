<?php


namespace IPS\toolbox\modules\front\bt;

use Exception;
use IPS\Application;
use IPS\Data\Cache;
use IPS\Data\Store;
use IPS\DateTime;
use IPS\Db;
use IPS\Dispatcher\Controller;
use IPS\dtprofiler\Profiler;
use IPS\dtprofiler\Profiler\Debug;
use IPS\Log;
use IPS\Member;
use IPS\Output;
use IPS\Patterns\ActiveRecordIterator;
use IPS\Plugin;
use IPS\Request;
use IPS\Theme;
use Symfony\Component\Filesystem\Filesystem;
use function count;
use function defined;
use function header;
use function htmlentities;
use function ini_get;
use function is_array;
use function is_dir;
use function md5;
use function microtime;
use function mt_rand;
use function nl2br;
use function sleep;
use function str_replace;
use function time;

\IPS\toolbox\Application::loadAutoLoader();

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) ? $_SERVER[ 'SERVER_PROTOCOL' ] : 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * bt
 */
class _bt extends Controller
{

    /**
     * @inheritdoc
     */
    protected function manage()
    {
        $store = Store::i()->dtprofiler_bt;
        $hash = Request::i()->bt;
        $output = 'Nothing Found';
        if ( isset( $store[ $hash ] ) ) {
            $bt = str_replace( "\\\\", "\\", $store[ $hash ][ 'bt' ] );
            $output = '<code>' . $store[ $hash ][ 'query' ] . '</code><br><pre class="prettyprint lang-php">' . $bt . '</pre>';

        }

        Output::i()->output = "<div class='ipsPad'>{$output}</div>";

    }

    /**
     * shows data for the cache dialog
     */
    protected function cache()
    {
        $store = Store::i()->dtprofiler_bt_cache;
        $hash = Request::i()->bt;
        $output = 'Nothing Found';
        if ( isset( $store[ $hash ] ) ) {
            $bt = str_replace( "\\\\", "\\", $store[ $hash ][ 'bt' ] );
            $content = nl2br( htmlentities( $store[ $hash ][ 'content' ] ) );
            $output = '<code>' . $content . '</code><br><pre class="prettyprint lang-php">' . $bt . '</pre>';

        }

        Output::i()->output = "<div class='ipsPad'>{$output}</div>";

    }

    /**
     * shows data for the logs dialog
     */
    protected function log()
    {
        $id = Request::i()->id;
        $output = 'Nothing Found';
        try {
            $log = Log::load( $id );
            $data = DateTime::ts( $log->time );
            $name = 'Date: ' . $data;
            if ( $log->category !== \null ) {
                $name .= '<br> Type: ' . $log->category;
            }

            if ( $log->url !== \null ) {
                $name .= '<br> URL: ' . $log->url;
            }
            $msg = nl2br( htmlentities( $log->message ) );
            $output = $name . '<br>' . $msg . '<br><pre class="prettyprint lang-php">' . $log->backtrace . '</pre>';

        } catch ( Exception $e ) {
        }

        Output::i()->output = "<div class='ipsPad'>{$output}</div>";

    }

    /**
     * @throws Db\Exception
     * @throws \UnexpectedValueException
     */
    protected function debug()
    {
        $max = ( ini_get( 'max_execution_time' ) / 2 ) - 5;
        $time = time();
        $since = Request::i()->last ?: 0;
        while ( \true ) {
            $ct = time() - $time;
            if ( $ct >= $max ) {
                Output::i()->json( [ 'error' => 1 ] );
            }

            $query = Db::i()->select( '*', 'dtprofiler_debug', [
                'debug_ajax = ? AND debug_id > ? AND debug_viewed=?',
                1,
                $since,
                0,
            ], \null, \null, \null, \null, Db::SELECT_SQL_CALC_FOUND_ROWS );

            if ( $query->count( \true ) ) {

                $iterators = new ActiveRecordIterator( $query, Debug::class );

                $last = 0;

                /* @var \IPS\tooblox\Profiler\Debug $obj */
                foreach ( $iterators as $obj ) {
                    $list[] = $obj->body();
                    $last = $obj->id;
                }

                $return = [];
                if ( is_array( $list ) && count( $list ) ) {
                    $count = count( $list );
                    $return[ 'count' ] = $count;
                    $lists = '';
                    foreach ( $list as $l ) {
                        $lists .= Theme::i()->getTemplate( 'generic', 'dtprofiler', 'front' )->li( $l );
                    }
                    $return[ 'last' ] = $last;
                    $return[ 'items' ] = $lists;
                }

                if ( is_array( $return ) && count( $return ) ) {
                    Output::i()->json( $return );
                }
            }
            else {
                sleep( 1 );
                continue;
            }
        }
    }

    protected function phpinfo()
    {
        \phpinfo();
        exit;
    }

    protected function clearCaches()
    {
        $redirect = \base64_decode( Request::i()->data );
        /* Clear JS Maps first */
        Output::clearJsFiles();

        /* Reset theme maps to make sure bad data hasn't been cached by visits mid-setup */
        foreach ( Theme::themes() as $id => $set ) {
            /* Invalidate template disk cache */
            $set->cache_key = md5( microtime() . mt_rand( 0, 1000 ) );

            /* Update mappings */
            $set->css_map = [];
            $set->save();
        }

        Store::i()->clearAll();
        Cache::i()->clearAll();
        Member::clearCreateMenu();

        $path = \IPS\ROOT_PATH . '/hook_temp';

        if ( is_dir( $path ) ) {
            \IPS\tooblox\Application::loadAutoLoader();
            $fs = new Filesystem();
            $fs->remove( [ $path ] );
        }

        Output::i()->redirect( $redirect );
    }

    protected function thirdParty()
    {
        $enable = Request::i()->enable;
        $redirect = \base64_decode( Request::i()->data );
        $apps = Profiler::i()->apps();
        $plugins = Profiler::i()->plugins();

        /* Loop Apps */
        foreach ( $apps as $app ) {
            Db::i()->update( 'core_applications', [ 'app_enabled' => $enable ], [ 'app_id=?', $app->id ] );
        }

        /* Look Plugins */
        foreach ( $plugins as $plugin ) {
            Db::i()->update( 'core_plugins', [ 'plugin_enabled' => $enable ], [ 'plugin_id=?', $plugin->id ] );
        }

        if ( !empty( $apps ) ) {
            Application::postToggleEnable();
        }

        if ( !empty( $plugins ) ) {
            Plugin::postToggleEnable( \true );
        }

        /* Clear cache */
        Cache::i()->clearAll();
        Output::i()->redirect( $redirect );
    }

    protected function enableDisableApp()
    {
        $enabled = !Request::i()->enabled;
        $redirect = \base64_decode( Request::i()->data );
        $id = Request::i()->id;
        Db::i()->update( 'core_applications', [ 'app_enabled' => $enabled ], [ 'app_id=?', $id ] );
        Application::postToggleEnable();
        Cache::i()->clearAll();
        Output::i()->redirect( $redirect );
    }

    protected function enableDisablePlugin()
    {
        $enabled = !Request::i()->enabled;
        $redirect = \base64_decode( Request::i()->data );
        $id = Request::i()->id;
        Db::i()->update( 'core_plugins', [ 'plugin_enabled' => $enabled ], [ 'plugin_id=?', $id ] );
        Application::postToggleEnable();
        Cache::i()->clearAll();
        Output::i()->redirect( $redirect );
    }

    protected function gitInfo()
    {
        $info = [];
        Profiler::i()->getLastCommitId( $info );
        Profiler::i()->hasChanges( $info );
        //        print_r($info);exit;
        $html = '';
        if ( !empty( $info ) ) {
            $html = Theme::i()->getTemplate( 'bar', 'dtprofiler', 'front' )->git( $info );
        }

        Output::i()->json( [ 'html' => $html ] );
    }
    //    protected function checkout(){
    //        $app = Request::i()->dir;
    //        $branch = Request::i()->branch;
    //        $redirect = \base64_decode(Request::i()->data);
    //        $path = \IPS\ROOT_PATH.'/applications/'.$app.'/.git/';
    //        if( is_dir( $path ) && function_exists( 'exec' ) ){
    ////            try {
    //                $git = new GitRepository($path);
    //                $git->checkout( $branch );
    ////            } catch (GitException $e) {
    ////            }
    //        }
    //        Output::i()->redirect($redirect);
    //    }

    //    protected function commitPush()
    //    {
    //        $app = Request::i()->dir;
    //        $branch = Request::i()->branch;
    //        $redirect = \base64_decode(Request::i()->data);
    //        $gitReposPath = \IPS\ROOT_PATH . '/git.php';
    //        $appRepos = [];
    //        if (file_exists($gitReposPath)) {
    //            require $gitReposPath;
    //            if( isset( $appRepos[$app] ) ){
    //            $path = \IPS\ROOT_PATH . '/applications/' . $app . '/.git/';
    //            if (is_dir($path) && function_exists('exec')) {
    //                $e[] = [
    //                    'class' => 'textarea',
    //                    'name' => 'dtprofiler_commit_message'
    //                ];
    //
    //                $e[] = [
    //                    'class' => 'yn',
    //                    'name' => 'dtprofiler_push'
    //                ];
    //
    //                $forms = Forms::execute(['elements' => $e, 'submitLang' => 'dtprofiler_commit_button']);
    //
    //                if ($values = $forms->values()) {
    //                    $msg = $values[ 'dtprofiler_commit_message' ];
    //                    //                try {
    //                    $git = new GitRepository($path);
    //                    $git->execute( [
    //                        'config',
    //                        'user.name',
    //                        Member::loggedIn()->name
    //                    ]);
    //
    //                    $git->execute( [
    //                        'config',
    //                        'user.email',
    //                        Member::loggedIn()->email
    //                    ]);
    ////                    $git->addAllChanges();
    //                    $git->commit($msg, '-a');
    //                    //git config --get remote.origin.url
    //
    //                    if ($values[ 'dtprofiler_push' ]) {
    //                        foreach( $appRepos[$app] as $repo ) {
    //                            $git->push(null, ['--repo' => $repo]);
    //                        }
    //                    }
    //                    //                } catch (GitException $e) {
    //                    //                }
    //                    Output::i()->redirect($redirect);
    //
    //                }
    //            }
    //                Output::i()->output = $forms;
    //            }
    //        }
    //        else{
    //            Output::i()->redirect($redirect);
    //        }
    //    }
}
