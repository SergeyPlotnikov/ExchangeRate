<?php
/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 06.04.2017
 * Time: 22:30
 */

require_once 'Captcha.php';
session_start();

 if ($_POST['code'] == Captcha::getCode()) {
        echo 'true';
    } else {
        echo Captcha::image();
    }

