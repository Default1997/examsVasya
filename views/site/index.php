<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */

$this->title = 'Расчет подготовки к сессии';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Сессия близко, Василий!</h1>

        <p class="lead">Вбей свои предметы и узнай в каком порядке готовиться к экзаменам.</p>
    </div>

    <div class="body-content">
        <div class="row">
            
            <?php 


            Pjax::begin(['id' => 'my-pjax']);
                $form = ActiveForm::begin(['options' => ['data-pjax' => false, 'name' => 'form']]);
                
                foreach ($examForms as $index => $examForm) {
                    echo "<div class='col-sm-4'>";
                    echo $form->field($examForms[$index], "examName[$index]")->label('Название предмета');
                    echo "</div>";

                    // echo "<pre>";
                    // print_r($examForm->oldAttributes);
                    // echo "</pre>";

                    echo "<div class='col-sm-4'>";
                    echo $form->field($examForms[$index],"daysToPrepare[$index]")->label('Дней на подготовку')->textInput(['type' => 'number']); ;
                    echo "</div>";

                    echo "<div class='col-sm-4'>";
                    echo $form->field($examForms[$index],"examDate[$index]")->label('Дата экзамена')->textInput(['type' => 'date']); 
                    echo "</div>";            
                }

                echo "<div class='col-sm-4'>";
                echo Html::submitButton('Рассчитать', ['class' => 'btn btn-success btn-block', 'name' => 'calculate', 'value' => 'sendForCalculate']);
                echo "</div>";
                
                ActiveForm::end();

                $form = ActiveForm::begin(['options' => ['data-pjax' => true]]);
                echo "<div class='col-sm-4'>";
                    echo Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-block', 'name' => 'add', 'value' => 'create_add']);
                    echo "</div>";
                ActiveForm::end();

                $form = ActiveForm::begin(['options' => ['data-pjax' => true]]);
                echo "<div class='col-sm-4'>";
                    echo Html::submitButton('Сбросить', ['class' => 'btn btn-danger btn-block', 'name' => 'discard', 'value' => 'discard_fields']);
                    echo "</div>";
                ActiveForm::end();
            Pjax::end();

            ?>

        </div>
    </div>
</div>
