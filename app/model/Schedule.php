<?php

namespace RIB\model;

use DateTime;
use Exception;
use RIB\core\Model;

class Schedule extends Model
{

    /**
     * Checking to see if it's time for an alert, if it is, it sends it out
     */
    public function check(): void
    {
        foreach ($this->db->getSendingDailyNow() as $item) {

            $message = $this->db->getMessagePrepared($item['chat_id']);

            // confirm
            $this->db->setScheduleDailyStatusSent($item['schedule_daily_id']);

            if (empty($message)) {
                continue;
            }

            $answer = $message['text'].' /_'.$message['message_id'];


            // if this image
            if (!empty($message['image'])) {
                $img = curl_file_create($_ENV['DIR_FILE'].$message['image'], 'image/jpeg');


                $answer = $this->telegram->sendPhoto(
                  [
                    'chat_id' => $item['chat_id'],
                    'photo' => $img,
                    'caption' => stripslashes($answer)
                  ]
                );

                // if there is an error
//                if (!$answer['ok']) {
//                    \Sentry\captureMessage(
//                      'chat_id: '.$item['chat_id']
//                      .'description: '.$answer['description']
//                      .'error_code: '.$answer['error_code']
//                    );
//                }
                continue;
            }


            // default is text
            $this->telegram->sendMessage(
              [
                'chat_id' => $item['chat_id'],
                'text' => stripslashes($answer),
              ]
            );

        }
    }

    /**
     * Generates the date and time of the alert in mysql format with an offset from the time zone
     * @param  int  $hour_start
     * @param  int  $hour_end
     * @param  int  $time_zone_offset
     * @return string
     * @throws Exception
     */
    public function createDateTimeForSchedule(int $hour_start, int $hour_end, int $time_zone_offset): string
    {
        $date_starting = gmdate('Y-m-d '.rand($hour_start, $hour_end).':'.rand(10, 59).':s');
        $date = new DateTime($date_starting);
        $date->modify('+'.(-1) * $time_zone_offset.' hours');
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Fills out the schedule of notifications for the current day
     * @throws Exception
     */
    public function generate(): void
    {
        // Получим список пользователей, которым необходимо создать расписание сегодня
        foreach ($this->db->getSchedulesNeedToday() as $item) {
            // сколько уведомлений отправлять сегодня
            for ($i = 0; $i < $item['quantity']; $i++) {
                $this->db->addSendingDailyNow(
                  [
                    'chat_id' => $item['chat_id'],
                    'date_time' => $this->createDateTimeForSchedule(
                      $item['hour_start'],
                      $item['hour_end'],
                      $item['time_zone_offset']
                    ),
                    'status_sent' => 0,
                  ]
                );
            }
        }

        // Тут же почистим все старые отправленные сообщения за прошлые дни
        $this->db->cleanSendingDailyOld();
    }
}
