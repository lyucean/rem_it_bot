<?php

require_once __DIR__ . '/vendor/autoload.php';

use RIB\model\Processing;
use RIB\model\Schedule;

// Проверяем расписание, нужно ли кому-то отправить сообщение
(new  Schedule())->check();

// Ответ на все сообщения раз в секунду
(new Processing())->check();

// Давайте создадим список рассылки на день.
(new  Schedule())->generate();
