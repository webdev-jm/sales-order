<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Holidays {{ now()->year }}</h3>
            <div class="card-tools">
                <div class="btn-group mr-2">
                    <button type="button"
                        class="btn btn-sm {{ $activeView === 'list' ? 'btn-primary' : 'btn-default' }}"
                        wire:click="switchView('list')">
                        <i class="fas fa-list mr-1"></i> List
                    </button>
                    <button type="button"
                        class="btn btn-sm {{ $activeView === 'calendar' ? 'btn-primary' : 'btn-default' }}"
                        wire:click="switchView('calendar')">
                        <i class="fas fa-calendar mr-1"></i> Calendar
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-add"
                    wire:click="$emit('openAddHoliday')">
                    <i class="fas fa-plus mr-1"></i> Add Custom Holiday
                </button>
            </div>
        </div>

        @if($activeView === 'list')
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm table-hover">
                <thead>
                    <tr>
                        <th style="width:130px">Date</th>
                        <th>Title</th>
                        <th style="width:160px">Type</th>
                        <th style="width:140px">Status</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mergedList as $holiday)
                    <tr>
                        <td class="text-nowrap">
                            {{ \Carbon\Carbon::parse($holiday['date'])->format('M d, Y') }}
                        </td>
                        <td>{{ $holiday['title'] }}</td>
                        <td>
                            @if($holiday['type'] === 'philippine')
                                <span class="badge badge-info">Philippine Holiday</span>
                            @else
                                <span class="badge badge-secondary">Custom</span>
                            @endif
                        </td>
                        <td>
                            @if($holiday['type'] === 'philippine')
                                @if($holiday['is_work_day'])
                                    <span class="badge badge-success mr-1">Work</span>
                                    <button type="button" class="btn btn-xs btn-outline-danger"
                                        wire:click="toggleWorkDay('{{ $holiday['date'] }}', '{{ addslashes($holiday['title']) }}')"
                                        wire:loading.attr="disabled">
                                        Set No Work
                                    </button>
                                @else
                                    <span class="badge badge-danger mr-1">No Work</span>
                                    <button type="button" class="btn btn-xs btn-outline-success"
                                        wire:click="toggleWorkDay('{{ $holiday['date'] }}', '{{ addslashes($holiday['title']) }}')"
                                        wire:loading.attr="disabled">
                                        Set as Work
                                    </button>
                                @endif
                            @else
                                @if($holiday['is_work_day'])
                                    <span class="badge badge-success">Work</span>
                                @else
                                    <span class="badge badge-danger">No Work</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if($holiday['type'] === 'custom' && $holiday['id'])
                                <button type="button" class="btn btn-xs btn-danger"
                                    wire:click="deleteHoliday({{ $holiday['id'] }})"
                                    wire:loading.attr="disabled"
                                    onclick="return confirm('Delete this holiday?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No holidays found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <div class="card-body p-0">
            <div id="calendar-container"></div>
        </div>
        <div class="card-footer">
            <div class="d-flex flex-wrap" style="gap:16px">
                <span class="d-flex align-items-center" style="gap:6px">
                    <span style="width:14px;height:14px;border-radius:3px;background:#3498db;display:inline-block"></span>
                    Philippine Holiday (No Work)
                </span>
                <span class="d-flex align-items-center" style="gap:6px">
                    <span style="width:14px;height:14px;border-radius:3px;background:#27ae60;display:inline-block"></span>
                    Philippine Holiday (Work Day)
                </span>
                <span class="d-flex align-items-center" style="gap:6px">
                    <span style="width:14px;height:14px;border-radius:3px;background:#e74c3c;display:inline-block"></span>
                    Custom Holiday
                </span>
            </div>
        </div>
        @endif
    </div>

    @if($activeView === 'calendar')
    <script>
        (function initCalendar() {
            var holidays = {!! json_encode($calendarData) !!};
            var calendarEl = document.getElementById('calendar-container');
            if (!calendarEl || typeof FullCalendar === 'undefined') {
                document.addEventListener('DOMContentLoaded', function() { initCalendar(); });
                return;
            }
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left  : 'prev,next',
                    center: 'title',
                    right : 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                dateClick: function(info) {
                    Livewire.emit('setHolidayAdd', info.dateStr);
                    $('#modal-add').modal('show');
                },
                themeSystem: 'bootstrap',
                events: holidays,
            });
            calendar.render();
        })();
    </script>
    @endif
</div>
