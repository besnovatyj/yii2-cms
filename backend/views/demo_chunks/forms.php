<!-- Forms demo: required fields, validation states -->
<div class="card my-3">
    <div class="card-header">Forms — required, valid/invalid states</div>
    <div class="card-body">
        <form>
            <div class="row g-3">
                <!-- Required text -->
                <div class="col-md-6 required">
                    <label class="form-label" for="demo-name">Имя</label>
                    <input type="text" class="form-control" id="demo-name" placeholder="Введите имя" required>
                </div>
                <!-- Required email -->
                <div class="col-md-6 required">
                    <label class="form-label" for="demo-email">Email</label>
                    <input type="email" class="form-control" id="demo-email" placeholder="name@example.com" required>
                </div>
                <!-- Optional select -->
                <div class="col-md-6">
                    <label class="form-label" for="demo-role">Роль</label>
                    <select class="form-select" id="demo-role">
                        <option selected>Выберите роль...</option>
                        <option>Admin</option>
                        <option>Editor</option>
                        <option>Viewer</option>
                    </select>
                </div>
                <!-- Textarea required -->
                <div class="col-12 required">
                    <label class="form-label" for="demo-comment">Комментарий</label>
                    <textarea class="form-control" id="demo-comment" rows="3" placeholder="Текст..."></textarea>
                </div>
                <!-- Validated states -->
                <div class="col-md-6">
                    <label class="form-label" for="demo-valid">Валидное поле</label>
                    <input type="text" class="form-control is-valid" id="demo-valid" value="Верно">
                    <div class="valid-feedback">Отлично!</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="demo-invalid">Невалидное поле</label>
                    <input type="text" class="form-control is-invalid" id="demo-invalid" value="Ошибка">
                    <div class="invalid-feedback">Введите корректное значение.</div>
                </div>
                <!-- Checkbox -->
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="demo-check">
                        <label class="form-check-label" for="demo-check">Согласен с условиями</label>
                    </div>
                </div>
                <!-- Кнопки -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-floppy me-1"></i>Сохранить
                    </button>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Сбросить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
