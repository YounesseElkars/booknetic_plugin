<?php

namespace BookneticApp\Providers\Core;


use BookneticApp\Providers\Helpers\Helper;

class Controller
{
    public $modulesDir;
    public $prefix;

    final protected static function getViewFilePath($viewName )
    {
        $viewName = str_replace('.', DIRECTORY_SEPARATOR, $viewName );

        $calledClass = get_called_class();
        $classFile = new \ReflectionClass( $calledClass );
        $classFile = $classFile->getFileName();

        return plugin_dir_path($classFile) . 'view' . DIRECTORY_SEPARATOR . $viewName . '.php';
    }

    final protected function view ( $viewName, $parameters = [], $extends = 'index', $enqueeAssets = false )
    {
        $_mn = Helper::_post( '_mn', '0', 'int' );

        if( file_exists( $viewName ) )
        {
            $childViewFile = $viewName;
        }
        else
        {
            $childViewFile  = static::getViewFilePath( $viewName );
        }


        if( ! file_exists( $childViewFile ) )
        {
            echo $this->response( false, htmlspecialchars( $viewName ) . ' - view not exists!' );
            return;
        }

        $fullViewPath = $extends === false ? $childViewFile : $this->modulesDir . 'Base' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $extends . '.php';

        $currentModule = Route::getCurrentModule();
        $currentAction = Route::getCurrentAction();

        if( $enqueeAssets )
        {
            do_action( $this->prefix.'enqueue_assets', $currentModule, $currentAction, $fullViewPath );
        }

        require_once $fullViewPath;
    }

    final protected function modalView ( $viewName, $parameters = [], $createJSVariables = [] )
    {
        if( ! file_exists( $viewName ) )
        {
            $viewName = "modal.{$viewName}";
        }

        ob_start();
        $this->view( $viewName, $parameters, false, true );

        $JSadditionalHTML = '';
        foreach ( (array)$createJSVariables as $variable => $value )
        {
            $JSadditionalHTML .= '<script type="text/javascript">var '.$variable.' = '.json_encode( $value ).';</script>';
        }

        return $this->response( true, [
            'html' => htmlspecialchars( $JSadditionalHTML . ob_get_clean() )
        ] );
    }

    final protected function tabView( $viewName )
    {
        if( ! file_exists( $viewName ) )
        {
            $viewName = "modal.{$viewName}";
        }

        $childViewFile = static::getViewFilePath( $viewName );

        if( ! file_exists( $childViewFile ) )
        {
            return '';
        }

        return $childViewFile;
    }

    final protected function response( $status , $arr = [] )
    {
        return Helper::response( $status , $arr, true );
    }
}
