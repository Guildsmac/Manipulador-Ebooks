<?php
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/4/2018
 * Time: 11:22 PM
 */

class NameManipulator{
    public static function getFileName($filePath){
        return pathinfo($filePath, PATHINFO_FILENAME);

    }
    public static function getFileExtension($filePath){
        return pathinfo($filePath, PATHINFO_EXTENSION);

    }
    public static function getFilePath($filePath){
        return pathinfo($filePath, PATHINFO_DIRNAME);
    }

    public static function invertSlashes($path){
        $path = str_replace('\\', '/', $path);
        return $path;
    }

    public static function normalizePath($path){
        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '|(?<=.)/+|', '/', $path );
        if ( ':' === substr( $path, 1, 1 ) ) {
            $path = ucfirst( $path );
        }
        return $path;
    }

}