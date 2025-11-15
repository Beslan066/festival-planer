<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Фестиваль - Планировщик расписания</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Добавьте ваш CSS из предыдущей версии */
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 1200px; margin: 0 auto; }
        .events-section, .schedule-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .event-item { padding: 10px; border: 1px solid #ddd; margin: 5px 0; border-radius: 5px; cursor: pointer; }
        .event-item.selected { background: #e3f2fd; border-color: #2196f3; }
        .event-item input { margin-right: 10px; }
        .time-slot { padding: 8px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #007bff; }
        .schedule-option { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .buttons-container { margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
        button { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .save-btn { background: #28a745; color: white; }
        .clear-btn { background: #dc3545; color: white; }
        .load-btn { background: #17a2b8; color: white; }
        .scenario-section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .tab-container { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #e9ecef; border: none; border-radius: 5px; cursor: pointer; }
        .tab.active { background: #007bff; color: white; }
        .hidden { display: none; }
    </style>

    <link rel="stylesheet" href="{{asset('/style.css')}}">
</head>
<body>
<header>
    <h1>Организатор расписания фестиваля</h1>
    <p class="app-description">
        Выберите сценарий использования и создайте оптимальное расписание мероприятий.
    </p>
</header>

<div class="tab-container">
    <button class="tab active" onclick="showScenario(1)">Индивидуальное планирование</button>
    <button class="tab" onclick="showScenario(2)">Групповое планирование</button>
    <button class="tab" onclick="showScenario(3)">Корректировка на лету</button>
</div>

<div class="container">
    <!-- Левая колонка - События -->
    <div class="events-section">
        <h2>События фестиваля</h2>
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

        <div id="eventsList">
            @foreach($events as $event)
                <div class="event-item {{ in_array($event->id, $selectedEvents) ? 'selected' : '' }}"
                     onclick="toggleEvent({{ $event->id }})">
                    <input type="checkbox" id="event-{{ $event->id }}"
                        {{ in_array($event->id, $selectedEvents) ? 'checked' : '' }}>
                    <label for="event-{{ $event->id }}">
                        <strong>{{ $event->name }}</strong><br>
                        <span class="time">{{ $event->time }}</span> |
                        <span class="location">{{ $event->location }}</span>
                        <span class="duration">({{ $event->duration }} мин)</span>
                    </label>
                </div>
            @endforeach
        </div>

        <!-- Сценарий 1: Индивидуальное планирование -->
        <div id="scenario1" class="scenario-content">
            <div class="buttons-container">
                <button onclick="generateSchedules()">Сгенерировать расписания</button>
                <button onclick="saveSelection()" class="save-btn">Сохранить выбор</button>
                <button onclick="clearSelection()" class="clear-btn">Очистить выбор</button>
            </div>
        </div>

        <!-- Сценарий 2: Групповое планирование -->
        <div id="scenario2" class="scenario-content hidden">
            <div class="group-management">
                <h3>Управление группой</h3>
                <input type="text" id="memberName" placeholder="Имя участника" style="padding: 8px; margin-right: 10px;">
                <button onclick="saveMemberSelection()" class="save-btn">Сохранить выбор участника</button>
                <button onclick="mergeGroupSelections()" class="load-btn">Создать групповое расписание</button>
            </div>
            <div id="groupMembersList">
                <h4>Участники группы:</h4>
                @foreach($groupSelections as $member => $events)
                    <div>{{ $member }}: {{ count($events) }} событий</div>
                @endforeach
            </div>
        </div>

        <!-- Сценарий 3: Корректировка на лету -->
        <div id="scenario3" class="scenario-content hidden">
            <div class="adjustment-controls">
                <h3>Корректировка расписания</h3>
                <p>Выберите дополнительное событие для добавления в текущее расписание:</p>
                <button onclick="adjustSchedule()" class="save-btn">Обновить расписание</button>
            </div>
        </div>
    </div>

    <!-- Правая колонка - Расписания -->
    <div class="schedule-section">
        <h2>Рекомендуемые расписания</h2>

        <div class="schedule-actions">
            <button onclick="saveAsList()" class="save-btn">Сохранить как список</button>
            <button onclick="loadSavedSchedules()" class="load-btn">Мои сохранения</button>
        </div>

        <div id="schedulesList">
            @if(isset($currentSchedule) && count($currentSchedule) > 0)
                @if(count($currentSchedule) > 1)
                    <!-- Показываем выбор из 3 вариантов -->
                    <div class="schedule-selection">
                        <h3>Выберите один вариант расписания:</h3>
                        @foreach($currentSchedule as $index => $schedule)
                            <div class="schedule-option" id="scheduleOption{{ $index }}">
                                <div class="schedule-header">
                                    <h4>Вариант {{ $index + 1 }} ({{ count($schedule) }} событий)</h4>
                                    <button onclick="selectSchedule({{ $index }})" class="select-btn">
                                        Выбрать этот вариант
                                    </button>
                                </div>
                                <div class="schedule-content">
                                    @foreach($schedule as $event)
                                        <div class="time-slot">
                                            <strong>{{ $event['name'] }}</strong><br>
                                            <span class="time">{{ $event['time'] }}</span> |
                                            <span class="location">{{ $event['location'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Выбранное расписание (скрыто изначально) -->
                    <div id="selectedScheduleSection" style="display: none;">
                        <h3>Ваше выбранное расписание</h3>
                        <div id="selectedScheduleContent"></div>
                        <button onclick="saveSelectedSchedule()" class="save-btn">Сохранить это расписание</button>
                    </div>
                @else
                    <!-- Показываем одно сохраненное расписание -->
                    <div class="saved-schedule-display">
                        <h3>Ваше сохраненное расписание</h3>
                        @foreach($currentSchedule[0] as $event)
                            <div class="time-slot saved">
                                <strong>{{ $event['name'] }}</strong><br>
                                <span class="time">{{ $event['time'] }}</span> |
                                <span class="location">{{ $event['location'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <p>Сгенерируйте расписания, чтобы увидеть варианты</p>
            @endif
        </div>

        <div id="savedSchedulesSection" style="display: none;">
            <h3>Мои сохраненные расписания и списки</h3>

            <!-- Добавьте кнопки массового управления -->
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <button onclick="selectAllSchedules()" class="select-all-btn">Выбрать все</button>
                <button onclick="deselectAllSchedules()" class="deselect-all-btn">Снять выбор</button>
            </div>

            <div id="savedSchedulesList"></div>
        </div>
    </div>
</div>

<script>
    let currentScenario = 1;
    let selectedScheduleIndex = null;
    let availableSchedules = [];
    let selectedSchedules = new Set();


    // Добавьте эту функцию в начало скрипта
    async function initializePage() {
        try {
            // Загружаем текущее состояние из сессии
            const response = await fetch('/events/current-state');
            const data = await handleResponse(response);

            if (data.success) {
                // Обновляем UI в соответствии с данными из сессии
                updateUIFromSession(data);
            }
        } catch (error) {
            console.error('Error initializing page:', error);
        }
    }

    // Функция для обновления UI на основе данных сессии
    function updateUIFromSession(data) {
        // Обновляем выбранные события
        const selectedEvents = data.selected_events || [];
        document.querySelectorAll('.event-item').forEach(item => {
            const eventId = parseInt(item.querySelector('input').id.replace('event-', ''));
            const isSelected = selectedEvents.includes(eventId);
            item.classList.toggle('selected', isSelected);
            item.querySelector('input').checked = isSelected;
        });

        document.getElementById('selectedCount').textContent = selectedEvents.length;
        updateStats();

        // Обновляем отображение расписания
        const currentSchedule = data.current_schedule || [];
        if (currentSchedule.length > 0 && currentSchedule[0].length > 0) {
            displaySingleSchedule(currentSchedule[0]);
        } else {
            document.getElementById('schedulesList').innerHTML = '<p>Сгенерируйте расписания, чтобы увидеть варианты</p>';
        }
    }


    //  Функция для обработки ошибок JSON
    async function handleResponse(response) {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Ожидался JSON, но получен: ${text.substring(0, 100)}`);
        }
        return response.json();
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

        // Секция для выбранного расписания
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

        // Скрываем все варианты
        document.querySelectorAll('.schedule-option').forEach(option => {
            option.style.display = 'none';
        });

        // Показываем выбранное расписание
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

        const response = await fetch('/schedules/save-selected', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ schedule_index: selectedScheduleIndex })
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            displaySingleSchedule(data.selected_schedule);
        } else {
            alert(data.message);
        }
    }

    async function saveAsList() {
        const selectedCount = document.getElementById('selectedCount').textContent;

        if (selectedCount === '0') {
            alert('Нет выбранных событий для сохранения!');
            return;
        }

        const response = await fetch('/schedules/save-list', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();
        alert(data.message);

        if (data.success) {
            loadSavedSchedules();
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
                // Скрываем кнопку удаления, если нет расписаний
                const deleteBtn = document.getElementById('deleteSelectedBtn');
                if (deleteBtn) {
                    deleteBtn.style.display = 'none';
                }
            }else {
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

                // ... остальной код генерации расписаний

                html += `
            </div>
        </div>
    `;
                savedList.innerHTML = html;

                // Показываем bulkActions если есть расписания
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
                // Преобразуем selected_events в массив, если это необходимо
                const selectedEvents = Array.isArray(data.selected_events)
                    ? data.selected_events
                    : Object.values(data.selected_events || {});

                // Обновляем UI с выбранными событиями
                document.querySelectorAll('.event-item').forEach(item => {
                    const eventId = parseInt(item.querySelector('input').id.replace('event-', ''));
                    const isSelected = selectedEvents.includes(eventId);
                    item.classList.toggle('selected', isSelected);
                    item.querySelector('input').checked = isSelected;
                });

                document.getElementById('selectedCount').textContent = selectedEvents.length;
                updateStats();

                // Если это расписание (не список), показываем его
                if (data.schedule_data && data.schedule_data.length > 0 && data.schedule_type !== 'list') {
                    displaySingleSchedule(data.schedule_data[0]);
                } else {
                    // Если это список, очищаем отображение расписаний
                    document.getElementById('schedulesList').innerHTML = '<p>Загружен список событий. Сгенерируйте расписания для просмотра вариантов.</p>';
                }

                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка загрузки расписания: ' + error.message);
        }
    }

    async function deleteSchedule(scheduleId) {
        if (confirm('Удалить это расписание?')) {
            const response = await fetch(`/schedules/delete/${scheduleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();
            alert(data.message);
            loadSavedSchedules();
        }
    }

    // Сценарий 2: Групповое планирование
    async function saveMemberSelection() {
        const memberName = document.getElementById('memberName').value;
        if (!memberName) {
            alert('Введите имя участника');
            return;
        }

        const response = await fetch('/group/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ member_name: memberName })
        });

        const data = await response.json();
        alert(data.message);

        if (data.success) {
            updateGroupMembersList(data.group_selections);
        }
    }

    async function mergeGroupSelections() {
        const response = await fetch('/group/merge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            displayScheduleSelection(data.schedules);
            document.getElementById('selectedCount').textContent = data.total_events;
            updateStats();
            alert(data.message);
        } else {
            alert(data.message);
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

        const response = await fetch('/schedules/adjust', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ event_id: parseInt(newEventId) })
        });

        const data = await response.json();

        if (data.success) {
            displayScheduleSelection(data.schedules);
            alert(data.message);
        } else {
            alert(data.message);
        }
    }

    async function clearSelection() {
        if (confirm('Очистить все выбранные события?')) {
            try {
                // Сначала очищаем UI
                document.querySelectorAll('.event-item input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.parentElement.classList.remove('selected');
                });

                // Затем очищаем серверную сессию
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

                    // Полностью очищаем отображение расписаний
                    document.getElementById('schedulesList').innerHTML = '<p>Сгенерируйте расписания, чтобы увидеть варианты</p>';

                    // Очищаем текущее расписание в сессии
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

        // Обновляем визуальное состояние (с проверкой существования элемента)
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
            // Не обрабатываем ошибку специально, так как это дополнительная операция
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
                // Очищаем выбор и перезагружаем список
                selectedSchedules.clear();
                await loadSavedSchedules(); // Ждем завершения загрузки
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка удаления: ' + error.message);
        }
    }

    // Удаление одиночного расписания
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

                // Удаляем из выбранных, если было выбрано
                selectedSchedules.delete(scheduleId);
                await loadSavedSchedules(); // Ждем завершения загрузки
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка удаления: ' + error.message);
            }
        }
    }

    // Функция для выбора всех расписаний
    function selectAllSchedules() {
        const checkboxes = document.querySelectorAll('.schedules-list input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const scheduleId = parseInt(checkbox.id.replace('check-', ''));
            selectedSchedules.add(scheduleId);
            checkbox.checked = true;
            document.getElementById(`schedule-${scheduleId}`).classList.add('selected');
        });
        updateDeleteButton();
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

    // Инициализация
    document.addEventListener('DOMContentLoaded', function() {
        updateStats();
    });


</script>
</body>
</html>
