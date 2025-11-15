<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SavedSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        try {
            $events = Event::orderBy('start_time')->get();
            $selectedEvents = Session::get('selected_events', []);
            $currentSchedule = Session::get('current_schedule', null);
            $groupSelections = Session::get('group_selections', []);

            // Получаем статистику через прямой запрос к БД
            $stats = $this->calculateStats($selectedEvents);

            return view('schedule', compact(
                'events',
                'selectedEvents',
                'currentSchedule',
                'groupSelections',
                'stats'
            ));
        } catch (\Exception $e) {
            return view('schedule')->with('error', 'Ошибка загрузки: ' . $e->getMessage());
        }
    }

    private function calculateStats($selectedEvents)
    {
        if (empty($selectedEvents)) {
            return [
                'total_events' => 0,
                'most_popular_location' => '',
                'location_count' => 0,
                'most_popular_time' => '',
                'time_count' => 0,
                'total_duration' => 0
            ];
        }

        $events = Event::whereIn('id', $selectedEvents)->get();

        $locations = [];
        $timeSlots = [];
        $totalDuration = 0;

        foreach ($events as $event) {
            $locations[$event->location] = ($locations[$event->location] ?? 0) + 1;
            $timeSlot = $event->start_time->format('H');
            $timeSlots[$timeSlot] = ($timeSlots[$timeSlot] ?? 0) + 1;
            $totalDuration += $event->duration;
        }

        $mostPopularLocation = empty($locations) ? '' : array_keys($locations, max($locations))[0];
        $mostPopularTime = empty($timeSlots) ? '' : array_keys($timeSlots, max($timeSlots))[0];

        return [
            'total_events' => count($selectedEvents),
            'most_popular_location' => $mostPopularLocation,
            'location_count' => $locations[$mostPopularLocation] ?? 0,
            'most_popular_time' => $mostPopularTime,
            'time_count' => $timeSlots[$mostPopularTime] ?? 0,
            'total_duration' => $totalDuration
        ];
    }

    public function generate(Request $request)
    {
        try {
            $selectedEvents = Session::get('selected_events', []);

            if (empty($selectedEvents)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Выберите хотя бы одно событие!'
                ]);
            }

            $events = Event::whereIn('id', $selectedEvents)
                ->orderBy('start_time')
                ->get();

            $schedules = $this->generateSchedules($events);

            Session::put('current_schedule', $schedules);
            Session::put('available_schedules', $schedules);

            return response()->json([
                'success' => true,
                'schedules' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка генерации: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateSchedules($events)
    {
        $schedules = [];

        // Генерируем 3 варианта расписания
        for ($i = 0; $i < min(3, count($events)); $i++) {
            $schedule = [];
            $lastEvent = null;

            for ($j = $i; $j < count($events); $j++) {
                $currentEvent = $events[$j];

                if (!$lastEvent || !$currentEvent->conflictsWith($lastEvent)) {
                    $schedule[] = [
                        'id' => $currentEvent->id,
                        'name' => $currentEvent->name,
                        'time' => $currentEvent->time,
                        'location' => $currentEvent->location,
                        'duration' => $currentEvent->duration
                    ];
                    $lastEvent = $currentEvent;
                }
            }

            $schedules[] = $schedule;
        }

        // Сортируем по количеству событий
        usort($schedules, function($a, $b) {
            return count($b) - count($a);
        });

        return array_slice($schedules, 0, 3);
    }

    public function saveSelectedSchedule(Request $request)
    {
        try {
            $scheduleIndex = $request->input('schedule_index');
            $availableSchedules = Session::get('available_schedules', []);
            $selectedEvents = Session::get('selected_events', []);

            if (!isset($availableSchedules[$scheduleIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Выбранный вариант расписания не найден!'
                ]);
            }

            $selectedSchedule = $availableSchedules[$scheduleIndex];

            $schedule = SavedSchedule::create([
                'name' => 'Мое расписание от ' . now()->format('d.m.Y H:i'),
                'schedule_data' => [$selectedSchedule],
                'selected_events' => $selectedEvents,
                'type' => 'individual',
                'user_session_id' => Session::getId(),
                'selected_schedule_index' => $scheduleIndex
            ]);

            // Сохраняем выбранное расписание в сессии как текущее
            Session::put('current_schedule', [$selectedSchedule]);
            Session::put('selected_schedule_index', $scheduleIndex);

            return response()->json([
                'success' => true,
                'message' => 'Расписание сохранено!',
                'schedule_id' => $schedule->id,
                'selected_schedule' => $selectedSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка сохранения: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveCustomSchedule(Request $request)
    {
        try {
            $selectedEvents = Session::get('selected_events', []);

            if (empty($selectedEvents)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет выбранных событий для сохранения!'
                ]);
            }

            $events = Event::whereIn('id', $selectedEvents)
                ->orderBy('start_time')
                ->get()
                ->toArray();

            $schedule = SavedSchedule::create([
                'name' => 'Мой список событий от ' . now()->format('d.m.Y H:i'),
                'schedule_data' => [$events],
                'selected_events' => $selectedEvents,
                'type' => 'list',
                'user_session_id' => Session::getId()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Список событий сохранен!',
                'schedule_id' => $schedule->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка сохранения: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSavedSchedules()
    {
        try {
            $schedules = SavedSchedule::where('user_session_id', Session::getId())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($schedules);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка загрузки: ' . $e->getMessage()
            ], 500);
        }
    }

    public function load($id)
    {
        try {
            $schedule = SavedSchedule::findOrFail($id);

            // Убеждаемся, что selected_events является массивом
            $selectedEvents = is_array($schedule->selected_events)
                ? $schedule->selected_events
                : [];

            Session::put('selected_events', $selectedEvents);
            Session::put('current_schedule', $schedule->schedule_data);

            return response()->json([
                'success' => true,
                'message' => 'Расписание загружено!',
                'selected_events' => $selectedEvents,
                'schedule_data' => $schedule->schedule_data,
                'schedule_type' => $schedule->type
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка загрузки: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $schedule = SavedSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Расписание удалено!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления: ' . $e->getMessage()
            ], 500);
        }
    }

    // Сценарий 3: Корректировка на лету
    public function adjust(Request $request)
    {
        try {
            $currentSchedule = Session::get('current_schedule');
            $newEventId = $request->input('event_id');

            if (!$currentSchedule || empty($currentSchedule[0])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет активного расписания для корректировки'
                ]);
            }

            $newEvent = Event::find($newEventId);
            if (!$newEvent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Событие не найдено'
                ]);
            }

            // Добавляем новое событие и перестраиваем расписание
            $eventIds = array_column($currentSchedule[0], 'id');
            $eventIds[] = $newEventId;

            // Обновляем выбранные события в сессии
            Session::put('selected_events', $eventIds);

            $events = Event::whereIn('id', $eventIds)
                ->orderBy('start_time')
                ->get();

            $adjustedSchedule = $this->generateSchedules($events);

            Session::put('current_schedule', $adjustedSchedule);
            Session::put('available_schedules', $adjustedSchedule);

            return response()->json([
                'success' => true,
                'schedules' => $adjustedSchedule,
                'message' => 'Расписание обновлено с учетом нового события!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка корректировки: ' . $e->getMessage()
            ], 500);
        }
    }

// Сценарий 2: Групповое планирование
    public function saveGroupSelection(Request $request)
    {
        try {
            $memberName = $request->input('member_name');
            $selectedEvents = Session::get('selected_events', []);

            if (empty($selectedEvents)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет выбранных событий для сохранения'
                ]);
            }

            $groupSelections = Session::get('group_selections', []);
            $groupSelections[$memberName] = $selectedEvents;

            Session::put('group_selections', $groupSelections);

            return response()->json([
                'success' => true,
                'message' => 'Выбор участника ' . $memberName . ' сохранен!',
                'group_selections' => $groupSelections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка сохранения: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mergeGroupSelections(Request $request)
    {
        try {
            $groupSelections = Session::get('group_selections', []);

            if (empty($groupSelections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нет сохраненных выборов участников группы'
                ]);
            }

            // Объединяем все выбранные события
            $allSelectedEvents = [];
            foreach ($groupSelections as $memberEvents) {
                $allSelectedEvents = array_merge($allSelectedEvents, $memberEvents);
            }

            $allSelectedEvents = array_unique($allSelectedEvents);
            Session::put('selected_events', $allSelectedEvents);

            // Генерируем расписание для всей группы
            $events = Event::whereIn('id', $allSelectedEvents)
                ->orderBy('start_time')
                ->get();

            $groupSchedules = $this->generateSchedules($events);
            Session::put('current_schedule', $groupSchedules);
            Session::put('available_schedules', $groupSchedules);

            // Сохраняем групповое расписание
            $schedule = SavedSchedule::create([
                'name' => 'Групповое расписание от ' . now()->format('d.m.Y H:i'),
                'schedule_data' => $groupSchedules,
                'selected_events' => $allSelectedEvents,
                'type' => 'group',
                'user_session_id' => Session::getId(),
                'group_members' => array_keys($groupSelections)
            ]);

            return response()->json([
                'success' => true,
                'schedules' => $groupSchedules,
                'message' => 'Групповое расписание создано!',
                'total_events' => count($allSelectedEvents)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка создания группового расписания: ' . $e->getMessage()
            ], 500);
        }
    }
}
