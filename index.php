<?php

/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 04.04.2017
 * Time: 21:15
 */
header('Content-type:text/html;charset=utf-8');
require_once 'Captcha.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <title>Курс валют</title>
    <script src="jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <link rel="stylesheet" href="jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
    <script src="jquery-ui/jquery-ui.js"></script>

    <script>
        $(function () {
            //Проверка каптчи
            $('#check').on('click', function () {
                $.ajax({
                    url: 'captchaValidate',
                    type: 'post',
                    data: ({code: $('#code').val()}),
                    success: function (data) {
                        if (data === 'true') {
                            $('#submit').prop('disabled', false);
                            $('#captchaDiv').remove();
                        } else {
                            $('#captcha').attr('src', data);
                            $('#code').val('');
                        }
                    }
                });
            });

            //Обновление каптчи
            $('#captcha').on('click', function (e) {
                $.ajax({
                    data: ({rCaptcha: true}),
                    type: 'post',
                    url: 'refreshCaptcha.php',
                    success: function (data) {
                        e.target.src = data;
                        $('#code').val('');
                    }
                });
            });


            $.datepicker.setDefaults($.datepicker.regional["ua"]);
            $("#from").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "dd.mm.yy",
                dayNames: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                dayNamesMin: ['Вскр', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                firstDay: 1,
                minDate: new Date(2014, 1 - 1, 1),
                maxDate: "-3d",
                monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                monthNamesShort: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']

            });
            $("#to").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "dd.mm.yy",
                dayNames: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                dayNamesMin: ['Вскр', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                firstDay: 1,
                minDate: new Date(2014, 1 - 1, 1),
                maxDate: "-3d",
                monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                monthNamesShort: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
            });

            $('#submit').on('click', function () {
                var curArray = $('#form').serialize();
                $.ajax({
                    url: 'parse.php',
                    type: 'post',
                    data: curArray,
                    success: function (data) {
                        data = JSON.parse(data);
                        //Так как время на datetime оси хранится в мс, то приведем к сек
                        var points = [];
                        for (var i = 0; i < data.length; i++) {
                            points[i] = [];
                            for (var j = 0; j < data[i].length; j++) {
                                if (j == 0) {
                                    data[i][j] *= 1000;
                                }
                                points[i][j] = data[i][j];
                            }
                        }
                        Highcharts.setOptions({
                            global: {
                                useUTC: false
                            },
                            lang: {
                                shortMonths: ['Янв', 'Фев', 'Мрт', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сент', 'Окт', 'Нбр', 'Дек'],
                                shortWeekdays: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                                weekdays: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота']
                            }

                        });
                        Highcharts.chart('container', {
                            chart: {
                                zoomType: 'x'
                            },
                            title: {
                                text: 'График валют за период ' + $('#from').val() + ' - ' + $('#to').val()
                            },
                            yAxis: {
                                tickInterval: 0.5,
                                title: {
                                    text: 'Курс валюты'
                                }

                            },
                            xAxis: {
                                type: 'datetime',
                                startOnTick: true,
                                minZoom: 10,
                                labels: {
                                    rotation: 45,
                                    formatter: function () {
                                        return Highcharts.dateFormat('%d.%b.%Y', this.value);
                                    }
                                }
                            },
                            credits: {
                                enabled: false
                            },
                            series: [{
                                name: 'Курс валюты',
                                data: points
                            }]
                        });

                    }
                });
            });
        });
    </script>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <form name='curForm' id='form' action="parse.php" method="post">
                <select name="currency" id="cur">
                    <option value="0">Выберите валюту</option>
                    <?php
                    $currency = ['USD', 'EUR', 'RUB', 'CHF', 'GBP', 'ILS', 'CZK', 'PLZ', 'NOK', 'JPY'];
                    for ($i = 0; $i < count($currency); $i++) {
                        echo "<option value='{$currency[$i]}'> $currency[$i] </option>";
                    }
                    ?>
                </select>
                <div id="date">
                    <p>Выберите промежуток времени: </p>
                    <label for="from">C </label>
                    <input type="text" id="from" name="from">
                    <label for="to">По </label>
                    <input type="text" id="to" name="to" value="">
                </div>
                <input type="button" id="submit" name="send" disabled value="Узнать курс валюты">
            </form>

        </div>
    </div>
    <div class="row">
        <div id="captchaDiv" class="col-sm-6">
            <p>Введите буквы с изображения</p>
            <form id="captchaForm" autocomplete="off">
                <input id='code' type="text" name="code">
                <img id="captcha" src="<?= Captcha::image(); ?>" alt="" title="Нажмите для обновления">
                <input type="button" id="check" value="Подтвердить">
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div id="result"></div>
            <div id="container" style="height: 400px"></div>
        </div>
    </div>
</div>

</body>
</html>

