<?php
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

        if ($imgArray)
            $bookImagePath = self::createImageFile($imageFolder, $imgArray->getImageUrl());
        $imagePathRelativeToCSS =  self::getCSSPathRelativeToHTML($cssFolder . '/DRM.css', $imageFolder) . '/' . NameManipulator::getFileName($bookImagePath) . '.'. NameManipulator::getFileExtension($bookImagePath);

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
        return ['xhtml', 'html', 'css', 'opf'];

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

    private static function getCSSPathRelativeToHTML($htmlPath, $cssPath)
    {
        $originalHtmlPath = $htmlPath;
        $htmlPath = substr($htmlPath, 0, strrpos($htmlPath, '/'));
        $htmlSlashCount = substr_count($htmlPath, '/');
        $cssSlashCount = substr_count($cssPath, '/');
        if ($cssSlashCount > $htmlSlashCount) {
            return substr($cssPath, strrpos($originalHtmlPath, '/') + 1, strlen($cssPath));
        } else if ($cssSlashCount == $htmlSlashCount) {
            $temp = substr($cssPath, 0, strrpos($htmlPath, '/'));
            $multiplier = $htmlSlashCount - substr_count($temp, '/');
            return str_replace($temp . '/', str_repeat('../', $multiplier), $cssPath);
        } else
            return '';
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
                    $cssFolder = DRMInserter::getCSSPathRelativeToHTML($path, $cssFolder);
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
                    $cssFolder = substr($cssFolder, strrpos($path, '/') + 1, strlen($cssFolder)) . '/DRM.css';
                    $imgFolder = substr($imgPath, 0, strrpos($imgPath, '/'));
                    $imgPath = self::getCSSPathRelativeToHTML($path, $imgFolder) . '/' . NameManipulator::getFileName($imgPath) . '.' . NameManipulator::getFileExtension($imgPath);

                    $line = substr($line, 0, $pos) . DRMAccess::getOPFRef($cssFolder) . DRMAccess::getOPFImageRef($imgPath)  . substr($line, $pos, strlen($line));
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
            $readCSS = fopen(Constants::$cssPath, 'r');
            $writeCSS = fopen($cssRelPath, 'w');
            while (!feof($readCSS)) {
                $line = fgets($readCSS);
                if($imagePath)
                    if(stristr($line, "background: none;"))
                        $line = 'background: url("'. $imagePath . '") no-repeat;';
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
        return self::createMetaFolder($bookPath, "Styles");
    }

    private static function getImageFolder($bookPath)
    {
        $pathList = FolderManipulator::getFolders($bookPath);
        foreach ($pathList as $i) {
            $tempExt = NameManipulator::getFileExtension($i);
            if (strcmp($tempExt, 'jpg') == 0 && !is_dir($i)) {
                return substr($i, 0, strrpos($i, '/'));
            }
        }
        return self::createMetaFolder($bookPath, "Images");
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