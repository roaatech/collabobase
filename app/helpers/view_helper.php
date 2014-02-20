<?php

function operationResult($operationResult) {
    $alertType = $operationResult['code'] == 0 ? "alert-success" : ($operationResult['code'] < 0 ? "alert-warning" : "alert-danger");
    $resultType = $alertType == 'alert-success' ? __('Done!') : __('Error!');
    $message = __($operationResult['message']);
    $result = <<<XXX
        <div class="alert $alertType alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>$resultType</strong> {$message}
        </div>
XXX;
    return $result;
}
