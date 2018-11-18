<?php
    include_once "Zipper.php";
    include_once "FolderManipulator.php";
    include_once "NameManipulator.php";

    class DatabaseOrganize{

    static function getCoverImage($path){
        $read = fopen($path, 'r');
        while(!feof($read)){
            $line = fgets($read);
            if(stristr($line, 'id="cover"')){
                $hrefPos = strpos($line, 'href=');
                $hrefToEnd = substr($line, $hrefPos, strlen($line));
                $pathToImage = substr($hrefToEnd, 6, strpos(substr($hrefToEnd, 6, strlen($hrefToEnd)+1), '"'));
                return $pathToImage;
            }
        }
        return '';
    }

    static function organize($epubPath){
        $r = new stdClass();
        $r->book_path = $epubPath;
        $r->opf_path = '';
        $r->icon_img_path = '';
        $extractedPath = dirname($epubPath) . "/extracted";
        mkdir($extractedPath, 0777);
        Zipper::unzip($epubPath, $extractedPath);
        foreach(FolderManipulator::getFolders($extractedPath) as $i)
            if(strcmp(NameManipulator::getFileExtension($i), 'opf')==0){
                $r->opf_path = $i;
                $coverImageRelativePath = DatabaseOrganize::getCoverImage($i);
                $r->icon_img_path = $coverImageRelativePath!='' ? dirname($r->opf_path) . "/$coverImageRelativePath" : '' ;

                break;
            }
        return $r;
    }
    }