<?php
/**
 * @brief      Cons Singleton
 * @copyright  -storm_copyright-
 * @package    IPS Social Suite
 * @subpackage toolbox
 * @since      -storm_since_version-
 * @version    -storm_version-
 */


namespace IPS\toolbox\Build;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

use IPS\IPS;
use IPS\Member;
use IPS\Output;
use IPS\Patterns\Singleton;
use IPS\Request;
use IPS\toolbox\Forms;
use function constant;
use function gettype;
use function in_array;
use function mb_ucfirst;

/**
 * Cons Class
 *
 * @mixin \IPS\toolbox\Build\Cons
 */
class _Cons extends Singleton
{

    /**
     * @brief Singleton Instances
     * @note  This needs to be declared in any child class.
     * @var static
     */
    protected static $instance;
    protected static $devTools = [
        'DTBUILD',
        'DTPROFILER',
        'BYPASSPROXYDT',
    ];
    protected static $importantIPS = [
        'BYPASS_ACP_IP_CHECK',
        'IN_DEV',
        'IN_DEV_STRICT_MODE',
        'USE_DEVELOPMENT_BUILDS',
        'DEV_WHOOPS_EDITOR',
        'DEV_DEBUG_JS',
        'QUERY_LOG',
        'COOKIE_PREFIX',
        'CP_DIRECTORY',
        'DEV_USE_WHOOPS',
        'DEV_HIDE_DEV_TOOLS',
        'DEV_DEBUG_CSS',
        'DEBUG_TEMPLATES',
        'DEBUG_LOG',
        'COOKIE_PATH',
    ];
    protected $constants;

    public function form()
    {
        $constants = $this->buildConstants();
        $e = [];
        foreach ( $constants as $key => $value ) {
            $tab = mb_ucfirst( mb_substr( $key, 0, 1 ) );

            if ( in_array( $key, static::$devTools, true ) ) {
                $tab = 'DevTools';
            }

            if ( in_array( $key, static::$importantIPS, true ) ) {
                $tab = 'Important';
            }

            Member::loggedIn()->language()->words[ $tab . '_tab' ] = $tab;
            $e[ $key ] = [
                'name'        => $key,
                'label'       => $key,
                'default'     => $value[ 'current' ],
                'description' => $value[ 'description' ] ?? null,
                'tab'         => $tab,

            ];

            switch ( gettype( $value[ 'current' ] ) ) {
                case 'boolean':
                    $e[ $key ][ 'class' ] = 'yn';
                    $e[ $key ][ 'default' ] = (bool)$value[ 'current' ];
                    break;
                case 'int':
                    $e[ $key ][ 'class' ] = 'number';
                    break;
            }
        }

        $forms = Forms::execute( [ 'elements' => $e ] );

        if ( $values = $forms->values() ) {
            $this->save( $values, $constants );
            Output::i()->redirect( Request::i()->url(), 'Constants.php Updated!' );
        }

        return $forms;
    }

    protected function buildConstants()
    {

        if ( $this->constants === null ) {
            $cons = IPS::defaultConstants();
            $first = [];
            $constants = [];
            foreach ( $cons as $key => $con ) {
                if ( $key === 'READ_WRITE_SEPARATION' || $key === 'REPORT_EXCEPTIONS' ) {
                    continue;
                }
                $current = constant( '\\IPS\\' . $key );

                $data = [
                    'name'    => $key,
                    'default' => $con,
                    'current' => $current,
                    'type'    => gettype( constant( '\\IPS\\' . $key ) ),
                ];

                if ( in_array( $key, static::$importantIPS, true ) ) {
                    $first[ $key ] = $data;
                }
                else {
                    $constants[ $key ] = $data;
                }
            }
            ksort( $constants );

            $toolbox = [
                'BYPASSPROXYDT' => [
                    'name'        => 'BYPASSPROXYDT',
                    'default'     => false,
                    'current'     => defined( 'BYPASSPROXYDT' ) ? BYPASSPROXYDT : null,
                    'description' => 'This is a very special use case, if defined, will create dtproxy2 and copy the contents of dtproxy2 to dtproxy when building proxy files.',
                    'type'        => 'boolean',
                ],
                'DTBUILD'       => [
                    'name'        => 'DTBUILD',
                    'default'     => false,
                    'current'     => defined( 'DTBUILD' ) ? DTBUILD : null,
                    'description' => 'This enables special app build features for toolbox, use with caution.',
                    'type'        => 'boolean',
                ],
                'DTPROFILER'    => [
                    'name'        => 'DTPROFILER',
                    'default'     => false,
                    'current'     => defined( 'DTPROFILER' ) ? DTPROFILER : null,
                    'description' => 'this will enable/disable extra features for the profiler.',
                    'type'        => 'boolean',
                ],
            ];

            $this->constants = array_merge( $toolbox, $first, $constants );

        }

        return $this->constants;
    }

    public function save( array $values, array $constants )
    {
        $toWrite = [];

        foreach ( $constants as $key => $val ) {
            $data = $values[ $key ];
            switch ( $val[ 'type' ] ) {
                case 'integer':
                case 'boolean':
                    $check = (int)$data;
                    $check2 = (int)$val[ 'default' ];
                    break;
                default:
                    $check2 = (string)$val[ 'default' ];
                    $check = (string)$data;
                    break;
            }
            if ( ( defined( '\\IPS\\' . $key ) && $check !== $check2 ) || in_array( $key, static::$devTools, true ) ) {

                $dataType = "'" . $data . "'";

                switch ( $val[ 'type' ] ) {
                    case 'integer':
                        $dataType = (int)$data;
                        break;
                    case 'boolean':
                        $dataType = $data ? 'true' : 'false';
                        break;
                }

                $toWrite[] = "\\define('" . $key . "'," . $dataType . ');';
            }
        }
        $toWrite = implode( "\n", $toWrite );
        $fileData = <<<EOF
<?php
{$toWrite}
EOF;
        if ( \IPS\NO_WRITES !== \true ) {

            \file_put_contents( \IPS\ROOT_PATH . '/constants.php', $fileData );
        }
    }
}

