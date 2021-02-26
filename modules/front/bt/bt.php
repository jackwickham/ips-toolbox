<?php

/**
 * @brief       Bt Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox
 * @since       4.0.0
 * @version     -storm_version-
 */


namespace IPS\toolbox\modules\front\bt;

use Exception;
use IPS\Data\Store;
use IPS\DateTime;
use IPS\Db;
use IPS\Dispatcher\Controller;
use IPS\Helpers\MultipleRedirect;
use IPS\Http\Url;
use IPS\Log;
use IPS\Member;
use IPS\Output;
use IPS\Plugin;
use IPS\Request;
use IPS\toolbox\Application;
use IPS\toolbox\Profiler\Debug;
use IPS\toolbox\Proxy\Generator\Proxy;
use IPS\toolbox\Proxy\Proxyclass;
use IPS\toolbox\Shared\Lorem;
use Symfony\Component\Filesystem\Filesystem;
use UnexpectedValueException;
use IPS\Theme;

use function base64_decode;
use function count;
use function defined;
use function header;
use function htmlentities;
use function in_array;
use function ini_get;
use function is_array;
use function is_dir;
use function md5;
use function microtime;
use function mt_rand;
use function nl2br;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function phpinfo;
use function preg_replace;
use function sleep;
use function str_replace;
use function time;

use const IPS\ROOT_PATH;


Application::loadAutoLoader();

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0') . ' 403 Forbidden');
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
    protected function manage(): void
    {
        $store = Store::i()->dtprofiler_bt;
        $hash = Request::i()->bt;
        $output = 'Nothing Found';
        if (isset($store[$hash])) {
            $bt = str_replace("\\\\", "\\", $store[$hash]['bt']);
            $output = '<code>' . $store[$hash]['query'] . '</code><br><pre class="prettyprint lang-php">' . $bt . '</pre>';
        }

        Output::i()->output = "<div class='ipsPad'>{$output}</div>";
    }

    /**
     * shows data for the cache dialog
     */
    protected function cache(): void
    {
        $store = Store::i()->dtprofiler_bt_cache;
        $hash = Request::i()->bt;
        $output = 'Nothing Found';
        if (isset($store[$hash])) {
            $bt = str_replace("\\\\", "\\", $store[$hash]['bt']);
            $content = nl2br(htmlentities($store[$hash]['content']));
            $output = '<code>' . $content . '</code><br><pre class="prettyprint lang-php">' . $bt . '</pre>';
        }

        Output::i()->output = "<div class='ipsPad'>{$output}</div>";
    }

    /**
     * shows data for the logs dialog
     */
    protected function log(): void
    {
        $id = Request::i()->id;
        $output = 'Nothing Found';
        try {
            $log = Log::load($id);
            $data = DateTime::ts($log->time);
            $name = 'Date: ' . $data;
            if ($log->category !== null) {
                $name .= '<br> Type: ' . $log->category;
            }

            if ($log->url !== null) {
                $name .= '<br> URL: ' . $log->url;
            }
            $msg = nl2br(htmlentities($log->message));
            $output = $name . '<br>' . $msg . '<br><pre class="prettyprint lang-php">' . $log->backtrace . '</pre>';
        } catch (Exception $e) {
        }

        Output::i()->output = "<div class='ipsPad'>{$output}</div>";
    }

    /**
     * @throws Db\Exception
     * @throws UnexpectedValueException
     */
    protected function debug(): void
    {
        $max = (ini_get('max_execution_time') / 2) - 5;
        $time = time();
        $since = Request::i()->last ?: 0;
        while (true) {
            $ct = time() - $time;
            if ($ct >= $max) {
                Output::i()->json(['error' => 1]);
            }

            $config = [
                'where' => [
                    'debug_id > ? AND debug_viewed = ?',
                    $since,
                    0,
                ]
            ];
            $debug = Debug::all($config, true);
            if ($debug !== 0) {
                $debug = Debug::all($config);
                $last = 0;
                $list = [];
                /* @var Debug $obj */
                foreach ($debug as $obj) {
                    $list[] = $obj->body();
                    $last = $obj->id;
                }

                $return = [];
                if (empty($list) !== true) {
                    $count = count($list);
                    $return['count'] = $count;
                    $lists = '';
                    foreach ($list as $l) {
                        $lists .= Theme::i()->getTemplate('generic', 'toolbox', 'front')->li($l);
                    }
                    $return['last'] = $last;
                    $return['items'] = $lists;
                }

                if (is_array($return) && count($return)) {
                    Output::i()->json($return);
                }
            } else {
                sleep(1);
                continue;
            }
        }
    }

    protected function phpinfo(): void
    {
        ob_start();
        phpinfo();
        $content = ob_get_clean();
        ob_end_clean();
        $content = preg_replace('#<head>(?:.|\n|\r)+?</head>#miu', '', $content);
        Output::i()->title = 'phpinfo()';
        Output::i()->output = Theme::i()->getTemplate('bt', 'toolbox', 'front')->phpinfo($content);
    }

    protected function clearCaches(): void
    {
        $redirect = base64_decode(Request::i()->data);
        $this->_clearCache();
        Output::i()->redirect($redirect);
    }

    protected function _clearCache()
    {
        $path = ROOT_PATH . '/hook_temp';

        if (is_dir($path)) {
            Application::loadAutoLoader();
            $fs = new Filesystem();
            $fs->remove([$path]);
        }
        /* Don't clear CSS/JS when we click "check again" or the page will be broken - it's unnecessary anyways */
        if (!isset(Request::i()->checkAgain)) {
            /* Clear JS Maps first */
            Output::clearJsFiles();

            /* Reset theme maps to make sure bad data hasn't been cached by visits mid-setup */
            \IPS\Theme::deleteCompiledCss();
            \IPS\Theme::deleteCompiledResources();

            foreach (\IPS\Theme::themes() as $id => $set) {
                /* Invalidate template disk cache */
                $set->cache_key = md5(microtime() . mt_rand(0, 1000));
                $set->save();
            }
        }

        \IPS\Data\Store::i()->clearAll();
        \IPS\Data\Cache::i()->clearAll();
        \IPS\Output\Cache::i()->clearAll();

        Member::clearCreateMenu();
    }

    protected function thirdParty(): void
    {
        $enable = (int)Request::i()->enabled;
        $redirect = base64_decode(Request::i()->data);
        $apps = Profiler::i()->apps();
        $plugins = Profiler::i()->plugins();

        /* Loop Apps */
        foreach ($apps as $app) {
            Db::i()->update('core_applications', ['app_enabled' => $enable], ['app_id=?', $app->id]);
        }

        /* Look Plugins */
        foreach ($plugins as $plugin) {
            Db::i()->update('core_plugins', ['plugin_enabled' => $enable], ['plugin_id=?', $plugin->id]);
        }

        if (!empty($apps)) {
            Application::postToggleEnable();
        }

        if (!empty($plugins)) {
            Plugin::postToggleEnable(true);
        }

        /* Clear cache */
        Cache::i()->clearAll();
        Plugin\Hook::writeDataFile();
        Output::i()->redirect($redirect);
    }

    protected function enableDisableApp(): void
    {
        $enabled = (int)Request::i()->enabled;
        if ($enabled === 1) {
            $enabled = 0;
        } else {
            $enabled = 1;
        }
        $redirect = base64_decode(Request::i()->data);
        $id = (int)Request::i()->id;
        $data = Db::i()->select('*', 'core_applications', ['app_id=?', $id])->first();
        /** @var Application $app */
        $app = \IPS\Application::constructFromData($data);
        $app->enabled = $enabled;
        $app->save();
//        Db::i()->update('core_applications', ['app_enabled' => $enabled], ['app_id=?', $id]);
        Application::postToggleEnable();
        $this->_clearCache();

        Output::i()->redirect($redirect);
    }

    protected function enableDisablePlugin(): void
    {
        $enabled = !Request::i()->enabled;
        $redirect = base64_decode(Request::i()->data);
        $id = Request::i()->id;
        Db::i()->update('core_plugins', ['plugin_enabled' => $enabled], ['plugin_id=?', $id]);
        Application::postToggleEnable();
        Cache::i()->clearAll();
        Output::i()->redirect($redirect);
    }

    protected function gitInfo(): void
    {
        $info = [];
        Profiler::i()->getLastCommitId($info);
        Profiler::i()->hasChanges($info);
        $html = '';
        if (!empty($info)) {
            $html = Theme::i()->getTemplate('bar', 'toolbox', 'front')->git($info);
        }

        Output::i()->json(['html' => $html]);
    }

    protected function gitCheckout()
    {
    }

    protected function lorem(): void
    {
        if (Session::i()->userAgent->browser === 'Chrome') {
            $form = Form::create()->formPrefix('toolbox_lorem_');

            $form->add('amount', 'number')->value(5)->options(['min' => 1]);
            $form->add('type', 'select')->options(
                [
                    'options' => [
                        0 => 'Select type',
                        1 => 'Words',
                        2 => 'Sentences',
                        3 => 'Paragraphs',
                    ],
                ]
            )->required();

            if ($values = $form->values()) {
                $return = '';
                $amount = $values['amount'];
                switch ($values['type']) {
                    case 1:
                        $return = Lorem::i()->words($amount);
                        break;
                    case 2:
                        $return = Lorem::i()->sentences($amount);
                        break;
                    case 3:
                        $return = Lorem::i()->paragraphs($amount);
                        break;
                }

                Output::i()->json(['text' => $return, 'type' => 'toolboxClipBoard']);
            }
            Output::i()->output = $form->dialogForm();
        } else {
            Output::i()->output = '<div class="ipsPad">' . nl2br(Lorem::i()->paragraphs(8)) . '</div>';
        }
    }

    protected function bitwiseValues()
    {
        $start = 1;
        $values = [1];
        $html = '<div class="ipsPad ipsClearfix">';
        $html .= '<div class="ipsPad ipsPos_left">1 => 1</div>';
        for ($i = 2; $i <= 45; $i++) {
            $start *= 2;
            $html .= '<div class="ipsPad ipsPos_left">' . $i . ' => ' . $start . '</div>';
        }
        $html .= '</div>';
        Output::i()->output = $html;
    }

    protected function clearAjax()
    {
        Db::i()->update('toolbox_debug', ['debug_viewed' => 1]);
    }

    protected function proxy()
    {
        if (\IPS\NO_WRITES === true) {
            Output::i()->error(
                'Proxy generator can not be used atm, NO_WRITES is enabled in the constants.php.',
                '100foo'
            );
        }
        Proxyclass::i()->dirIterator();
        Proxyclass::i()->buildHooks();
        $iterator = Store::i()->dtproxy_proxy_files;
        foreach ($iterator as $key => $file) {
            Proxyclass::i()->build($file);
        }
        unset(Store::i()->dtproxy_proxy_files);
        Proxy::i()->buildConstants();
        $step = 1;
        do {
            $step = Proxyclass::i()->makeToolboxMeta($step);
        }while($step !== null);
        Proxy::i()->generateSettings();
        Proxyclass::i()->buildCss();
        unset(Store::i()->dtproxy_proxy_files, Store::i()->dtproxy_templates);
        Output::i()->output = '';
    }
//    protected function adminer()
//    {
//        $url = Url::baseUrl() . '/applications/toolbox/sources/Profiler/Adminer/db.php';
//        Output::i(
//        )->output = '<iframe id="toolboxAdminer"  width="100%" height="600px" marginheight="0" frameborder="0" src="' . $url . '"></iframe>';
//    }

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
