<!-- Alerts demo: Bootstrap native alerts с иконками -->
<div class="card my-3">
    <div class="card-header">Alerts</div>
    <div class="card-body d-flex flex-column gap-2">
        <div class="alert alert-success d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>Операция выполнена успешно.</span>
        </div>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="bi bi-x-circle-fill flex-shrink-0"></i>
            <span>Произошла ошибка. Попробуйте снова.</span>
        </div>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>Внимание: действие необратимо.</span>
        </div>
        <div class="alert alert-info d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="bi bi-info-circle-fill flex-shrink-0"></i>
            <span>Информация для пользователя.</span>
        </div>
        <!-- Dismissible -->
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>Dismissible alert — можно закрыть.</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Badges demo -->
<div class="card my-3">
    <div class="card-header">Badges</div>
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <span class="badge text-bg-primary">Primary</span>
        <span class="badge text-bg-secondary">Secondary</span>
        <span class="badge text-bg-success">Success</span>
        <span class="badge text-bg-danger">Danger</span>
        <span class="badge text-bg-warning">Warning</span>
        <span class="badge text-bg-info">Info</span>
        <span class="badge text-bg-light">Light</span>
        <span class="badge text-bg-dark">Dark</span>
        <!-- Pill badges -->
        <span class="badge rounded-pill text-bg-primary">Pill</span>
        <span class="badge rounded-pill text-bg-danger">99+</span>
        <!-- Badge в кнопке -->
        <button type="button" class="btn btn-sm btn-outline-secondary">
            Notifications <span class="badge text-bg-danger ms-1">4</span>
        </button>
    </div>
</div>
