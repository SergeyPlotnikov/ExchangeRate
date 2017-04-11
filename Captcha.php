<?php

/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 05.04.2017
 * Time: 17:55
 */
class Captcha
{
    private static $captcha = '__captcha__';
    private static $font = 'Sparkly.ttf';
    private static $width = 70;
    private static $height = 70;
    private static $fontSize = 40;
    private static $characterWidth = 40;

    private static function sessionExists()
    {
        return isset($_SESSION);
    }

    //Формируем код каптчи и записываем ее в сессию
    private static function generateCode($length)
    {
        $code = null;
        for ($i = 0; $i < $length; $i++) {
            $code .= self::getRandom();
        }
        self::$width = $length * self::$characterWidth;
        if (self::sessionExists()) {
            $_SESSION[self::$captcha] = $code;
        }

        return $code;
    }

    //Получение случайной цифры, буквы в нижнем и верхнем регистре для каптчи
    private static function getRandom()
    {
        $type = rand(0, 2);
        switch ($type) {
            case 2:
                //Англ буквы в верхнем регистре
                $random = chr(rand(65, 90));
                break;
            case 1:
                //Англ. буквы в нижнем регистре
                $random = chr(rand(97, 122));
                break;
            default:
                //Число
                $random = rand(0, 9);
        }
        return $random;
    }

    private static function getWidth()
    {
        return self::$width;
    }

    private static function getHeight()
    {
        return self::$height;
    }

    public static function image()
    {
        $length = 6;
        $code = self::generateCode($length);
        ob_start();
        $image = imagecreatetruecolor(self::getWidth(), self::getHeight());
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, self::getWidth(), self::getHeight(), $white);
        //Добавим разных точек на нашу каптчу
        for ($dot = 0; $dot < 2000; $dot++) {
            $red = rand(0, 255);
            $green = rand(0, 255);
            $blue = rand(0, 255);
            $dotColor = imagecolorallocate($image, $red, $green, $blue);
            //Координаты линии
            $x1 = rand(0, self::getWidth());
            $y1 = rand(0, self::getHeight());
            $x2 = $x1 + 1;
            $y2 = $y1 + 1;
            imageline($image, $x1, $y1, $x2, $y2, $dotColor);
        }
        //Добавляем цифры и буквы на капчу
        for ($start = -$length; $start < 0; $start++) {
            $color = imagecolorallocate($image, rand(0, 177), rand(0, 177), rand(0, 177));
            $character = substr($code, $start, 1);
            $x = ($start + 6) * self::$characterWidth;
            $y = rand(self::getHeight() - 20, self::getHeight() - 10);
            imagettftext($image, self::$fontSize, 0, $x, $y, $color, self::$font, $character);
        }
        imagepng($image);
        imagedestroy($image);
        $source = ob_get_contents();
        ob_end_clean();

        return "data:image/png;base64," . base64_encode($source);
    }

    //Получение каптчи
    public static function getCode()
    {
        if (self::sessionExists()) {
            return $_SESSION[self::$captcha];
        }
    }
}