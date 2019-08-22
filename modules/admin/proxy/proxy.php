<?php

namespace IPS\toolbox\modules\admin\proxy;

use IPS\Dispatcher\Controller;
use IPS\Helpers\MultipleRedirect;
use IPS\Http\Url;
use IPS\Member;
use IPS\Output;
use IPS\toolbox\Proxy\Proxyclass;
use Symfony\Component\Filesystem\Filesystem;
use function count;
use function defined;
use function header;
use function in_array;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * proxy
 */
class _proxy extends Controller
{

    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {

        if ( \IPS\NO_WRITES === \true ) {
            Output::i()->error( 'Proxy generator can not be used atm, NO_WRITES is enabled in the constants.php.', '100foo' );
        }
        \IPS\Dispatcher::i()->checkAcpPermission( 'proxy_manage' );

        parent::execute();
    }

    /**
     * ...
     *
     * @return    void
     */
    protected function manage()
    {

        Output::i()->title = Member::loggedIn()->language()->addToStack( 'dtproxy_proxyclass_title' );
        Output::i()->output = new MultipleRedirect( $this->url, function ( $data )
        {

            if ( !$data || !count( $data ) ) {
                $data = [];
                $data[ 'total' ] = Proxyclass::i()->dirIterator();
                $data[ 'current' ] = 0;
                $data[ 'progress' ] = 0;
                $data[ 'firstRun' ] = 1;
            }

            $run = Proxyclass::i()->run( $data );

            if ( $run === \null ) {
                return \null;
            }
            else {
                /**
                 * @todo hacky af, but what is a boy to do? :P
                 */
                if ( in_array( 'current', $run ) ) {
                    $progress = isset( $run[ 'progress' ] ) ? $run[ 'progress' ] : 0;

                    if ( $run[ 'total' ] && $run[ 'current' ] ) {
                        $progress = ( $run[ 'current' ] / $run[ 'total' ] ) * 100;
                    }

                    $language = Member::loggedIn()->language()->addToStack( 'dtproxy_progress', \false, [
                        'sprintf' => [
                            $run[ 'current' ],
                            $run[ 'total' ],
                        ],
                    ] );

                    return [
                        [
                            'total'    => $run[ 'total' ],
                            'current'  => $run[ 'current' ],
                            'progress' => $run[ 'progress' ],
                        ],
                        $language,
                        $progress,
                    ];
                }
                else {
                    $progress = ( $run[ 'complete' ] / $run[ 'tot' ] ) * 100;
                    $language = Member::loggedIn()->language()->addToStack( 'dtproxy_progress_extra', \false, [
                        'sprintf' => [
                            $run[ 'lastStep' ],
                            $run[ 'complete' ],
                            $run[ 'tot' ],
                        ],
                    ] );

                    return [
                        [ 'complete' => $run[ 'complete' ], 'step' => $run[ 'step' ] ],
                        $language,
                        $progress,
                    ];
                }
            }
        }, function ()
        {

            if ( defined( '\BYPASSPROXYDT' ) && \BYPASSPROXYDT === \true ) {
                \IPS\toolbox\Application::loadAutoLoader();
                $fs = new Filesystem();
                $fs->mirror( \IPS\ROOT_PATH . '/dtProxy2', \IPS\ROOT_PATH . '/dtProxy' );
            }
            /* And redirect back to the overview screen */
            $url = Url::internal( 'app=core&module=overview&controller=dashboard' );
            Output::i()->redirect( $url, 'dtproxy_done' );
        } );
    }
}
