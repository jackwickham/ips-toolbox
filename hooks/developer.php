//<?php

use IPS\Application;
use IPS\Db;
use IPS\Db\Exception;
use IPS\Helpers\Form\Select;
use IPS\Http\Url;
use IPS\Output;
use IPS\Request;
use IPS\toolbox\DevCenter\Extensions\ExtensionException;
use IPS\toolbox\DevCenter\Schema;
use IPS\toolbox\DevCenter\Sources;
use IPS\toolbox\Forms;

if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class toolbox_hook_developer extends _HOOK_CLASS_
{
    public function execute( $command = 'do' )
    {

        $appKey = Request::i()->appKey;
        Output::i()->jsVars[ 'dtdevplus_table_url' ] = (string)$this->url->setQueryString( [ 'appKey' => $appKey ] );
        parent::execute( $command );
    }

    public function manage()
    {

        Sources::menu();
        parent::manage();
    }

    public function addVersionQuery()
    {

        Output::i()->jsFiles = \array_merge( Output::i()->jsFiles, Output::i()->js( 'admin_query.js', 'toolbox', 'admin' ) );

        $tables = Db::i()->query( 'SHOW TABLES' );
        $t = [];
        $t[ 0 ] = 'Select Table';

        foreach ( $tables as $table ) {
            $foo = \array_values( $table );
            $t[ $foo[ 0 ] ] = $foo[ 0 ];
        }

        $el[ 'prefix' ] = 'dtdevplus_';

        $el[] = [
            'name'     => 'select',
            'class'    => 'Select',
            'required' => \true,
            'ops'      => [
                'options' => [
                    0            => 'Select One',
                    'addColumn'  => 'Add Column',
                    'dropColumn' => 'Drop Column',
                    'code'       => 'Code Box',
                ],
                'toggles' => [
                    'code'       => [
                        'code',
                    ],
                    'dropColumn' => [
                        'ext_table',
                        'ext_field',
                    ],
                    'addColumn'  => [
                        'ext_table',
                        'add_column',
                        'type',
                        'length',
                        'decimals',
                        'default',
                        'comment',
                        'allow_null',
                        'unsigned',
                        'zerofill',
                        'auto_increment',
                        'binary',
                        'binary',
                        'values',
                    ],
                ],
            ],
        ];

        $val = function ( $val )
        {

            /* Check it starts with \IPS\Db::i()-> */
            $val = \trim( $val );
            if ( \mb_substr( $val, 0, 14 ) !== '\IPS\Db::i()->' ) {
                throw new \DomainException( 'versions_query_start' );
            }

            /* Check there's only one query */
            if ( \mb_substr( $val, -1 ) !== ';' ) {
                $val .= ';';
            }
            if ( \mb_substr_count( $val, ';' ) > 1 ) {
                throw new \DomainException( 'versions_query_one' );
            }

            /* Check our Regex will be okay with it */
            \preg_match( '/^\\\IPS\\\Db::i\(\)->(.+?)\(\s*[\'"](.+?)[\'"]\s*(,\s*(.+?))?\)\s*;$/', $val, $matches );
            if ( empty( $matches ) ) {
                throw new \DomainException( 'versions_query_format' );
            }

            /* Run it if we're adding it to the current working version */
            if ( Request::i()->id === 'working' ) {
                try {
                    try {
                        if ( @eval( $val ) === \false ) {
                            throw new \DomainException( 'versions_query_phperror' );
                        }
                    } catch ( \ParseError $e ) {
                        throw new \DomainException( 'versions_query_phperror' );
                    }
                } catch ( Exception $e ) {
                    throw new \DomainException( $e->getMessage() );
                }
            }
        };

        $el[] = [
            'name'     => 'code',
            'class'    => "TextArea",
            'default'  => '\IPS\Db::i()->',
            'required' => \true,
            'v'        => $val,
            'ops'      => [
                'size' => 45,
            ],
        ];

        if ( !isset( Request::i()->dtdevplus_code ) || Request::i()->dtdevplus_code !== 'code' ) {
            $el[] = [
                'name'     => 'ext_table',
                'class'    => 'Select',
                'required' => \true,
                'ops'      => [
                    'options' => $t,
                    'parse'   => 'raw',
                ],
            ];

            $el[] = [
                'name'  => 'ext_field',
                'class' => 'Select',
                'ops'   => [
                    'options'           => [

                    ],
                    'userSuppliedInput' => \true,
                ],
            ];

            $ints = [
                'add_column',
                'length',
                'allow_null',
                'default',
                'comment',
                'sunsigned',
                'zerofill',
                'auto_increment',
            ];

            $decfloat = [
                'add_column',
                'length',
                'decimals',
                'allow_null',
                'default',
                'comment',
                'sunsigned',
                'zerofill',
            ];

            $dates = [
                'add_column',
                'allow_null',
                'default',
                'comment',
            ];

            $char = [
                'add_column',
                'length',
                'allow_null',
                'default',
                'comment',
                'binary',
            ];

            $text = [
                'add_column',
                'allow_null',
                'comment',
                'binary',
            ];

            $binary = [
                'add_column',
                'length',
                'allow_null',
                'default',
                'comment',
            ];

            $blob = [
                'add_column',
                'allow_null',
                'comment',
            ];

            $enum = [
                'add_column',
                'values',
                'allow_null',
                'default',
                'comment',
            ];

            $el[] = [
                'class' => 'Select',
                'name'  => 'type',
                'ops'   => [
                    'options' => Db::$dataTypes,
                    'toggles' => [
                        'TINYINT'    => $ints,
                        'SMALLINT'   => $ints,
                        'MEDIUMINT'  => $ints,
                        'INT'        => $ints,
                        'BIGINT'     => $ints,
                        'DECIMAL'    => $decfloat,
                        'FLOAT'      => $decfloat,
                        'BIT'        => [
                            'columns',
                            'length',
                            'allow_null',
                            'default',
                            'comment',
                        ],
                        'DATE'       => $dates,
                        'DATETIME'   => $dates,
                        'TIMESTAMP'  => $dates,
                        'TIME'       => $dates,
                        'YEAR'       => $dates,
                        'CHAR'       => $char,
                        'VARCHAR'    => $char,
                        'TINYTEXT'   => $text,
                        'TEXT'       => $text,
                        'MEDIUMTEXT' => $text,
                        'LONGTEXT'   => $text,
                        'BINARY'     => $binary,
                        'VARBINARY'  => $binary,
                        'TINYBLOB'   => $blob,
                        'BLOB'       => $blob,
                        'MEDIUMBLOB' => $blob,
                        'BIGBLOB'    => $blob,
                        'ENUM'       => $enum,
                        'SET'        => $enum,

                    ],
                ],
            ];

            $el[] = [
                'name'     => 'add_column',
                'required' => \true,
                'class'    => 'Text',
            ];

            $el[] = [
                'name'  => 'values',
                'class' => 'Stack',
            ];

            $el[] = [
                'name'    => 'length',
                'class'   => 'Number',
                'default' => 255,
            ];

            $el[] = [
                'name'  => 'allow_null',
                'class' => 'YesNo',
            ];

            $el[] = [
                'name'  => 'decimals',
                'class' => 'Number',
            ];

            $el[] = [
                'name'  => 'default',
                'class' => 'TextArea',
            ];

            $el[] = [
                'name'  => 'comment',
                'class' => 'TextArea',
            ];

            $el[] = [
                'name'  => 'sunsigned',
                'class' => 'YesNo',
            ];

            $el[] = [
                'name'  => 'zerofill',
                'class' => 'YesNo',
            ];

            $el[] = [
                'name'  => 'auto_increment',
                'class' => 'YesNo',
            ];

            $el[] = [
                'name'  => 'binary',
                'class' => 'YesNo',
            ];

            $el[] = [
                'name'  => 'values',
                'class' => 'Stack',
            ];
        }

        $config = [
            'elements'   => $el,
            'attributes' => [ 'data-controller' => 'ips.admin.dtdevplus.query' ],
            'name'       => 'add_version_query',
        ];

        $forms = Forms::execute( $config );

        /* If submitted, add to json file */
        if ( $vals = $forms->values() ) {
            /* Get our file */
            $version = Request::i()->id;
            $json = $this->_getQueries( $version );
            $install = $this->_getQueries( 'install' );
            if ( $vals[ 'dtdevplus_select' ] !== 'code' ) {
                $type = $vals[ 'dtdevplus_select' ];
                $table = $vals[ 'dtdevplus_ext_table' ];
                if ( $type === 'dropColumn' ) {
                    $column = $vals[ 'dtdevplus_ext_field' ];
                    $json[] = [ 'method' => $type, 'params' => [ $table, $column ] ];
                    Db::i()->dropColumn( $table, $column );
                }
                else {
                    $column = $vals[ 'dtdevplus_add_column' ];
                    $schema = [];
                    $schema[ 'name' ] = $vals[ 'dtdevplus_add_column' ];
                    $schema[ 'type' ] = $vals[ 'dtdevplus_type' ];

                    if ( isset( $vals[ 'dtdevplus_length' ] ) && $vals[ 'dtdevplus_length' ] ) {
                        $schema[ 'length' ] = $vals[ 'dtdevplus_length' ];
                    }
                    else {
                        $schema[ 'length' ] = \null;
                    }

                    if ( isset( $vals[ 'dtdevplus_decimals' ] ) && $vals[ 'dtdevplus_decimals' ] ) {
                        $schema[ 'decimals' ] = $vals[ 'dtdevplus_decimals' ];
                    }
                    else {
                        $schema[ 'decimals' ] = \null;
                    }

                    if ( isset( $vals[ 'dtdevplus_values' ] ) && \count( $vals[ 'dtdevplus_values' ] ) ) {
                        $schema[ 'values' ] = $vals[ 'dtdevplus_values' ];
                    }
                    else {
                        $schema[ 'values' ] = \null;
                    }

                    if ( isset( $vals[ 'dtdevplus_allow_null' ] ) && $vals[ 'dtdevplus_allow_null' ] ) {
                        $schema[ 'allow_null' ] = \true;
                    }
                    else {
                        $schema[ 'allow_null' ] = \false;
                    }

                    if ( isset( $vals[ 'dtdevplus_default' ] ) && $vals[ 'dtdevplus_default' ] ) {
                        $schema[ 'default' ] = $vals[ 'dtdevplus_default' ];
                    }
                    else {
                        $schema[ 'default' ] = \null;
                    }

                    if ( isset( $vals[ 'dtdevplus_comment' ] ) && $vals[ 'dtdevplus_comment' ] ) {
                        $schema[ 'comment' ] = $vals[ 'dtdevplus_comment' ];
                    }
                    else {
                        $schema[ 'comment' ] = '';
                    }

                    if ( isset( $vals[ 'dtdevplus_sunsigned' ] ) && $vals[ 'dtdevplus_sunsigned' ] ) {
                        $schema[ 'unsigned' ] = $vals[ 'dtdevplus_sunsigned' ];
                    }
                    else {
                        $schema[ 'unsigned' ] = \false;
                    }

                    if ( isset( $vals[ 'dtdevplus_zerofill' ] ) && $vals[ 'dtdevplus_zerofill' ] ) {
                        $schema[ 'zerofill' ] = $vals[ 'dtdevplus_zerofill' ];
                    }
                    else {
                        $schema[ 'zerofill' ] = \false;
                    }

                    if ( isset( $vals[ 'dtdevplus_auto_increment' ] ) && $vals[ 'dtdevplus_auto_increment' ] ) {
                        $schema[ 'auto_increment' ] = $vals[ 'dtdevplus_auto_increment' ];
                    }
                    else {
                        $schema[ 'auto_increment' ] = \false;
                    }

                    if ( isset( $vals[ 'dtdevplus_binary' ] ) && $vals[ 'dtdevplus_binary' ] ) {
                        $schema[ 'binary' ] = $vals[ 'dtdevplus_auto_increment' ];
                    }
                    else {
                        $schema[ 'binary' ] = \false;
                    }

                    if ( $type === 'addColumn' ) {
                        $json[] = [ 'method' => $type, 'params' => [ $table, $schema ] ];
                        $install[] = [ 'method' => $type, 'params' => [ $table, $schema ] ];
                        $this->_writeQueries( 'install', $install );
                        Db::i()->addColumn( $table, $schema );
                    }
                    else {
                        if ( $type === 'changeColumn' ) {
                            $json[] = [ 'method' => $type, 'params' => [ $table, $column, $schema ] ];
                            Db::i()->changeColumn( $table, $column, $schema );
                        }
                    }
                }

            }
            else {

                /* Work out the different parts of the query */
                $val = \trim( $vals[ 'dtdevplus_code' ] );
                if ( \mb_substr( $val, -1 ) !== ';' ) {
                    $val .= ';';
                }

                \preg_match( '/^\\\IPS\\\Db::i\(\)->(.+?)\(\s*(.+?)\s*\)\s*;$/', $val, $matches );

                /* Add it on */
                $json[] = [
                    'method' => $matches[ 1 ],
                    'params' => eval( 'return array( ' . $matches[ 2 ] . ' );' ),
                ];
            }

            /* Write it */
            $this->_writeQueries( $version, $json );

            /* Redirect us */
            Output::i()->redirect( Url::internal( "app=core&module=applications&controller=developer&appKey={$this->application->directory}&tab=versions&root={$version}" ) );
        }

        Output::i()->output = $forms;
    }

    protected function _manageDevFolder()
    {

        return \IPS\toolbox\DevCenter\Dev::i()->form();
    }

    protected function _manageSchemaImports()
    {

        $schema = $this->_getSchema();

        return Schema::i()->form( $schema, $this->application );
    }

    protected function _manageGitHooks()
    {

        $app = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/';
        $git = $app . '.git/';

        if ( \is_dir( $git ) ) {
            if ( isset( Request::i()->hookType ) ) {
                $gitPath = $git . 'hooks/';
                $type = Request::i()->hookType;
                $hook = $gitPath = $gitPath . $type;
                $app = $this->application->directory;
                switch ( $type ) {
                    case 'pre-commit':
                        if ( !\is_file( $hook ) ) {
                            $content = <<<'EOF'
#!/usr/bin/php
<?php
require "/home/michael/public_html/dev/init.php";
$gitHooks = (new \IPS\toolbox\GitHooks( ["toolbox"] ) )->removeSpecialHooks(true);
EOF;
                            \file_put_contents( $hook, $content );
                        }
                        break;
                    case 'post-commit':
                        if ( !\is_file( $hook ) ) {
                            $content = <<<'EOF'
#!/usr/bin/php
<?php
require "/home/michael/public_html/dev/init.php";
$gitHooks = (new \IPS\toolbox\GitHooks( ["toolbox"] ) )->writeSpecialHooks(true);
EOF;
                            \file_put_contents( $hook, $content );
                        }
                        break;
                }
            }
            $url = Url::internal( "app=core&module=applications&controller=developer&appKey={$this->application->directory}&tab=GitHooks" );
            $precommit = $url->setQueryString( [ 'hookType' => 'pre-commit' ] );
            $html = <<<EOF
<div class="ipsGrid ipsPad">
    <div class="ipsGrid_span1 ipsPad">Pre-Commit </div>
    <div class="ipsGrid_span2 ipsPad"><a class="ipsButton ipsButton_fullWidth ipsButton_important" href="{$precommit}">add</a></div>
    <div class="ipsGrid_span9 ipsPad">Adds a pre-commit hook for git, this is useful if you are using the specialHooks extension for dtproxy.</div>
</div>
EOF;
        }
        else {
            $html = <<<EOF
<div class="ipsPad">No Git repo found for this application</div>
EOF;
        }

        return $html;
    }

    protected function _writeJson( $file, $data )
    {

        if ( $file === \IPS\ROOT_PATH . "/applications/{$this->application->directory}/data/settings.json" ) {
            if ( Application::appIsEnabled( 'dtproxy' ) ) {
                \IPS\toolbox\Generator\Proxy::i()->generateSettings();
            }
        }

        parent::_writeJson( $file, $data );
    }

    protected function addExtension()
    {

        Output::i()->jsFiles = \array_merge( Output::i()->jsFiles, Output::i()->js( 'admin_query.js', 'toolbox', 'admin' ) );

        $appKey = Request::i()->appKey;
        $supportedExtensions = [
            'FileStorage',
            'ContentRouter',
            'CreateMenu',
        ];

        $supportedApps = [
            'core',
        ];
        $extapp = Request::i()->extapp;
        $extension = Request::i()->type;
        $ours = \false;

        if ( \in_array( $extapp, $supportedApps ) && \in_array( $extension, $supportedExtensions ) ) {
            try {
                $application = Application::load( $appKey );
                $extapp = Application::load( $extapp );
                $ours = \true;
            } catch ( \Exception $e ) {
            }
        }

        if ( $ours === \false ) {
            parent::addExtension();
        }
        else {
            $baseClass = '\\IPS\\toolbox\DevCenter\\Extensions\\';
            $class = $baseClass . $extension;
            /* @var IPS\toolbox\DevCenter\Extensions\ExtensionsAbstract $class */
            $class = new $class( $extapp, $application, $extension );
            try {
                Output::i()->output = $class->form();
            } catch ( ExtensionException $e ) {
                Request::i()->dev_extensions_classname = Request::i()->dtdevplus_ext_class;
                parent::addExtension();
            }
        }
    }

    // Create new methods with the same name as the 'do' parameter which should execute it


    protected function dtgetFields()
    {

        $table = Request::i()->table;
        $fields = Db::i()->query( "SHOW COLUMNS FROM " . Db::i()->real_escape_string( Db::i()->prefix . $table ) );
        $f = [];
        foreach ( $fields as $field ) {
            $f[ \array_values( $field )[ 0 ] ] = \array_values( $field )[ 0 ];
        }

        $data = new Select( 'dtdevplus_ext_field', \null, \false, [
            'options'           => $f,
            'parse'             => \false,
            'userSuppliedInput' => \true,
        ], \null, \null, \null, 'js_dtdevplus_ext_field' );

        $send[ 'error' ] = 0;
        $send[ 'html' ] = $data->html();
        Output::i()->json( $send );
    }

    protected function dtdevplusImport()
    {

        $table = Request::i()->table;
        $schema = $this->_getSchema();
        $exists = $schema[ $table ] ?? \null;
        if ( $exists !== \null ) {
            //blank the existing inserts
            $schema[ $table ][ 'inserts' ] = [];
            $write = \false;
            $rows = Db::i()->select( '*', $table );
            foreach ( $rows as $row ) {
                $schema[ $table ][ 'inserts' ][] = $row;
                $write = \true;
            }

            if ( $write === \true ) {
                $this->_writeSchema( $schema );
            }
        }

        Output::i()->redirect( Url::internal( "app=core&module=applications&controller=developer&appKey={$this->application->directory}&tab=SchemaImports" ) );
    }

}