<?php
/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 08.02.2017
 * Time: 22:49
 */
header('Content-type:text/html;charset=utf-8');

include "MCurl.php";
$from = $_POST['from'];
$to = $_POST['to'];
$currency = $_POST['currency'];

$start = strtotime($from);
$end = strtotime($to);
$period = 86400;//кол-во сек в сутках

$curl = MCurl::app('https://api.privatbank.ua/p24api');
for ($i = $start; $i <= $end; $i += $period) {
    $date = date('d.m.Y', $i);
    $curl->prepare("/exchange_rates?date={$date}", $date);
}
$res = $curl->request($currency);

$output = [];
foreach ($res as $date => $cur) {
   $output[] = array(strtotime($date),(float)$cur);
}
echo json_encode($output);


