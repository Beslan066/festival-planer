<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Фестиваль - Планировщик расписания</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-radius: 12px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }

        .app-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .app-description {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .tab-container {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .tab {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius);
            cursor: pointer;
            color: white;
            font-weight: 600;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .tab:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .tab.active {
            background: white;
            color: var(--primary);
            border-color: white;
            box-shadow: var(--shadow);
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        

        .section-card h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-card h2 i {
            color: var(--secondary);
        }

        .comparison-info {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .comparison-info p {
            margin: 5px 0;
            font-weight: 500;
        }

        #selectionStats {
            font-weight: 600;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 20px;
        }

        .events-list {
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .event-item {
            padding: 15px;
            border: 2px solid #e9ecef;
            margin: 8px 0;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .event-item:hover {
            border-color: var(--primary);
            background-color: #f8f9ff;
        }

        .event-item.selected {
            border-color: var(--success);
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
        }

        .event-item input {
            margin-top: 3px;
            transform: scale(1.2);
        }

        .event-info {
            flex: 1;
        }

        .event-info strong {
            color: var(--primary);
            font-size: 1.1rem;
            display: block;
            margin-bottom: 5px;
        }

        .event-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .time-slot {
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-left: 4px solid var(--primary);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .time-slot:hover {
            transform: translateX(5px);
        }

        .time-slot.selected {
            border-left-color: var(--success);
            background: linear-gradient(135deg, #d4edda, #e8f5e8);
        }

        .time-slot.saved {
            border-left-color: var(--info);
            background: linear-gradient(135deg, #e7f3ff, #f0f8ff);
        }

        .buttons-container {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .save-btn {
            background: linear-gradient(135deg, var(--success), var(--primary));
            color: white;
        }

        .clear-btn {
            background: linear-gradient(135deg, var(--danger), #e63946);
            color: white;
        }

        .load-btn {
            background: linear-gradient(135deg, var(--info), #4895ef);
            color: white;
        }

        .select-btn {
            background: linear-gradient(135deg, #38b000, #2d7d46);
            color: white;
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .delete-btn {
            background: linear-gradient(135deg, var(--danger), #e63946);
            color: white;
        }

        .scenario-content {
            margin-top: 20px;
        }

        .hidden {
            display: none !important;
        }

        /* Групповое планирование */
        .group-management {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .group-management h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }

        .member-input {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .member-input input {
            flex: 1;
            min-width: 200px;
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .group-members {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .group-member {
            background: white;
            padding: 12px;
            border-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .clear-group-btn {
            background: linear-gradient(135deg, var(--warning), #f3722c);
            color: white;
            margin-top: 10px;
        }

        /* Расписания */
        .schedule-selection {
            margin-bottom: 25px;
        }

        .schedule-option {
            border: 2px solid #e9ecef;
            padding: 20px;
            margin: 15px 0;
            border-radius: var(--border-radius);
            transition: var(--transition);
            background: white;
        }

        .schedule-option:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.1);
        }

        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .schedule-header h4 {
            color: var(--primary);
            margin: 0;
        }

        .selection-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .saved-schedule-display {
            border: 3px solid var(--success);
            padding: 25px;
            border-radius: var(--border-radius);
            background: linear-gradient(135deg, #f8fff9, #e8f5e8);
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.2);
        }

        /* Управление сохраненными расписаниями */
        .schedules-management {
            margin-top: 20px;
        }

        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .schedules-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .saved-item {
            display: flex;
            align-items: center;
            border: 2px solid #e9ecef;
            padding: 18px;
            margin: 12px 0;
            border-radius: var(--border-radius);
            background: white;
            transition: var(--transition);
            gap: 15px;
        }

        .saved-item:hover {
            border-color: var(--primary);
            transform: translateX(5px);
        }

        .saved-item.selected {
            border-color: var(--danger);
            background: linear-gradient(135deg, #ffeaea, #fff5f5);
        }

        .schedule-checkbox {
            display: flex;
            align-items: center;
        }

        .schedule-checkbox input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .schedule-info {
            flex: 1;
        }

        .schedule-info h4 {
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .schedule-meta {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .schedule-actions {
            display: flex;
            gap: 8px;
        }

        .schedule-actions button {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .bulk-actions {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .select-all-btn {
            background: linear-gradient(135deg, var(--info), #4895ef);
            color: white;
        }

        .deselect-all-btn {
            background: linear-gradient(135deg, var(--gray), #6c757d);
            color: white;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            header h1 {
                font-size: 2rem;
            }

            .container {
                gap: 15px;
            }

            .section-card {
                padding: 20px;
            }

            .schedule-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .management-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .saved-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .schedule-actions {
                width: 100%;
                justify-content: space-between;
            }

            .buttons-container {
                flex-direction: column;
            }

            .buttons-container button {
                width: 100%;
                justify-content: center;
            }
        }

        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Скорллбар */
        .events-list::-webkit-scrollbar,
        .schedules-list::-webkit-scrollbar {
            width: 6px;
        }

        .events-list::-webkit-scrollbar-track,
        .schedules-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .events-list::-webkit-scrollbar-thumb,
        .schedules-list::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }

        .events-list::-webkit-scrollbar-thumb:hover,
        .schedules-list::-webkit-scrollbar-thumb:hover {
            background: var(--secondary);
        }
    </style>
</head>
<body>
<div class="app-container">
    <header>
        <h1><i class="fas fa-calendar-alt"></i> Организатор расписания фестиваля</h1>
        <p class="app-description">
            Выберите сценарий использования и создайте оптимальное расписание мероприятий.
        </p>
    </header>

    <div class="tab-container">
        <button class="tab active" onclick="showScenario(1)">
            <i class="fas fa-user"></i> Индивидуальное планирование
        </button>
        <button class="tab" onclick="showScenario(2)">
            <i class="fas fa-users"></i> Групповое планирование
        </button>
        <button class="tab" onclick="showScenario(3)">
            <i class="fas fa-sync-alt"></i> Корректировка на лету
        </button>
    </div>

    <div class="container">
        <!-- Левая колонка - События -->
        <div class="section-card">
            <h2><i class="fas fa-list"></i> События фестиваля</h2>
            <div class="comparison-info">
                <p>Выбрано событий: <span id="selectedCount">{{ $stats['total_events'] ?? 0 }}</span> из <span id="totalCount">{{ count($events) }}</span></p>
                <p>Статистика: <span id="selectionStats">
                    @if($stats['total_events'] > 0)
                            {{ $stats['most_popular_location'] }} ({{ $stats['location_count'] }}),
                            в {{ $stats['most_popular_time'] }}:00 ({{ $stats['time_count'] }}),
                            всего {{ $stats['total_duration'] }} мин
                        @else
                            События не выбраны
                        @endif
                </span></p>
            </div>

            <div class="events-list" id="eventsList">
                @foreach($events as $event)
                    <div class="event-item {{ in_array($event->id, $selectedEvents) ? 'selected' : '' }}"
                         onclick="toggleEvent({{ $event->id }})">
                        <input type="checkbox" id="event-{{ $event->id }}"
                            {{ in_array($event->id, $selectedEvents) ? 'checked' : '' }}>
                        <div class="event-info">
                            <strong>{{ $event->name }}</strong>
                            <div class="event-meta">
                                <span><i class="fas fa-clock"></i> {{ $event->time }}</span>
                                <span><i class="fas fa-map-marker-alt"></i> {{ $event->location }}</span>
                                <span><i class="fas fa-hourglass-half"></i> {{ $event->duration }} мин</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Сценарий 1: Индивидуальное планирование -->
            <div id="scenario1" class="scenario-content">
                <div class="buttons-container">
                    <button onclick="generateSchedules()" class="save-btn">
                        <i class="fas fa-magic"></i> Сгенерировать расписания
                    </button>
                    <button onclick="saveAsList()" class="save-btn">
                        <i class="fas fa-save"></i> Сохранить как список
                    </button>
                    <button onclick="clearSelection()" class="clear-btn">
                        <i class="fas fa-trash"></i> Очистить выбор
                    </button>
                </div>
            </div>

            <!-- Сценарий 2: Групповое планирование -->
            <div id="scenario2" class="scenario-content hidden">
                <div class="group-management">
                    <h3><i class="fas fa-users-cog"></i> Управление группой</h3>
                    <div class="member-input">
                        <input type="text" id="memberName" placeholder="Введите имя участника">
                        <button onclick="saveMemberSelection()" class="save-btn">
                            <i class="fas fa-user-plus"></i> Сохранить выбор
                        </button>
                    </div>
                    <button onclick="clearGroupSelection()" class="clear-group-btn">
                        <i class="fas fa-users-slash"></i> Очистить группу
                    </button>
                </div>
                <div id="groupMembersList">
                    <h4><i class="fas fa-user-friends"></i> Участники группы:</h4>
                    <div class="group-members">
                        @foreach($groupSelections as $member => $events)
                            <div class="group-member">
                                <span>{{ $member }}</span>
                                <span class="badge">{{ count($events) }} событий</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="buttons-container" style="margin-top: 20px;">
                    <button onclick="mergeGroupSelections()" class="load-btn">
                        <i class="fas fa-object-group"></i> Создать групповое расписание
                    </button>
                </div>
            </div>

            <!-- Сценарий 3: Корректировка на лету -->
            <div id="scenario3" class="scenario-content hidden">
                <div class="adjustment-controls">
                    <h3><i class="fas fa-sync-alt"></i> Корректировка расписания</h3>
                    <p>Выберите дополнительное событие для добавления в текущее расписание:</p>
                    <button onclick="adjustSchedule()" class="save-btn">
                        <i class="fas fa-redo"></i> Обновить расписание
                    </button>
                </div>
            </div>
        </div>

        <!-- Правая колонка - Расписания -->
        <div class="section-card">
            <h2><i class="fas fa-calendar-check"></i> Рекомендуемые расписания</h2>

            <div class="buttons-container">
                <button onclick="saveAsList()" class="save-btn">
                    <i class="fas fa-list-alt"></i> Сохранить как список
                </button>
                <button onclick="loadSavedSchedules()" class="load-btn">
                    <i class="fas fa-archive"></i> Мои сохранения
                </button>
            </div>

            <div id="schedulesList">
                @if(isset($currentSchedule) && count($currentSchedule) > 0)
                    @if(count($currentSchedule) > 1)
                        <div class="schedule-selection">
                            <h3>Выберите один вариант расписания:</h3>
                            @foreach($currentSchedule as $index => $schedule)
                                <div class="schedule-option" id="scheduleOption{{ $index }}">
                                    <div class="schedule-header">
                                        <h4>Вариант {{ $index + 1 }} ({{ count($schedule) }} событий)</h4>
                                        <button onclick="selectSchedule({{ $index }})" class="select-btn">
                                            <i class="fas fa-check"></i> Выбрать этот вариант
                                        </button>
                                    </div>
                                    <div class="schedule-content">
                                        @foreach($schedule as $event)
                                            <div class="time-slot">
                                                <strong>{{ $event['name'] }}</strong><br>
                                                <span><i class="fas fa-clock"></i> {{ $event['time'] }}</span> |
                                                <span><i class="fas fa-map-marker-alt"></i> {{ $event['location'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="selectedScheduleSection" style="display: none;">
                            <h3>✅ Ваше выбранное расписание</h3>
                            <div id="selectedScheduleContent"></div>
                            <div class="selection-actions">
                                <button onclick="saveSelectedSchedule()" class="save-btn">
                                    <i class="fas fa-save"></i> Сохранить это расписание
                                </button>
                                <button onclick="cancelSelection()" class="clear-btn">
                                    <i class="fas fa-times"></i> Выбрать другой вариант
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="saved-schedule-display">
                            <h3><i class="fas fa-check-circle"></i> Ваше сохраненное расписание</h3>
                            @foreach($currentSchedule[0] as $event)
                                <div class="time-slot saved">
                                    <strong>{{ $event['name'] }}</strong><br>
                                    <span><i class="fas fa-clock"></i> {{ $event['time'] }}</span> |
                                    <span><i class="fas fa-map-marker-alt"></i> {{ $event['location'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p style="text-align: center; color: var(--gray); padding: 40px;">
                        <i class="fas fa-calendar-plus fa-2x" style="margin-bottom: 15px; display: block;"></i>
                        Сгенерируйте расписания, чтобы увидеть варианты
                    </p>
                @endif
            </div>

            <div id="savedSchedulesSection" style="display: none;">
                <h3><i class="fas fa-archive"></i> Мои сохраненные расписания и списки</h3>

                <div class="bulk-actions" id="bulkActions">
                    <button onclick="selectAllSchedules()" class="select-all-btn">
                        <i class="fas fa-check-double"></i> Выбрать все
                    </button>
                    <button onclick="deselectAllSchedules()" class="deselect-all-btn">
                        <i class="fas fa-times-circle"></i> Снять выбор
                    </button>
                </div>

                <div id="savedSchedulesList"></div>
            </div>
        </div>
    </div>
</div>


<script>
    let currentScenario = 1;
    let selectedScheduleIndex = null;
    let availableSchedules = [];
    let selectedSchedules = new Set();

    // Функция для обработки ошибок JSON
    async function handleResponse(response) {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Ожидался JSON, но получен: ${text.substring(0, 100)}`);
        }
        return response.json();
    }

    // Инициализация страницы
    async function initializePage() {
        try {
            const response = await fetch('/events/current-state');
            const data = await handleResponse(response);

            if (data.success) {
                updateUIFromSession(data);
            }
        } catch (error) {
            console.error('Error initializing page:', error);
        }
    }

    // Функция для обновления UI на основе данных сессии
    function updateUIFromSession(data) {
        const selectedEvents = data.selected_events || [];
        document.querySelectorAll('.event-item').forEach(item => {
            const eventId = parseInt(item.querySelector('input').id.replace('event-', ''));
            const isSelected = selectedEvents.includes(eventId);
            item.classList.toggle('selected', isSelected);
            item.querySelector('input').checked = isSelected;
        });

        document.getElementById('selectedCount').textContent = selectedEvents.length;
        updateStats();

        const currentSchedule = data.current_schedule || [];
        if (currentSchedule.length > 0 && currentSchedule[0].length > 0) {
            displaySingleSchedule(currentSchedule[0]);
        } else {
            document.getElementById('schedulesList').innerHTML = '<p>Сгенерируйте расписания, чтобы увидеть варианты</p>';
        }
    }

    function showScenario(scenario) {
        currentScenario = scenario;
        document.querySelectorAll('.scenario-content').forEach(el => {
            el.classList.add('hidden');
        });
        document.getElementById('scenario' + scenario).classList.remove('hidden');

        document.querySelectorAll('.tab').forEach((tab, index) => {
            if (index + 1 === scenario) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
    }

    async function toggleEvent(eventId) {
        try {
            const response = await fetch('/events/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ event_id: eventId })
            });

            const data = await handleResponse(response);

            if (data.success) {
                const eventElement = document.querySelector(`#event-${eventId}`).parentElement;
                eventElement.classList.toggle('selected');
                document.getElementById('selectedCount').textContent = data.selected_count;
                updateStats();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка: ' + error.message);
        }
    }

    async function updateStats() {
        try {
            const response = await fetch('/events/stats');
            const data = await handleResponse(response);

            let statsText = 'События не выбраны';
            if (data.total_events > 0) {
                statsText = `${data.most_popular_location} (${data.location_count}), в ${data.most_popular_time}:00 (${data.time_count}), всего ${data.total_duration} мин`;
            }

            document.getElementById('selectionStats').textContent = statsText;
        } catch (error) {
            console.error('Error updating stats:', error);
        }
    }

    async function generateSchedules() {
        try {
            const response = await fetch('/schedules/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await handleResponse(response);

            if (data.success) {
                displayScheduleSelection(data.schedules);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка генерации расписаний: ' + error.message);
        }
    }

    function displayScheduleSelection(schedules) {
        const schedulesList = document.getElementById('schedulesList');
        availableSchedules = schedules;

        if (!schedules || schedules.length === 0) {
            schedulesList.innerHTML = '<p>Не удалось составить расписание. Попробуйте выбрать другие события.</p>';
            return;
        }

        let html = `
            <div class="schedule-selection">
                <h3>Выберите один вариант расписания:</h3>
        `;

        schedules.forEach((schedule, index) => {
            const scheduleLength = Array.isArray(schedule) ? schedule.length : 0;
            html += `
                <div class="schedule-option" id="scheduleOption${index}">
                    <div class="schedule-header">
                        <h4>Вариант ${index + 1} (${scheduleLength} событий)</h4>
                        <button onclick="selectSchedule(${index})" class="select-btn">
                            Выбрать этот вариант
                        </button>
                    </div>
                    <div class="schedule-content">
            `;

            if (Array.isArray(schedule)) {
                schedule.forEach(event => {
                    html += `
                        <div class="time-slot">
                            <strong>${event.name}</strong><br>
                            <span class="time">${event.time}</span> |
                            <span class="location">${event.location}</span>
                        </div>
                    `;
                });
            }

            html += `
                    </div>
                </div>
            `;
        });

        html += `</div>`;

        html += `
            <div id="selectedScheduleSection" style="display: none;">
                <h3>✅ Ваше выбранное расписание</h3>
                <div id="selectedScheduleContent"></div>
                <div class="selection-actions">
                    <button onclick="saveSelectedSchedule()" class="save-btn">Сохранить это расписание</button>
                    <button onclick="cancelSelection()" class="clear-btn">Выбрать другой вариант</button>
                </div>
            </div>
        `;

        schedulesList.innerHTML = html;
    }

    function displaySingleSchedule(schedule) {
        const schedulesList = document.getElementById('schedulesList');

        if (!schedule || !Array.isArray(schedule)) {
            schedulesList.innerHTML = '<p>Ошибка отображения расписания</p>';
            return;
        }

        let html = `
            <div class="saved-schedule-display">
                <h3>✅ Ваше сохраненное расписание</h3>
        `;

        schedule.forEach(event => {
            html += `
                <div class="time-slot saved">
                    <strong>${event.name}</strong><br>
                    <span class="time">${event.time}</span> |
                    <span class="location">${event.location}</span>
                </div>
            `;
        });

        html += `</div>`;

        schedulesList.innerHTML = html;
    }

    function selectSchedule(index) {
        selectedScheduleIndex = index;
        const selectedSchedule = availableSchedules[index];

        if (!selectedSchedule || !Array.isArray(selectedSchedule)) {
            alert('Ошибка: выбранное расписание не найдено');
            return;
        }

        document.querySelectorAll('.schedule-option').forEach(option => {
            option.style.display = 'none';
        });

        const selectedContent = document.getElementById('selectedScheduleContent');
        let contentHtml = '';

        selectedSchedule.forEach(event => {
            contentHtml += `
                <div class="time-slot selected">
                    <strong>${event.name}</strong><br>
                    <span class="time">${event.time}</span> |
                    <span class="location">${event.location}</span>
                </div>
            `;
        });

        selectedContent.innerHTML = contentHtml;
        document.getElementById('selectedScheduleSection').style.display = 'block';
    }

    function cancelSelection() {
        selectedScheduleIndex = null;
        document.getElementById('selectedScheduleSection').style.display = 'none';
        document.querySelectorAll('.schedule-option').forEach(option => {
            option.style.display = 'block';
        });
    }

    async function saveSelectedSchedule() {
        if (selectedScheduleIndex === null) {
            alert('Сначала выберите вариант расписания!');
            return;
        }

        try {
            const response = await fetch('/schedules/save-selected', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ schedule_index: selectedScheduleIndex })
            });

            const data = await handleResponse(response);

            if (data.success) {
                alert(data.message);
                displaySingleSchedule(data.selected_schedule);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка сохранения: ' + error.message);
        }
    }

    async function saveAsList() {
        const selectedCount = document.getElementById('selectedCount').textContent;

        if (selectedCount === '0') {
            alert('Нет выбранных событий для сохранения!');
            return;
        }

        try {
            const response = await fetch('/schedules/save-list', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await handleResponse(response);
            alert(data.message);

            if (data.success) {
                loadSavedSchedules();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка сохранения: ' + error.message);
        }
    }

    async function loadSavedSchedules() {
        try {
            const response = await fetch('/schedules/saved');
            const schedules = await handleResponse(response);

            const savedSection = document.getElementById('savedSchedulesSection');
            const savedList = document.getElementById('savedSchedulesList');

            if (!schedules || schedules.length === 0) {
                savedList.innerHTML = '<p>Нет сохраненных расписаний.</p>';
                const deleteBtn = document.getElementById('deleteSelectedBtn');
                if (deleteBtn) {
                    deleteBtn.style.display = 'none';
                }
            } else {
                let html = `
                    <div class="schedules-management">
                        <div class="management-header">
                            <h4>Управление сохраненными расписаниями</h4>
                            <button id="deleteSelectedBtn" onclick="deleteSelectedSchedules()" class="delete-btn" style="display: none;">
                                Удалить выбранные
                            </button>
                        </div>
                        <div class="schedules-list">
                `;

                // ГЕНЕРИРУЕМ СПИСОК РАСПИСАНИЙ
                schedules.forEach(schedule => {
                    const scheduleType = schedule.type === 'list' ? '📋 Список' :
                        schedule.type === 'group' ? '👥 Групповое' : '⏰ Индивидуальное';
                    const eventsCount = schedule.total_events || 0;
                    const isGroup = schedule.type === 'group';

                    html += `
                        <div class="saved-item ${selectedSchedules.has(schedule.id) ? 'selected' : ''}" id="schedule-${schedule.id}">
                            <div class="schedule-checkbox">
                                <input type="checkbox" id="check-${schedule.id}"
                                       onchange="toggleScheduleSelection(${schedule.id})"
                                       ${selectedSchedules.has(schedule.id) ? 'checked' : ''}>
                            </div>
                            <div class="schedule-info">
                                <h4>${scheduleType}: ${schedule.name}</h4>
                                <p class="schedule-meta">
                                    <small>Создано: ${new Date(schedule.created_at).toLocaleString()}</small><br>
                                    <small>Событий: ${eventsCount}</small>
                                    ${isGroup && schedule.group_members ?
                        `<br><small>Участники: ${schedule.group_members.join(', ')}</small>` : ''}
                                </p>
                            </div>
                            <div class="schedule-actions">
                                <button onclick="loadSchedule(${schedule.id})">Загрузить</button>
                                <button onclick="deleteSingleSchedule(${schedule.id})" class="delete-btn">Удалить</button>
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;
                savedList.innerHTML = html;

                const bulkActions = document.getElementById('bulkActions');
                if (bulkActions) {
                    bulkActions.style.display = 'block';
                }

                updateDeleteButton();
            }

            savedSection.style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка загрузки сохраненных расписаний: ' + error.message);
        }
    }

    async function loadSchedule(scheduleId) {
        try {
            const response = await fetch(`/schedules/load/${scheduleId}`);
            const data = await handleResponse(response);

            if (data.success) {
                const selectedEvents = Array.isArray(data.selected_events)
                    ? data.selected_events
                    : Object.values(data.selected_events || {});

                document.querySelectorAll('.event-item').forEach(item => {
                    const eventId = parseInt(item.querySelector('input').id.replace('event-', ''));
                    const isSelected = selectedEvents.includes(eventId);
                    item.classList.toggle('selected', isSelected);
                    item.querySelector('input').checked = isSelected;
                });

                document.getElementById('selectedCount').textContent = selectedEvents.length;
                updateStats();

                if (data.schedule_data && data.schedule_data.length > 0 && data.schedule_type !== 'list') {
                    displaySingleSchedule(data.schedule_data[0]);
                } else {
                    document.getElementById('schedulesList').innerHTML = '<p>Загружен список событий. Сгенерируйте расписания для просмотра вариантов.</p>';
                }

                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка загрузки расписания: ' + error.message);
        }
    }

    async function deleteSingleSchedule(scheduleId) {
        if (confirm('Удалить это расписание?')) {
            try {
                const response = await fetch(`/schedules/delete/${scheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await handleResponse(response);
                alert(data.message);

                selectedSchedules.delete(scheduleId);
                await loadSavedSchedules();
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка удаления: ' + error.message);
            }
        }
    }

    // Сценарий 2: Групповое планирование
    async function saveMemberSelection() {
        const memberName = document.getElementById('memberName').value;
        if (!memberName) {
            alert('Введите имя участника');
            return;
        }

        try {
            const response = await fetch('/group/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ member_name: memberName })
            });

            const data = await handleResponse(response);
            alert(data.message);

            if (data.success) {
                updateGroupMembersList(data.group_selections);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка сохранения выбора участника: ' + error.message);
        }
    }

    async function mergeGroupSelections() {
        try {
            const response = await fetch('/group/merge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await handleResponse(response);

            if (data.success) {
                displayScheduleSelection(data.schedules);
                document.getElementById('selectedCount').textContent = data.total_events;
                updateStats();
                alert(data.message);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка создания группового расписания: ' + error.message);
        }
    }

    function updateGroupMembersList(selections) {
        const membersList = document.getElementById('groupMembersList');
        let html = '<h4>Участники группы:</h4>';

        for (const [member, events] of Object.entries(selections)) {
            html += `<div class="group-member">${member}: ${events.length} событий</div>`;
        }

        membersList.innerHTML = html;
    }

    // Сценарий 3: Корректировка на лету
    async function adjustSchedule() {
        const selectedCheckboxes = document.querySelectorAll('.event-item input[type="checkbox"]:checked');
        const newEventId = selectedCheckboxes[selectedCheckboxes.length - 1]?.id.replace('event-', '');

        if (!newEventId) {
            alert('Выберите событие для добавления');
            return;
        }

        try {
            const response = await fetch('/schedules/adjust', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ event_id: parseInt(newEventId) })
            });

            const data = await handleResponse(response);

            if (data.success) {
                displayScheduleSelection(data.schedules);
                alert(data.message);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка корректировки расписания: ' + error.message);
        }
    }

    async function clearSelection() {
        if (confirm('Очистить все выбранные события?')) {
            try {
                document.querySelectorAll('.event-item input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.parentElement.classList.remove('selected');
                });

                const response = await fetch('/events/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await handleResponse(response);

                if (data.success) {
                    document.getElementById('selectedCount').textContent = '0';
                    updateStats();
                    document.getElementById('schedulesList').innerHTML = '<p>Сгенерируйте расписания, чтобы увидеть варианты</p>';
                    await clearCurrentSchedule();
                    console.log('Selection cleared successfully');
                }
            } catch (error) {
                console.error('Error clearing selection:', error);
                alert('Ошибка при очистке выбора: ' + error.message);
            }
        }
    }

    // Функция для выбора/снятия выбора расписания
    function toggleScheduleSelection(scheduleId) {
        if (selectedSchedules.has(scheduleId)) {
            selectedSchedules.delete(scheduleId);
        } else {
            selectedSchedules.add(scheduleId);
        }

        const scheduleElement = document.getElementById(`schedule-${scheduleId}`);
        if (scheduleElement) {
            scheduleElement.classList.toggle('selected', selectedSchedules.has(scheduleId));
        }

        updateDeleteButton();
    }

    // Функция для очистки текущего расписания в сессии
    async function clearCurrentSchedule() {
        try {
            const response = await fetch('/schedules/clear-current', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        } catch (error) {
            console.error('Error clearing current schedule:', error);
        }
    }

    // Обновление состояния кнопки удаления
    function updateDeleteButton() {
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        if (deleteBtn) {
            if (selectedSchedules.size > 0) {
                deleteBtn.style.display = 'block';
                deleteBtn.textContent = `Удалить выбранные (${selectedSchedules.size})`;
            } else {
                deleteBtn.style.display = 'none';
            }
        }
    }

    // Массовое удаление выбранных расписаний
    async function deleteSelectedSchedules() {
        if (selectedSchedules.size === 0) {
            alert('Не выбраны расписания для удаления');
            return;
        }

        if (!confirm(`Вы уверены, что хотите удалить ${selectedSchedules.size} расписаний?`)) {
            return;
        }

        try {
            const response = await fetch('/schedules/delete-multiple', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    schedule_ids: Array.from(selectedSchedules)
                })
            });

            const data = await handleResponse(response);

            if (data.success) {
                alert(data.message);
                selectedSchedules.clear();
                await loadSavedSchedules();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка удаления: ' + error.message);
        }
    }

    // Функция для выбора всех расписаний
    function selectAllSchedules() {
        const checkboxes = document.querySelectorAll('.schedules-list input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const scheduleId = parseInt(checkbox.id.replace('check-', ''));
            selectedSchedules.add(scheduleId);
            checkbox.checked = true;
            const scheduleElement = document.getElementById(`schedule-${scheduleId}`);
            if (scheduleElement) {
                scheduleElement.classList.add('selected');
            }
        });
        updateDeleteButton();
    }

    // Функция для снятия выбора со всех расписаний
    function deselectAllSchedules() {
        selectedSchedules.clear();
        document.querySelectorAll('.schedules-list input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.saved-item').forEach(item => {
            item.classList.remove('selected');
        });
        updateDeleteButton();
    }

    async function clearGroupSelection() {
        if (confirm('Очистить все выборы участников группы?')) {
            try {
                const response = await fetch('/group/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await handleResponse(response);

                if (data.success) {
                    alert(data.message);
                    updateGroupMembersList({});
                }
            } catch (error) {
                console.error('Error clearing group selection:', error);
                alert('Ошибка при очистке группы: ' + error.message);
            }
        }
    }

    // Инициализация
    document.addEventListener('DOMContentLoaded', function() {
        initializePage();
    });
</script>
</body>
</html>
