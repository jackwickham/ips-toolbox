<?php


namespace IPS\toolbox\modules\admin\settings;
use function header;

/* To prevent PHP errors (extending class does not exist) revealing path */

use IPS\Dispatcher\Controller;
use IPS\Output;
use IPS\toolbox\Build\Cons;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

/**
 * cons
 */
class _cons extends Controller
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ( \IPS\NO_WRITES === \true ) {
            Output::i()->error( 'Altering constants.php with NO_WRITES enabled, isn\'t allowed. please disable and trying again', '100foo' );
        }
        \IPS\Dispatcher::i()->checkAcpPermission( 'cons_manage' );
        parent::execute();
    }

    /**
     * @inheritdoc
     */
    protected function manage()
    {
        $form = Cons::i()->form();
        Output::i()->output = $form;
    }

    // Create new methods with the same name as the 'do' parameter which should execute it
}
