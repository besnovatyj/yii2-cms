<?php $this->registerJs(file_get_contents(__DIR__ . '/PasswordDecoder.js'), \yii\web\View::POS_END); ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const decoder = new PasswordDecoder('decoderContainer');
    });
</script>

<div class="card">
    <div class="card-header d-md-flex justify-content-md-between">
        <div class="pt-1">Дешифровка пароля из экспорта настроек HeidiSQL</div>
        <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#HeidiSQL_password_decoder" role="button"
           aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-plus-lg"></i>
            <i class="bi bi-dash-lg"></i>
        </a>
    </div>
    <div class="collapse" id="HeidiSQL_password_decoder">
        <div class="card-body">
            <div id="decoderContainer"></div>
        </div>
    </div>
</div>

<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// PHP версия данного декодера - https://gist.github.com/trevorbicewebdesign/6b747ba8e00a2e9f8001
//function decodeHeidisql($hex) {
//    $string	= '';
//    $shift 	= substr($hex, -1, 1);
//    $hex 	= substr($hex, 0, -1);
//    for($i=0;$i<strlen($hex); $i += 2) {
//        $string .= chr(intval(substr($hex, $i, 2), 16)-$shift);
//    }
//
//    return $string;
//}
//echo decodeHeidisql('755A5A585C3D8141786B3C385E3A393');

?>
