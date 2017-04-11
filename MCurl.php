<?php

/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 16.02.2017
 * Time: 22:15
 */
class MCurl
{
    private $mh;
    private $host;
    private $channels = [];

    private function __construct($host)
    {
        $this->mh = curl_multi_init();
        $this->host = $host;
    }

    static function app($host)
    {
        return new self($host);
    }


    function request($currency)
    {
        $active = null;
        do {
            curl_multi_exec($this->mh, $active);
            curl_multi_select($this->mh);
        } while ($active > 0);


        $result = [];
        foreach ($this->channels as $date => $channel) {
            $xml = curl_multi_getcontent($channel);
            if ($xml === '') {
                continue;
            }
            $sxml = new SimpleXMLElement($xml);
            for ($j = 0; $j < count($sxml->exchangerate); $j++) {
                if ((string)$sxml->exchangerate[$j]['currency'] == "{$currency}") {
                    $result[$date] = (string)$sxml->exchangerate[$j]['saleRate'];
                }
            }
            curl_multi_remove_handle($this->mh, $channel);
        }

        return $result;
    }

    function prepare($url, $date)
    {
        $url = $this->makeURL($url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_multi_add_handle($this->mh, $ch);
        $this->channels[$date] = $ch;
    }

    function __destruct()
    {
        curl_multi_close($this->mh);
    }

    private function makeURL($url)
    {
        if ($url[0] != '/') {
            $url = '/' . $url;
        }
        return $this->host . $url;
    }
}