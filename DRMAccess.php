<?php
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/12/2018
 * Time: 5:07 PM
 */

class DRMAccess{
    public static function getHTMLDRM($userObj){
        /*$file = fopen($htmlPath, 'r');
        while(!feof($file)){
            $line = fgets($file);
            if(stristr($line, '<footer')){
                fclose($file);
                return $line;
            }
        }
        return null;*/
        return "<footer class=\"watermarkDRM\"><p> Nome: {$userObj->name} </p><p> CPF: {$userObj->cpf} </p><p> ID: {$userObj->id}</p></footer>";

    }
    public static function getHTMLDRMRef($cssRelPath){
        return $cssRelPath!='' ? ('<link rel="stylesheet" href="' . $cssRelPath . '/DRM.css" />') : ('<link rel="stylesheet" href="./DRM.css" />');
    }

    public static function getOPFRef($cssPath){
        return '<item id="drm"  href="' . $cssPath . '" media-type="text/css" />';
    }

    public static function getOPFImageRef($imgPath = null){
        $r = '';
        if($imgPath){
            $r = "\n" . '<item id = "drm_img " href = "' . $imgPath . '" media-type="image/jpg" />' . "\n";
        }
        return $r;
    }

}