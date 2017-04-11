<?php
require_once 'Captcha.php';
session_start();
if (isset($_POST['rCaptcha'])) {
    echo Captcha::image();
}