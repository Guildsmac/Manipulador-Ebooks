<?php
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/12/2018
 * Time: 8:19 PM
 */

class BookInformations{
    public static function getInformations($opfPath){
        $informations = array();
        $file = fopen($opfPath, 'r');
        $informations['titulo']=null;
        $informations['autor']=null;
        $informations['lingua'] = null;
        $informations['data'] = null;
        $informations['publicadora'] = null;
        $informations['direito'] = null;
        $informations['descricao'] = null;

        while(!feof($file)){
            $line = fgets($file);
            if(stristr($line, '<dc:title'))
                $informations['titulo'] = trim(strip_tags($line));
            if(stristr($line, '<dc:creator'))
                $informations['autor'] = trim(strip_tags($line));
            if(stristr($line, '<dc:language'))
                $informations['lingua'] = trim(strip_tags($line));
            if(stristr($line, '<dc:date'))
                $informations['data'] = trim(strip_tags($line));
            if(stristr($line, '<dc:publisher'))
                $informations['publicadora'] = trim(strip_tags($line));
            if(stristr($line, '<dc:rights'))
                $informations['direito'] = trim(strip_tags($line));
            if(stristr($line, '<dc:description'))
                $informations['descricao'] = strip_tags(htmlspecialchars_decode(trim($line)));


        }
        return $informations;
    }

    public static function toStringInformations($informations){
        return ("Título - " . ($informations['titulo']!=null ? $informations['titulo'] : 'Não identificado') . '<br>' .
               "Autor - " . ($informations['autor']!=null ? $informations['autor'] : 'Não identificado') . '<br>' .
               "Língua - " . ($informations['lingua']!=null ? $informations['lingua'] : 'Não identificada') . '<br>' .
               "Data de publicação - " . ($informations['data']!=null ? $informations['data'] : 'Não identificado') . '<br>' .
               "Publicadora - " . ($informations['publicadora']!=null ? $informations['publicadora'] : 'Não identificada') . '<br>' .
               "Direitos reservados - " . ($informations['direito']!=null ? $informations['direito'] : 'Não identificado') . '<br>' .
               "Descrição - " . ($informations['descricao']!=null ? $informations['descricao'] : 'Não identificado') . '<br>');
    }

}