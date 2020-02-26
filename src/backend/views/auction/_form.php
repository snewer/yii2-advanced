<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\Auction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="auction-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'post_id')->textInput() ?>

    <?= $form->field($model, 'openAt')->widget(MaskedInput::class, ['mask' => '99.99.9999 99:99']) ?>

    <?= $form->field($model, 'closeAt')->widget(MaskedInput::class, ['mask' => '99.99.9999 99:99']) ?>

    <p>Текущее время: <?= date('d.m.Y H:i:s') ?></p>

    <?= $form->field($model, 'start')->textInput() ?>

    <?= $form->field($model, 'blitz')->textInput() ?>

    <?= $form->field($model, 'current_price')->textInput() ?>

    <?= $form->field($model, 'bids_count')->textInput() ?>

    <?= $form->field($model, 'step')->textInput() ?>

    <?= $form->field($model, 'anti_sniper')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
