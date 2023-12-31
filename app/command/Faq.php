<?php


namespace RIB\command;

use Telegram;

class Faq
{
    private Telegram $telegram;
    private int $chat_id;

    public function __construct($telegram)
    {
        $this->telegram = $telegram;
        $this->chat_id = $this->telegram->ChatID();
    }

    public function index()
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => "Я твой личный библиотекарь всего того, что даёт вам поддержку, " .
                    "мотивацию, делает сильнее, поднимает настроение. " .
                    "Это могут быть  цитаты, мысли, фото, видео. Присылай всё мне." .
                    PHP_EOL .
                    "Я сохраняю их и отправляю обратно, по одной штуке в день, каждый день, в удобный для тебя интервал." .
                    "Так, каждый день, вы будете получать то, маленькую поддержку, от самого себя."
            ]
        );

        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chat_id,
                'text' => "Поделюсь опытом:" .
                    PHP_EOL .
                    "- Лучше всего добавлять текст, прочтение которого не будет занимать у тебя более минуты - двух, так это не будет тебя сильно отвлекать;" .
                    PHP_EOL .
                    "- Вместо ссылки, лучше выписать основную суть и уже прикрепить ссылку в конце сообщения;" .
                    PHP_EOL .
                    "- Длинные ролики, так же лучше не сохранять, идеальное время 1-2минуты, и так же, выписать суть и в конце прикрепить уже ссылку на видео;" .
                    PHP_EOL .
                    "- Если необходимо, то отправленное сообщение можно отредактировать самым обычным образом, я сохраню все изменения;"
            ]
        );
    }
}
