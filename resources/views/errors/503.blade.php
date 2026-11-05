{{-- `php artisan down` shows the same maintenance page as MAINTENANCE_MODE in .env. --}}
@include('maintenance.page', [
    'message' => trim((string) ($exception?->getMessage() ?? '')) ?: __('The system is being updated. It will be back shortly.'),
    'until' => null,
])
