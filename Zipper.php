<?php
include_once "FolderManipulator.php";
include_once "DefaultMessages.php";

class Zipper{
    public static function zipFolder($sourcePath, $fileName, $destPath = null){
        $filesList = FolderManipulator::getFolders($sourcePath);
        if($destPath==null)
            $destPath=$sourcePath;
        $path = $destPath . "/" . $fileName;
        $zip = new ZipArchive;
        $rootName = substr($sourcePath, strrpos($sourcePath, '/')+1);
        if ($zip->open($path, ZipArchive::CREATE|ZipArchive::OVERWRITE) === TRUE){
            foreach($filesList as $i){
                $actPath = Zipper::getRelativePath($i, $rootName);
                $actPath = str_replace('\\', '/', $actPath);
                $actPath = str_replace($rootName. '/', '', $actPath);
                if(is_file($i))
                    $zip->addFile($i, $actPath);
            }
            $zip->close();
        }else
            DefaultMessages::zipOpenError();




    }

    public static function unzip($sourcePath, $destPath){
        $zip = new ZipArchive;
        if($zip->open($sourcePath)===TRUE) {
            $zip->extractTo($destPath);
            $zip->close();

        }
        else
            DefaultMessages::zipOpenError();
    }

    public static function getRelativePath($completePath, $rootPath){ //GET THE PATH FROM THE ROOT STRING
        $r = substr($completePath, strrpos($completePath, NameManipulator::invertSlashes($rootPath))+strlen($rootPath)+1);
        return $r;
    }

}