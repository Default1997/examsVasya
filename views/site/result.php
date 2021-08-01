<?php 
$this->title = 'Результат';

$keys = array_keys($calendar);


echo '<div class="container row ">';
foreach ($keys as $key) {
    if ($calendar[$key] == 'Свободный день') {
        echo '<div class="calendarBlock col-sm-3 mx-auto text-center box animate fadeInRight two">';
    }elseif (substr_compare($calendar[$key], 'Подготовка', 0, 10) == '0') {
        echo '<div class="calendarBlockPrepare col-sm-3 mx-auto text-center  box animate fadeInRight one">';
    }else{
        echo '<div class="calendarBlockExam col-sm-3 mx-auto text-center  box animate fadeInRight four">';
    }
    
    echo $key . '<br>' . $calendar[$key] . ' <br> ';
    echo '</div>';
}
echo '</div>';
?>