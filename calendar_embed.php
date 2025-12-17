<?php /* calendar_embed.php */ ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- مهم: نفس ملفات الكيلندر اللي عندك -->
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" as="style">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- CSS الكيلندر (زي اللي كان بالكود الأصلي) -->
  <link rel="stylesheet" crossorigin href="./assets/main-D9K-blpF.css">

  <style>
    /* نخلي الصفحة نظيفة داخل iframe */
    body{ margin:0; background:transparent; }
    .container-fluid{ padding: 14px !important; }
  </style>
</head>

<body data-page="calendar" class="calendar-page">
  <div class="container-fluid" x-data="calendarComponent" x-init="init()">

    <div class="calendar-container">
      <div class="calendar-layout">

        <!-- Sidebar -->
        <div class="calendar-sidebar" :class="{ 'mobile-show': sidebarVisible }">

          <div class="calendar-sidebar-header">
            <h5 class="sidebar-title mb-0">Calendar</h5>
            <button class="btn btn-primary btn-sm" @click="addEvent()" title="Add Event">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>

          <!-- Mini Calendar -->
          <div class="mini-calendar">
            <div class="mini-calendar-header">
              <div class="d-flex justify-content-between align-items-center">
                <button class="btn btn-sm btn-outline-secondary" @click="previousMonth()"><i class="bi bi-chevron-left"></i></button>
                <h6 class="mb-0 fw-semibold" x-text="currentMonthYear"></h6>
                <button class="btn btn-sm btn-outline-secondary" @click="nextMonth()"><i class="bi bi-chevron-right"></i></button>
              </div>
            </div>

            <div class="mini-calendar-weekdays">
              <div class="weekday">S</div><div class="weekday">M</div><div class="weekday">T</div>
              <div class="weekday">W</div><div class="weekday">T</div><div class="weekday">F</div><div class="weekday">S</div>
            </div>

            <div class="mini-calendar-grid">
              <template x-for="day in miniCalendarDays" :key="day.date">
                <div class="mini-calendar-day"
                  :class="{
                    'today': day.isToday,
                    'other-month': day.isOtherMonth,
                    'selected': day.isSelected,
                    'has-events': day.hasEvents
                  }"
                  @click="selectDate(day.date)"
                  x-text="day.day"></div>
              </template>
            </div>
          </div>

          <!-- Categories (فقط 3) -->
          <div class="event-categories">
            <h6 class="category-title">Event Categories</h6>
            <div class="category-list">

              <label class="category-item">
                <input type="checkbox" x-model="visibleTypes" value="event" class="form-check-input">
                <span class="category-color" style="background: var(--bs-primary);"></span>
                <span class="category-name">Events</span>
                <span class="category-count" x-text="getCategoryCount('event')"></span>
              </label>

              <label class="category-item">
                <input type="checkbox" x-model="visibleTypes" value="meeting" class="form-check-input">
                <span class="category-color" style="background: var(--bs-success);"></span>
                <span class="category-name">Meetings</span>
                <span class="category-count" x-text="getCategoryCount('meeting')"></span>
              </label>

              <label class="category-item">
                <input type="checkbox" x-model="visibleTypes" value="task" class="form-check-input">
                <span class="category-color" style="background: var(--bs-warning);"></span>
                <span class="category-name">Tasks</span>
                <span class="category-count" x-text="getCategoryCount('task')"></span>
              </label>

            </div>
          </div>

          <!-- Upcoming -->
          <div class="upcoming-events">
            <h6 class="upcoming-title">Upcoming Events</h6>
            <div class="upcoming-list">
              <template x-for="event in upcomingEvents.slice(0, 5)" :key="event.id">
                <div class="upcoming-item" @click="viewEvent(event)">
                  <div class="upcoming-time">
                    <span class="time" x-text="event.timeStr"></span>
                    <span class="date" x-text="event.dateStr"></span>
                  </div>
                  <div class="upcoming-content">
                    <h6 class="upcoming-event-title" x-text="event.title"></h6>
                    <p class="upcoming-description" x-text="event.description"></p>
                  </div>
                  <div class="upcoming-indicator" :style="`background: ${getCategoryColor(event.type)}`"></div>
                </div>
              </template>

              <div x-show="upcomingEvents.length === 0" class="upcoming-empty">
                <i class="bi bi-calendar-check"></i>
                <p>No upcoming events</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main -->
        <div class="calendar-main">
          <div class="calendar-header">
            <div class="calendar-nav-left">
              <button class="btn btn-link d-lg-none me-2 p-0" @click="sidebarVisible = !sidebarVisible">
                <i class="bi bi-list fs-5"></i>
              </button>

              <div class="calendar-nav-controls">
                <button class="btn btn-outline-secondary" @click="previousPeriod()"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-outline-primary" @click="goToToday()">Today</button>
                <button class="btn btn-outline-secondary" @click="nextPeriod()"><i class="bi bi-chevron-right"></i></button>
              </div>

              <h3 class="calendar-title" x-text="currentPeriodTitle"></h3>
            </div>

            <div class="calendar-nav-right">
              <div class="view-switcher">
                <button class="view-btn" :class="{ 'active': currentView === 'month' }" @click="switchView('month')">
                  <i class="bi bi-calendar3 me-1"></i>Month
                </button>
                <button class="view-btn" :class="{ 'active': currentView === 'week' }" @click="switchView('week')">
                  <i class="bi bi-calendar2-week me-1"></i>Week
                </button>
                <button class="view-btn" :class="{ 'active': currentView === 'day' }" @click="switchView('day')">
                  <i class="bi bi-calendar-day me-1"></i>Day
                </button>
              </div>
            </div>
          </div>

          <div class="calendar-content">
            <!-- نفس Views (Month/Week/Day) بتشتغل من calendar.js -->
            <div x-show="currentView === 'month'" class="month-view">
              <div class="month-header">
                <div class="month-header-day">Sunday</div><div class="month-header-day">Monday</div><div class="month-header-day">Tuesday</div>
                <div class="month-header-day">Wednesday</div><div class="month-header-day">Thursday</div><div class="month-header-day">Friday</div>
                <div class="month-header-day">Saturday</div>
              </div>

              <div class="month-grid">
                <template x-for="day in calendarDays" :key="day.date">
                  <div class="month-day"
                    :class="{ 'today': day.isToday, 'other-month': day.isOtherMonth, 'selected': day.isSelected, 'has-events': day.events && day.events.length > 0 }"
                    @click="selectDay(day)"
                    @dblclick="addEventForDay(day)">
                    <div class="day-number" x-text="day.day"></div>

                    <div class="day-events">
                      <template x-for="(event, index) in day.events?.slice(0, 3)" :key="event.id">
                        <div class="day-event" :class="`event-${event.type}`" @click.stop="viewEvent(event)" :title="event.title + ' - ' + event.time">
                          <span class="event-title" x-text="event.title"></span>
                        </div>
                      </template>

                      <div x-show="day.events && day.events.length > 3" class="more-events"
                        @click.stop="showMoreEvents(day)"
                        x-text="`+${day.events.length - 3} more`"></div>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <div x-show="currentView === 'week'" class="week-view">
              <div class="week-header">
                <div class="time-column">Time</div>
                <template x-for="day in weekDays" :key="day.date">
                  <div class="week-day-header" :class="{ 'today': day.isToday }">
                    <div class="day-name" x-text="day.dayName"></div>
                    <div class="day-number" x-text="day.dayNumber"></div>
                  </div>
                </template>
              </div>

              <div class="week-grid">
                <div class="time-slots">
                  <template x-for="hour in hours" :key="hour">
                    <div class="time-slot" x-text="hour"></div>
                  </template>
                </div>

                <div class="week-days">
                  <template x-for="day in weekDays" :key="day.date">
                    <div class="week-day-column" :class="{ 'today': day.isToday }">
                      <template x-for="hour in hours" :key="hour">
                        <div class="hour-slot" @click="addEventAtTime(day.date, hour)" @dblclick="addEventAtTime(day.date, hour)">
                          <template x-for="event in getEventsForDateTime(day.date, hour)" :key="event.id">
                            <div class="week-event" :class="`event-${event.type}`" @click.stop="viewEvent(event)" :title="event.title + ' - ' + event.time">
                              <div class="event-time" x-text="event.time"></div>
                              <div class="event-title" x-text="event.title"></div>
                            </div>
                          </template>
                        </div>
                      </template>
                    </div>
                  </template>
                </div>
              </div>
            </div>

            <div x-show="currentView === 'day'" class="day-view">
              <div class="day-view-header">
                <div class="day-info">
                  <h4 class="day-title" x-text="selectedDayTitle"></h4>
                  <p class="day-date" x-text="selectedDayDate"></p>
                </div>
              </div>

              <div class="day-schedule">
                <div class="schedule-times">
                  <template x-for="hour in hours" :key="hour">
                    <div class="schedule-time" x-text="hour"></div>
                  </template>
                </div>

                <div class="schedule-events">
                  <template x-for="hour in hours" :key="hour">
                    <div class="schedule-hour" @click="addEventAtTime(selectedDay, hour)" @dblclick="addEventAtTime(selectedDay, hour)">
                      <template x-for="event in getEventsForDateTime(selectedDay, hour)" :key="event.id">
                        <div class="schedule-event" :class="`event-${event.type}`" @click.stop="viewEvent(event)">
                          <div class="event-time" x-text="event.time"></div>
                          <div class="event-title" x-text="event.title"></div>
                          <div class="event-description" x-text="event.description"></div>
                        </div>
                      </template>

                      <div x-show="isCurrentHour(hour) && isToday(selectedDay)" class="current-time-indicator"></div>
                    </div>
                  </template>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Add Event Modal: فقط 3 أنواع -->
  <div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Add New Event</h5>
            <p class="text-muted mb-0 small">Create a new calendar event</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body pt-2" x-data="addEventModal" x-init="init()">
          <form @submit.prevent="submitEvent()">
            <div class="mb-3">
              <label class="form-label fw-semibold">Event Title *</label>
              <input type="text" class="form-control form-control-lg" x-model="eventData.title" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Event Type</label>
                <select class="form-select" x-model="eventData.type">
                  <option value="event">📅 Event</option>
                  <option value="meeting">🤝 Meeting</option>
                  <option value="task">✅ Task</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Priority</label>
                <select class="form-select" x-model="eventData.priority">
                  <template x-for="priority in priorityOptions" :key="priority.value">
                    <option :value="priority.value" x-text="priority.label"></option>
                  </template>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Date *</label>
                <input type="date" class="form-control" x-model="eventData.date" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Start Time *</label>
                <input type="time" class="form-control" x-model="eventData.time" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Description</label>
              <textarea class="form-control" rows="3" x-model="eventData.description"></textarea>
            </div>
          </form>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" @click="closeModal()">Cancel</button>
          <button type="button" class="btn btn-primary px-4" @click="submitEvent()">Create Event</button>
        </div>
      </div>
    </div>
  </div>

  <button class="add-event-btn" @click="addEvent()" title="Add Event">
    <i class="bi bi-plus-lg fs-4"></i>
  </button>

  <!-- JS الكيلندر (زي اللي كان بالكود الأصلي) -->
  <script type="module" crossorigin src="./assets/main-BPhDq89w.js"></script>
  <script type="module" crossorigin src="./assets/calendar-Bv9AekZw.js"></script>

  <!-- احتياط: امسحي أي نوع غير الثلاثة -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const root = document.querySelector('[x-data="calendarComponent"]');
      let tries = 0;
      const t = setInterval(() => {
        tries++;
        const data = root && root.__x && root.__x.$data;
        if (data && Array.isArray(data.visibleTypes)) {
          data.visibleTypes = data.visibleTypes.filter(x => ['event','meeting','task'].includes(x));
          clearInterval(t);
        }
        if (tries > 25) clearInterval(t);
      }, 100);
    });
  </script>
</body>
</html>
