<?php
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/6/2018
 * Time: 9:23 AM
 */

class FolderManipulator{
    public static function getFolders($path){ //GET LIST OF FODLERS FROM GIVEN PATh
        $r = array();
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach($objects as $name => $object)
            if(!$objects->isDot()) {
                $name = NameManipulator::invertSlashes($name);
                $r[] = $name;
            }

        return $r;
    }
    public static function getNewFolder($path, $folderName){
        $cont = 1;
        $actFolder = $path . "/" . $folderName . "-" . $cont;
        if(file_exists($actFolder)){
            while(file_exists($actFolder))
                $actFolder = $path . "/" . $folderName . "-" . ++$cont;
        }
        mkdir($actFolder, 0777);
        return $actFolder;
    }
    public static function getNewFolderNoCreate($path, $folderName){
        $cont = 1;
        $actFolder = $path . "\\" . $folderName . "-" . $cont;
        if(file_exists($actFolder)){
            while(file_exists($actFolder))
                $actFolder = $path . "\\" . $folderName . "-" . ++$cont;
        }
        return $actFolder;
    }

}