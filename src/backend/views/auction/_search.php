<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AuctionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="auction-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'description') ?>

    <?= $form->field($model, 'post_id') ?>

    <?= $form->field($model, 'open_at') ?>

    <?php // echo $form->field($model, 'close_at') ?>

    <?php // echo $form->field($model, 'start') ?>

    <?php // echo $form->field($model, 'blitz') ?>

    <?php // echo $form->field($model, 'current_price') ?>

    <?php // echo $form->field($model, 'bids_count') ?>

    <?php // echo $form->field($model, 'step') ?>

    <?php // echo $form->field($model, 'anti_sniper') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
