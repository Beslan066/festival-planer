<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run()
    {
        $events = [
            [
                'name' => 'Открытие фестиваля',
                'start_time' => Carbon::today()->setTime(10, 0),
                'end_time' => Carbon::today()->setTime(11, 0),
                'location' => 'Главная сцена',
                'duration' => 60,
                'category' => 'церемония'
            ],
            [
                'name' => 'Мастер-класс по живописи',
                'start_time' => Carbon::today()->setTime(11, 0),
                'end_time' => Carbon::today()->setTime(12, 30),
                'location' => 'Павильон А',
                'duration' => 90,
                'category' => 'мастер-класс'
            ],
            [
                'name' => 'Концерт джаз-банда',
                'start_time' => Carbon::today()->setTime(11, 30),
                'end_time' => Carbon::today()->setTime(13, 0),
                'location' => 'Главная сцена',
                'duration' => 90,
                'category' => 'концерт'
            ],
            [
                'name' => 'Лекция о современном искусстве',
                'start_time' => Carbon::today()->setTime(12, 0),
                'end_time' => Carbon::today()->setTime(13, 0),
                'location' => 'Конференц-зал',
                'duration' => 60,
                'category' => 'лекция'
            ],
            [
                'name' => 'Фуд-корт: дегустация',
                'start_time' => Carbon::today()->setTime(13, 0),
                'end_time' => Carbon::today()->setTime(14, 0),
                'location' => 'Фуд-корт',
                'duration' => 60,
                'category' => 'еда'
            ],
            [
                'name' => 'Выставка скульптур',
                'start_time' => Carbon::today()->setTime(13, 30),
                'end_time' => Carbon::today()->setTime(15, 0),
                'location' => 'Павильон Б',
                'duration' => 90,
                'category' => 'выставка'
            ],
            [
                'name' => 'Интерактивный спектакль',
                'start_time' => Carbon::today()->setTime(14, 0),
                'end_time' => Carbon::today()->setTime(15, 30),
                'location' => 'Главная сцена',
                'duration' => 90,
                'category' => 'театр'
            ],
            [
                'name' => 'Воркшоп по фотографии',
                'start_time' => Carbon::today()->setTime(15, 0),
                'end_time' => Carbon::today()->setTime(16, 30),
                'location' => 'Павильон А',
                'duration' => 90,
                'category' => 'мастер-класс'
            ],
            [
                'name' => 'Акустический концерт',
                'start_time' => Carbon::today()->setTime(16, 0),
                'end_time' => Carbon::today()->setTime(17, 0),
                'location' => 'Малая сцена',
                'duration' => 60,
                'category' => 'концерт'
            ],
            [
                'name' => 'Закрытие фестиваля',
                'start_time' => Carbon::today()->setTime(17, 30),
                'end_time' => Carbon::today()->setTime(18, 30),
                'location' => 'Главная сцена',
                'duration' => 60,
                'category' => 'церемония'
            ],
            [
                'name' => 'Выставка цифрового искусства',
                'start_time' => Carbon::today()->setTime(10, 30),
                'end_time' => Carbon::today()->setTime(12, 0),
                'location' => 'Павильон В',
                'duration' => 90,
                'category' => 'выставка'
            ],
            [
                'name' => 'Панельная дискуссия: Искусство будущего',
                'start_time' => Carbon::today()->setTime(12, 30),
                'end_time' => Carbon::today()->setTime(13, 30),
                'location' => 'Конференц-зал',
                'duration' => 60,
                'category' => 'лекция'
            ],
            [
                'name' => 'Мастер-класс по каллиграфии',
                'start_time' => Carbon::today()->setTime(13, 0),
                'end_time' => Carbon::today()->setTime(14, 30),
                'location' => 'Павильон С',
                'duration' => 90,
                'category' => 'мастер-класс'
            ],
            [
                'name' => 'Выступление уличных театров',
                'start_time' => Carbon::today()->setTime(14, 30),
                'end_time' => Carbon::today()->setTime(15, 30),
                'location' => 'Открытая площадка',
                'duration' => 60,
                'category' => 'театр'
            ],
            [
                'name' => 'Воркшоп по созданию комиксов',
                'start_time' => Carbon::today()->setTime(15, 30),
                'end_time' => Carbon::today()->setTime(17, 0),
                'location' => 'Павильон А',
                'duration' => 90,
                'category' => 'мастер-класс'
            ]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }

        $this->command->info('Успешно создано ' . count($events) . ' событий фестиваля!');
    }
}
