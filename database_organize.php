<?php
    include_once "Zipper.php";
    include_once "FolderManipulator.php";
    include_once "NameManipulator.php";

    function database_organize($epubPath){
        $r = new stdClass();
        $r->epubPath = $epubPath;
        $r->opfPath = '';
        $extractedPath = dirname($epubPath) . "/extracted";
        mkdir($extractedPath, 0777);
        Zipper::unzip($epubPath, $extractedPath);
        foreach(FolderManipulator::getFolders($extractedPath) as $i)
            if(strcmp(NameManipulator::getFileExtension($i), 'opf')==0){
                $r->opfPath = $i;
                break;
            }
        return $r;
    }