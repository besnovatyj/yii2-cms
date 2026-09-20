<!-- Progress bars and Spinners demo -->
<div class="card my-3">
    <div class="card-header">Progress bars &amp; Spinners</div>
    <div class="card-body d-flex flex-column gap-3">

        <!-- Labeled progress bar -->
        <div>
            <div class="d-flex justify-content-between mb-1">
                <small>Загрузка файлов</small>
                <small>75%</small>
            </div>
            <div class="progress" role="progressbar" style="height: 8px;">
                <div class="progress-bar bg-primary" style="width: 75%"></div>
            </div>
        </div>

        <!-- Цветные bars -->
        <div class="progress" role="progressbar" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: 50%"></div>
        </div>
        <div class="progress" role="progressbar" style="height: 8px;">
            <div class="progress-bar bg-warning" style="width: 30%"></div>
        </div>
        <!-- Animated striped -->
        <div class="progress" role="progressbar" style="height: 8px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 60%"></div>
        </div>

        <!-- Stacked -->
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: 35%"></div>
            <div class="progress-bar bg-warning" style="width: 20%"></div>
            <div class="progress-bar bg-danger"  style="width: 10%"></div>
        </div>

        <!-- Spinners -->
        <div class="d-flex align-items-center gap-3 flex-wrap mt-2">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="spinner-border spinner-border-sm text-secondary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="spinner-grow text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <!-- Кнопка в состоянии загрузки -->
            <button class="btn btn-primary btn-sm" type="button" disabled>
                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                <span>Сохранение...</span>
            </button>
        </div>

    </div>
</div>
