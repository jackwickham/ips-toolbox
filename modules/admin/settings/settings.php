<?php

/**
 * @brief       Settings Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.1.0
 * @version     -storm_version-
 */

namespace IPS\toolbox\modules\admin\settings;

use Exception;
use Generator\Tokenizers\StandardTokenizer;
use IPS\Application;
use IPS\Dispatcher;
use IPS\Dispatcher\Controller;
use IPS\Helpers\Form;
use IPS\IPS;
use IPS\Output;
use IPS\Request;
use IPS\Settings;
use IPS\toolbox\GitHooks;
use IPS\toolbox\Profiler\Debug;
use RuntimeException;
use function defined;
use function header;
use const IPS\NO_WRITES;
use const IPS\ROOT_PATH;

\IPS\toolbox\Application::loadAutoLoader();

/* To prevent PHP errors (extending class does not exist) revealing path */

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * settings
 */
class _settings extends Controller
{

    /**
     * Execute
     *
     * @return    void
     * @throws RuntimeException
     */
    public function execute()
    {

        \IPS\toolbox\Application::loadAutoLoader();

        Dispatcher\Admin::i()->checkAcpPermission( 'settings_manage' );
        parent::execute();
    }

    /**
     * ...
     *
     * @return    void
     */
    protected function manage()
    {

        if ( NO_WRITES === \false ) {
            Output::i()->sidebar[ 'actions' ][ 'init' ] = [
                'icon'  => 'plus',
                'title' => 'Patch init.php',
                'link'  => Request::i()->url()->setQueryString( [ 'do' => 'patchInit' ] ),

            ];

            if ( property_exists( IPS::class, 'beenPatched' ) && IPS::$beenPatched === true ) {
                Output::i()->sidebar[ 'actions' ][ 'writeSpecialHooks' ] = [
                    'icon'  => '',
                    'title' => 'Add Special Hooks',
                    'link'  => Request::i()->url()->setQueryString( [ 'do' => 'writeSpecialHooks' ] ),

                ];

                Output::i()->sidebar[ 'actions' ][ 'removeSpecialHooks' ] = [
                    'icon'  => '',
                    'title' => 'Remove Special Hooks',
                    'link'  => Request::i()->url()->setQueryString( [ 'do' => 'removeSpecialHooks' ] ),

                ];
            }
            Output::i()->sidebar[ 'actions' ][ 'helpers' ] = [
                'icon'  => 'plus',
                'title' => 'Patch Helpers',
                'link'  => Request::i()->url()->setQueryString( [ 'do' => 'patchHelpers' ] ),

            ];
        }

        $form = \IPS\toolbox\Forms\Form::create()->object( Settings::i() );

        $form->element( 'toolbox_debug_templates', 'yn' )->tab( 'toolbox' );
        /* @var \IPS\toolbox\extensions\toolbox\Settings\settings $extension */
        foreach ( Application::allExtensions( 'toolbox', 'settings' ) as $extension ) {
            $extension->elements( $form );
        }

        /**
         * @var Form $form
         */
        if ( $values = $form->values() ) {
            foreach ( Application::appsWithExtension( 'toolbox', 'settings' ) as $app ) {
                $extensions = $app->extensions( 'toolbox', 'settings', \true );
                /* @var \IPS\toolbox\extensions\toolbox\settings\settings $extension */
                foreach ( $extensions as $extension ) {
                    $extension->formatValues( $values );
                }
            }
            $form->saveAsSettings( $values );
            Output::i()->redirect( $this->url->setQueryString( [ 'tab' => '' ] ), 'foo' );
        }

        Output::i()->title = 'Settings';
        Output::i()->output = $form;

    }

    protected function writeSpecialHooks()
    {

        $apps = Application::appsWithExtension( 'toolbox', 'SpecialHooks' );

        ( new GitHooks( $apps ) )->writeSpecialHooks();

        Output::i()->redirect( $this->url->setQueryString( [ 'tab' => '' ] ), 'SpecialHooks Created' );

    }

    protected function removeSpecialHooks()
    {

        $apps = Application::appsWithExtension( 'toolbox', 'SpecialHooks' );

        ( new GitHooks( $apps ) )->removeSpecialHooks();

        Output::i()->redirect( $this->url->setQueryString( [ 'tab' => '' ] ), 'SpecialHooks Removed' );

    }

    protected function patchHelpers()
    {

        if ( NO_WRITES === \false ) {

            try {
                $tokenizer = new StandardTokenizer( ROOT_PATH . '/init.php' );
                $helpers = '__DIR__ . \'/applications/toolbox/sources/Debug/Helpers.php\'';
                if ( $tokenizer->hasBackup() === false ) {
                    $tokenizer->backup();
                }
                $tokenizer->addRequire( $helpers, true, false );
                $tokenizer->write();
            } catch ( Exception $e ) {
                Debug::log( $e );
            }
        }

        Output::i()->redirect( $this->url, 'init.php patched with Debug Helpers' );
    }

    protected function patchInit()
    {

        if ( NO_WRITES === \false ) {

            $before = <<<'eof'

        $realClass = "_{$finalClass}";
        if (isset(self::$hooks[ "\\{$namespace}\\{$finalClass}" ]) AND \IPS\RECOVERY_MODE === false) {
            $path = ROOT_PATH . '/hook_temp/';
            if (!\is_dir($path)) {
                \mkdir($path, 0777, true);
            }

            $vendor = ROOT_PATH.'/applications/toolbox/sources/vendor/autoload.php';
            require $vendor;

            foreach (self::$hooks[ "\\{$namespace}\\{$finalClass}" ] as $id => $data) {
                $mtime = filemtime( ROOT_PATH . '/' . $data[ 'file' ] );
                $name = \str_replace(["\\", '/'], '_', $namespace . $realClass . $finalClass . $data[ 'file' ]);
                $filename = $name.'_' . $mtime . '.php';
                
                if (!file_exists( $path.$filename) && \file_exists(ROOT_PATH . '/' . $data[ 'file' ])) {
                    $fs = new \Symfony\Component\Filesystem\Filesystem();
                    $finder = new \Symfony\Component\Finder\Finder();
                    $finder->in( $path )->files()->name($name.'*.php');

                    foreach( $finder as $f ){
                        $fs->remove($f->getRealPath());
                    }
                    
                    $content = file_get_contents(ROOT_PATH . '/' . $data[ 'file' ]);
                    $content = preg_replace('#\b(?<![\'|"])_HOOK_CLASS_\b#', $realClass, $content);
                    $content = preg_replace( '#\b(?<![\'|"])_HOOK_CLASS_'.$data['class'].'\b#', $realClass, $content);
                    $contents = "namespace {$namespace}; " . $content;
                    if (!\file_exists($path . $filename)) {
                        \file_put_contents($path . $filename, "<?php\n\n" . $contents);
                    }
                }

                require_once $path . $filename;
                $realClass = $data[ 'class' ];
            }
        }
        
        $reflection = new \ReflectionClass("{$namespace}\\_{$finalClass}");
        if (eval("namespace {$namespace}; " . $extraCode . ($reflection->isAbstract() ? 'abstract' : '') . " class {$finalClass} extends {$realClass} {}") === false) {
            trigger_error("There was an error initiating the class {$namespace}\\{$finalClass}.", E_USER_ERROR);
        }
    
eof;
            try {
                $tokenizer = new StandardTokenizer( ROOT_PATH . '/init.php' );
                $tokenizer->addPath( ROOT_PATH );
                $tokenizer->addFileName( 'init' );
                $tokenizer->replaceMethod( 'monkeyPatch', $before );
                $tokenizer->addProperty( 'beenPatched', 'true', [ 'static' => true ] );
                if ( $tokenizer->hasBackup() === false ) {
                    $tokenizer->backup();
                }

                $tokenizer->save();
            } catch ( Exception $e ) {
                Debug::log( $e );
            }
        }

        Output::i()->redirect( $this->url, 'init.php patched' );
    }
}
