//<?php


if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) ) {
    header( ( $_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0' ) . ' 403 Forbidden' );
    exit;
}

class toolbox_hook_BuilderIterator extends _HOOK_CLASS_toolbox_hook_BuilderIterator
{
    
    /**
    * @inheritdoc
    */
    public function current()
    {

        $file = $this->key();
        $file = \IPS\ROOT_PATH . '/applications/' . $this->application->directory . '/' . $file;
        $path = new \SplFileInfo( $this->key() );
        if ( \is_file( $file ) && ( \mb_strpos( $file, '3rdparty' ) === \false || \mb_strpos( $file, '3rd_party' ) === \false || \mb_strpos( $file, 'vendor' ) === \false ) ) {
            if ( !\IPS\toolbox\DevCenter\Headerdoc::i()->can( $this->application ) ) {
                return $file;
            }

            if ( $path->getExtension() === 'php' ) {
                $temporary = \tempnam( \IPS\TEMP_DIRECTORY, 'IPS' );
                $contents = \file_get_contents( $file );
                if ( \mb_strpos( $contents, '_HOOK_CLASS_' ) !== \false ) {
                    $contents = \IPS\Plugin::addExceptionHandlingToHookFile( $file );
                }
                $contents = \preg_replace( '#\b_HOOK_CLASS_' . $this->application->directory . '_hook_' . $path->getBasename( '.php' ) . '\b#', '_HOOK_CLASS_', $contents );

                /* @var \IPS\toolbox\DevCenter\extensions\toolbox\DevCenter\Headerdoc\Headerdoc $class */
                foreach ( $this->application->extensions( 'toolbox', 'Headerdoc', \true ) as $class ) {
                    if ( \method_exists( $class, 'finalize' ) ) {
                        $contents = $class->finalize( $contents, $this->application );
                    }
                }

                \file_put_contents( $temporary, $contents );
                \register_shutdown_function( function ( $temporary )
                {
                    \unlink( $temporary );
                }, $temporary );

                return $temporary;
            }
        }

        return $file;
    }

}


