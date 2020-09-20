<?php

/**
 * @brief       Build Class
 * @author      -storm_author-
 * @copyright   -storm_copyright-
 * @package     IPS Social Suite
 * @subpackage  Dev Toolbox: Base
 * @since       1.2.0
 * @version     -storm_version-
 */

namespace IPS\toolbox;

use Exception;
use IPS\Application;
use IPS\Application\BuilderIterator;
use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\Patterns\Singleton;
use IPS\Request;
use IPS\toolbox\Profiler\Debug;
use Phar;
use PharData;
use RuntimeException;

use function chmod;
use function explode;
use function is_dir;
use function mkdir;
use function sprintf;


\IPS\toolbox\Application::loadAutoLoader();

class _Build extends Singleton
{

    protected static $instance;

    /**
     * Undocumented function
     *
     * @return void
     */
    public function export()
    {
        if (!Application::appIsEnabled('toolbox') || !\IPS\IN_DEV) {
            throw new \InvalidArgumentException('toolbox not installed');
        }

        $app = Request::i()->appKey;
        $application = Application::load($app);
        $title = $application->_title;
        Member::loggedIn()->language()->parseOutputForDisplay($title);
        $newLong = $application->long_version;

        if (empty($application->version) !== true) {
            $newShort = $application->version;
        } else {
            $newShort = '1.0.0';
            $newLong = 10000;
        }

        $form = Form::create();
        $form->dummy('Previous Long Version', $newLong);
        $form->dummy('Previous Short Version', $newShort);
        $form->add('toolbox_increment', 'yn')->value(1)->toggles(
            ['toolbox_long_version', 'toolbox_short_version'],
            true
        );
        $form->add('toolbox_long_version', 'number')->label('Long Version')->required()->empty($newLong);
        $form->add('toolbox_short_version')->label('Short Version')->required()->empty($newShort);
        $form->add('toolbox_beta', 'yn')->toggles(['toolbox_beta_version']);
        $form->add('toolbox_beta_version', 'number')->required()->empty(1);

        $form->add('toolbox_skip_dir', 'stack')->label('Skip Directories')->description(
            'Folders to skip using slasher on.'
        )->empty(
            [
                '3rdparty',
                'vendor',
            ]
        );
        $form->add('toolbox_skip_files', 'stack')->label('Skip Files')->description('Files to skip using slasher on.');

        if ($values = $form->values()) {
            $long = $values['toolbox_long_version'];
            $short = $values['toolbox_short_version'];
            if (isset($values['toolbox_increment']) && $values['toolbox_increment']) {
                $exploded = explode('.', $short);
                $end = $exploded[2] ?? 0;
                $short = "{$exploded[0]}.{$exploded[1]}." . ((int)$end + 1);
                $long++;
            }

            if (isset($values['toolbox_beta']) && $values['toolbox_beta']) {
                $short .= ' Beta ' . $values['toolbox_beta_version'];
            }

            $application->long_version = $long;
            $application->version = $short;
            $application->save();
            unset(Store::i()->applications);
            $path = \IPS\ROOT_PATH . '/exports/' . $application->directory . '/' . $long . '/';

            try {
                Slasher::i()->start(
                    $application,
                    $values['toolbox_skip_files'] ?? [],
                    $values['toolbox_skip_dir'] ?? []
                );

                try {
                    $application->assignNewVersion($long, $short);
                    $application->build();
                    $application->save();
                    if (!is_dir($path)) {
                        if (!mkdir($path, \IPS\IPS_FOLDER_PERMISSION, true) && !is_dir($path)) {
                            throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                        }
                        chmod($path, \IPS\IPS_FOLDER_PERMISSION);
                    }
                    $pharPath = $path . $application->directory . ' - ' . $application->version . '.tar';
                    $download = new PharData($pharPath, 0, $application->directory . '.tar', Phar::TAR);
                    $download->buildFromIterator(new BuilderIterator($application));
                } catch (Exception $e) {
                    Debug::log($e, 'phar');
                }
            } catch (Exception $e) {
                Debug::log($e, 'phar');
            }

            unset(Store::i()->applications, $download);
            $url = Url::internal('app=core&module=applications&controller=applications');
            Output::i()->redirect($url, $application->_title . ' successfully built!');
        }

        Output::i()->title = 'Build ' . $application->_title;
        Output::i()->output = $form;
    }
}
