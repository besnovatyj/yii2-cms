<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFilters" aria-labelledby="offcanvasFiltersLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasFiltersLabel">Фильтры поиска</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?php if (!empty($this->params['mobileFiltersForm'])): ?>
            <?= $this->params['mobileFiltersForm'] ?>
        <?php else: ?>
            <div id="mobile-filters-placeholder"></div>
        <?php endif; ?>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" form="mobile-filter-form" class="btn btn-primary btn-apply-mobile-filters">Применить</button>
            <button type="button" class="btn btn-outline-secondary" onclick="location.href='<?= \yii\helpers\Url::to(['index']) ?>'">Сбросить</button>
        </div>
    </div>
</div>
