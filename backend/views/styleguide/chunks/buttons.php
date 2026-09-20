<!-- Buttons demo: варианты, размеры, состояния. Разметка — голый Bootstrap. -->

<div class="card my-3">
    <div class="card-header">Solid</div>
    <div class="card-body d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary">Primary</button>
        <button type="button" class="btn btn-secondary">Secondary</button>
        <button type="button" class="btn btn-success">Success</button>
        <button type="button" class="btn btn-danger">Danger</button>
        <button type="button" class="btn btn-warning">Warning</button>
        <button type="button" class="btn btn-info">Info</button>
        <button type="button" class="btn btn-orange">Orange</button>
        <button type="button" class="btn btn-teal">Teal</button>
        <button type="button" class="btn btn-purple">Purple</button>
        <button type="button" class="btn btn-pink">Pink</button>
    </div>
</div>

<div class="card my-3">
    <div class="card-header">Outline</div>
    <div class="card-body d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-primary">Primary</button>
        <button type="button" class="btn btn-outline-secondary">Secondary</button>
        <button type="button" class="btn btn-outline-success">Success</button>
        <button type="button" class="btn btn-outline-danger">Danger</button>
        <button type="button" class="btn btn-outline-warning">Warning</button>
        <button type="button" class="btn btn-outline-info">Info</button>
        <button type="button" class="btn btn-outline-orange">Orange</button>
        <button type="button" class="btn btn-outline-teal">Teal</button>
        <button type="button" class="btn btn-outline-purple">Purple</button>
        <button type="button" class="btn btn-outline-pink">Pink</button>
    </div>
</div>

<div class="card my-3">
    <div class="card-header">Размеры и состояния</div>
    <div class="card-body d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-primary btn-sm">Small</button>
        <button type="button" class="btn btn-primary">Default</button>
        <button type="button" class="btn btn-primary btn-lg">Large</button>
        <button type="button" class="btn btn-primary active">Active</button>
        <button type="button" class="btn btn-primary" disabled>Disabled</button>
        <button type="button" class="btn btn-outline-primary" disabled>Disabled outline</button>
        <button type="button" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>С иконкой
        </button>
    </div>
</div>

<div class="card my-3">
    <div class="card-header">Панель кнопок</div>
    <div class="card-body">
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            </div>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1">
                <i class="bi bi-calendar3"></i>
                This week
            </button>
        </div>
    </div>
</div>
