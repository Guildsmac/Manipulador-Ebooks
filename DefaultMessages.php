<?php
/**
 * Copyright (c) 2019.
 * Developed by Gabriel Sousa
 * @author Gabriel Sousa <gabrielssc.ti@gmail.com>
 * Last modified 16/11/18 21:18.
 *
 */

/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/8/2018
 * Time: 8:24 PM
 */

class DefaultMessages{
    static function zipOpenError(){
        echo "<script> window.alert('NÃO FOI POSSÍVEL ABRIR ARQUIVO'); </script>";

    }
    static function fileOpenError(){
        echo "<script> window.alert('NÃO FOI POSSÍVEL LER ARQUIVO'); </script>";

    }
}