<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EventController extends Controller
{
    public function toggle(Request $request)
    {
        try {
            $eventId = $request->input('event_id');
            $selectedEvents = Session::get('selected_events', []);

            if (in_array($eventId, $selectedEvents)) {
                $selectedEvents = array_diff($selectedEvents, [$eventId]);
            } else {
                $selectedEvents[] = $eventId;
            }

            Session::put('selected_events', array_values($selectedEvents));

            // Обновляем статистику события
            $this->updateEventStats($eventId);

            return response()->json([
                'success' => true,
                'selected_count' => count($selectedEvents),
                'selected_events' => $selectedEvents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStats()
    {
        try {
            $selectedEvents = Session::get('selected_events', []);
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

            return response()->json([
                'total_events' => count($selectedEvents),
                'most_popular_location' => $mostPopularLocation,
                'location_count' => $locations[$mostPopularLocation] ?? 0,
                'most_popular_time' => $mostPopularTime,
                'time_count' => $timeSlots[$mostPopularTime] ?? 0,
                'total_duration' => $totalDuration
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка получения статистики: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clear(Request $request)
    {
        try {
            Session::forget('selected_events');
            Session::forget('event_stats');

            return response()->json([
                'success' => true,
                'message' => 'Выбор очищен'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateEventStats($eventId)
    {
        $stats = Session::get('event_stats', []);
        $stats[$eventId] = ($stats[$eventId] ?? 0) + 1;
        Session::put('event_stats', $stats);
    }
}
