<?php
/**
 * Copyright (c) 2019.
 * Developed by Gabriel Sousa
 * @author Gabriel Sousa <gabrielssc.ti@gmail.com>
 * Last modified 18/11/18 17:18.
 *
 */

include_once "DRMAccess.php";
include_once "Constants.php";
include_once "NameManipulator.php";
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/10/2018
 * Time: 8:54 AM
 */

/**
 *
 * IMPORTANTE
 * FALTA PEGAR UM JPG E BOTAR NA PASTA DE IMAGENS DO EBOOK E DEPOIS RETORNAR O ENDEREÇO RELATIVO PARA CRIAR O LINK
 * CONSERTAR A INSERÇÃO DO BACKGROUND NO CSS
 *
 */
class DRMInserter
{
    public static $opfPath;

    public static function insertDRM($bookPath, $userObj, $imgArray = null)
    {
        $pathList = FolderManipulator::getFolders($bookPath);
        $cssFolder = DRMInserter::getCSSFolder($bookPath);
        $imageFolder = DRMInserter::getImageFolder($bookPath);
        $bookImagePath = null;
        $imagePathRelativeToCSS = null;
        if ($imgArray) {
            $bookImagePath = self::createImageFile($imageFolder, $imgArray->getImageUrl());
            $imagePathRelativeToCSS = self::getRelativePathToOther($cssFolder . '/DRM.css', $imageFolder) . NameManipulator::getFileName($bookImagePath) . '.' . NameManipulator::getFileExtension($bookImagePath);
        }
        self::createCSSFile($cssFolder . '/', $imagePathRelativeToCSS);

        foreach ($pathList as $i) {
            if (strcmp('TOC', NameManipulator::getFileName($i)) != 0) {
                $tempExt = NameManipulator::getFileExtension($i);
                if (DRMInserter::hasModifiableExtension($tempExt) && !is_dir($i))
                    if (strcmp('css', $tempExt) != 0)
                        DRMInserter::modifyStructure($i, $tempExt, $cssFolder, $userObj, $bookImagePath);

            }
        }


    }

    private static function getExtensions()
    {
        return array('xhtml', 'html', 'css', 'opf');

    }

    private static function hasModifiableExtension($ext)
    {
        foreach (DRMInserter::getExtensions() as $i) {
            //echo "actExt: $i, destExt: $ext<br>";
            if (strcasecmp($ext, $i) == 0)
                return true;
        }
        return false;
    }

    private static function getRelativePathToOther($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);

    }

    private static function modifyStructure($path, $ext, $cssFolder, $userObj, $imgPath = null)
    {
        $read = fopen($path, 'r');
        $tempName = $path . '.tmp';
        $write = fopen($tempName, 'w');
        $replaced = false;

        while (!feof($read)) {
            $line = fgets($read);
            if (strcmp('html', $ext) == 0 | strcmp('xhtml', $ext) == 0) {
                if ($imgPath)
                    if (stristr($line, '<body')) {
                        $line .= "\n" . '<div class = "watermarkDRMImage"></div>';
                        $replace = true;
                    }
                if (stristr($line, '</head>')) {
                    $pos = strpos($line, '</head>');
                    //$cssFolder = substr($cssFolder, strrpos($path, '/')+1, strlen($cssFolder));
                    $cssFolder = DRMInserter::getRelativePathToOther($path, $cssFolder);
                    $line = substr($line, 0, $pos) . DRMAccess::getHTMLDRMRef($cssFolder) . substr($line, $pos, strlen($line));
                    $replaced = true;
                }
                if (stristr($line, '</body>')) {
                    $pos = strpos($line, '</body>');
                    $line = substr($line, 0, $pos) . DRMAccess::getHTMLDRM($userObj) . substr($line, $pos, strlen($line));
                    $replaced = true;
                }
                fputs($write, $line);
            }

            if (strcmp('opf', $ext) == 0) {

                DRMInserter::$opfPath = $path;
                if (stristr($line, '</manifest>')) {
                    $pos = strpos($line, '</manifest>');
                    $cssFolder = self::getRelativePathToOther($path, $cssFolder) . 'DRM.css';
                    if($imgPath){
                        $imgFolder = substr($imgPath, 0, strrpos($imgPath, '/'));
                        $imgPath = self::getRelativePathToOther($path, $imgFolder) . NameManipulator::getFileName($imgPath) . '.' . NameManipulator::   getFileExtension($imgPath);

                    }

                    $line = substr($line, 0, $pos) . DRMAccess::getOPFRef($cssFolder) . ($imgPath ? DRMAccess::getOPFImageRef($imgPath) : '') . substr($line, $pos, strlen($line));
                    if($imgPath){
                        $imgFolder = substr($imgPath, 0, strrpos($imgPath, '/'));
                        $imgPath = self::getRelativePathToOther($path, $imgFolder) . NameManipulator::getFileName($imgPath) . '.' . NameManipulator::getFileExtension($imgPath);
                    }
                    $line = substr($line, 0, $pos) . DRMAccess::getOPFRef($cssFolder) . DRMAccess::getOPFImageRef($imgPath) . substr($line, $pos, strlen($line));
                    $replaced = true;
                }
                fputs($write, $line);
            }

        }
        if (!feof($read))
            DefaultMessages::fileOpenError();
        fclose($read);
        fclose($write);
        if ($replaced)
            rename($tempName, $path);
        else
            unlink($tempName);

    }

    private static function createImageFile($destImagePath, $sourceImagePath)
    {
        $destImagePath = $destImagePath . '/' . NameManipulator::getFileName($sourceImagePath) . '.' . NameManipulator::getFileExtension($sourceImagePath);
        copy($sourceImagePath, $destImagePath);
        return $destImagePath;

    }

    private static function createCSSFile($path, $imagePath = null)
    {
        $cssRelPath = substr($path, 0, strrpos($path, '/')) . '/DRM.css';
        if (!file_exists($cssRelPath)) {
            $readCSS = fopen(Constants::cssPath(), 'r');
            $writeCSS = fopen($cssRelPath, 'w');
            while (!feof($readCSS)) {
                $line = fgets($readCSS);
                if ($imagePath)
                    if (stristr($line, "background: none;"))
                        $line = 'background: url("' . $imagePath . '") no-repeat;';
                fputs($writeCSS, $line);
            }
            fclose($readCSS);
            fclose($writeCSS);
        }
    }

    private static function getCSSFolder($bookPath)
    {
        $pathList = FolderManipulator::getFolders($bookPath);
        foreach ($pathList as $i) {
            $tempExt = NameManipulator::getFileExtension($i);
            if (strcmp($tempExt, 'css') == 0 && !is_dir($i)) {
                return substr($i, 0, strrpos($i, '/'));
            }

        }
        $newFolder = self::createMetaFolder($bookPath, "Styles");
        mkdir($newFolder, 0777);
        return $newFolder;
    }

    private static function getImageFolder($bookPath)
    {
        $pathList = FolderManipulator::getFolders($bookPath);
        foreach ($pathList as $i) {
            if(NameManipulator::getFileExtension($i)!='')
                if (exif_imagetype($i) && !is_dir($i)) {
                    return substr($i, 0, strrpos($i, '/'));
                }
        }
        $newFolder = self::createMetaFolder($bookPath, "Images");
        mkdir($newFolder, 0777);
        return $newFolder;
    }

    private static function createMetaFolder($bookPath, $folderName)
    {
        $pathList = FolderManipulator::getFolders($bookPath);
        foreach ($pathList as $i) {
            $tempExt = NameManipulator::getFileExtension($i);
            if (strcmp($tempExt, 'opf') == 0 && !is_dir($i)) {
                return substr($i, 0, strrpos($i, '/') + 1) . $folderName;
            }
        }
        return null;
    }

}