<?php

namespace app\models;

use yii\base\Model;
use DateTime;
use DateInterval;
use DatePeriod;

class ExamForm extends Model
{
    public $examName;
    public $daysToPrepare;
    public $examDate;

    public function rules()
    {
        return [
            [['examName', 'daysToPrepare', 'examDate'], 'required', 'message' => 'Поле пустое'],
            ['daysToPrepare', 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number', 'message' => 'Число должно быть больше нуля'],
            ['examName', 'match', 'pattern' => '/^[a-z]\w*$/i', 'message' => 'Только латинские буквы'],
            ['examName', 'string', 'length' => [0, 10], 'message' => 'Введите до 10 символов'],
        ];
    }

    static function sumDaysToPrepare($examForms)
    {   
        $sum = 0;
        for ($i=0; $i < count($examForms['examName']); $i++) {
            $sum = $sum + $examForms['daysToPrepare'][$i];
        }

        return $sum;
    }

    static function daysBeforeLastExam($examForms)
    {
        $currentDate = date("d-m-Y");
        $lastExamDate = $examForms['examDate'][0];

        $currentDate = new DateTime($currentDate);
        $lastExamDate = new DateTime($lastExamDate);

        for ($i=0; $i < count($examForms['examName']); $i++) { 
            $dateExam = new DateTime($examForms['examDate'][$i]);
            if ($dateExam < $currentDate) {
                ExamForm::notPass('Экзамен ' . $examForms['examName'][$i] . ' уже прошел!');
                die();
            }
        }

        $interval = $currentDate->diff($lastExamDate);

        return $interval->format('%R%a');
    }

    static function examsSort($examForms)
    {
        for($i = 0; $i < count($examForms['examName']); $i++){
            for($j = 0; $j < (count($examForms['examName']) - 1); $j++){
                if($examForms['examDate'][$j] < $examForms['examDate'][$j+1]){
                    $tempName = $examForms['examName'][$j];
                    $tempDays = $examForms['daysToPrepare'][$j];
                    $tempDate = $examForms['examDate'][$j];

                    $examForms['examName'][$j] = $examForms['examName'][$j+1];
                    $examForms['daysToPrepare'][$j] = $examForms['daysToPrepare'][$j+1];
                    $examForms['examDate'][$j] = $examForms['examDate'][$j+1];

                    $examForms['examName'][$j+1] = $tempName;
                    $examForms['daysToPrepare'][$j+1] = $tempDays;
                    $examForms['examDate'][$j+1] = $tempDate;
                }

                $dateExam = new DateTime($examForms['examDate'][$j]);
                $dateNextExam = new DateTime($examForms['examDate'][$j+1]);
                
                if($dateExam == $dateNextExam){
                    ExamForm::notPass('Экзамены ' . $examForms['examName'][$j] . ' и ' . $examForms['examName'][$j+1] . ' в один день, ты не успеешь подготовиться!');
                    die();
                }elseif( $dateExam->add(new DateInterval('P1D')) ==  $dateNextExam || 
                         $dateExam->sub(new DateInterval('P2D')) ==  $dateNextExam ){
                    ExamForm::notPass('Экзамены ' . $examForms['examName'][$j] . ' и ' . $examForms['examName'][$j+1] . ' идут подряд и тебе не хватит времени чтобы подготовиться!');
                    die();
                }
            }
        }
        return $examForms;
    }

    static function notPass($cause)
    {
        echo "Ты не успеешь подготовиться! Потому что: " . $cause;
    }

    static function calculate($examForms)
    {   
        $countExams =  count($examForms['examName']);
        $examForms = ExamForm::examsSort($examForms);
        $sumDaysToPrepare = ExamForm::sumDaysToPrepare($examForms);
        $daysBeforeLastExam = ExamForm::daysBeforeLastExam($examForms);     

        if ($sumDaysToPrepare > ($daysBeforeLastExam - $countExams)) {
            ExamForm::notPass('Дней на подготовку нужно больше чем дней до последнего экзамена!');
            die();
        }

        $start = new DateTime(date("d-m-Y"));
        $end = new DateTime($examForms['examDate'][0]);
        $end = $end->add(new DateInterval('P1D'));

        $calendar = array();

        
        $calendarDay = $start;

        while ($start != $end) {
            $day = $calendar[$calendarDay->format('d-m-Y')] = 'Свободный день';

            $allocatedDays = 0;
            
            for($i = 0; $i < $countExams; $i++){
                if($countExams > 1){
                    if ($examForms['examDate'][$i] == $examForms['examDate'][0]) {              
                        $daysBetweenExams = ExamForm::daysBetweenExams($examForms['examDate'][$i], $examForms['examDate'][$i+1]);
                        $prevExam = $examForms['examDate'][$i];                   
                    }elseif($examForms['examDate'][$countExams-1] == $examForms['examDate'][$i]){
                        $daysBetweenExams = ExamForm::daysBetweenExams($examForms['examDate'][$i], date("d-m-Y"));
                    }else{
                        $daysBetweenExams = ExamForm::daysBetweenExams($examForms['examDate'][$i], $examForms['examDate'][$i+1]);
                    }
                }else{
                    $daysBetweenExams = ExamForm::daysBetweenExams($examForms['examDate'][$i], date("d-m-Y"));
                }
                $examDay =  new DateTime($examForms['examDate'][$i]);
                if ($calendarDay->format('d-m-Y') == $examDay->format('d-m-Y')) {

                    $allocatedDays = 0;
                    
                    $calendar[$calendarDay->format('d-m-Y')] = 'ЭКЗАМЕН по предмету ' . $examForms['examName'][$i];
               
                    $daysToPrepare = $examForms['daysToPrepare'][$i] - $allocatedDays;                 
                    
                    $prevDay = new DateTime($calendarDay->format('d-m-Y'));
    
                    if (($daysBetweenExams - $allocatedDays) >= $examForms['daysToPrepare'][$i]) {
                        while ($daysToPrepare != 0) {
                            $prevDay->sub(new DateInterval('P1D'));

                            if ($calendar[$prevDay->format('d-m-Y')] == 'Свободный день') {

                                $calendar[$prevDay->format('d-m-Y')] = 'Подготовка к экзаменту ' . $examForms['examName'][$i];

                                $daysToPrepare--;
                                $allocatedDays++;
                            }         
                        }                    
                    }else{
                        ExamForm::notPass('Перед экзаменом ' . $examForms['examName'][$i] . ' недостаточно дней на подготовку! Нужно ' . $examForms['daysToPrepare'][$i] . 'дня(ей) на подготовку, а у тебя доступных дней: '. $daysBetweenExams);
                        die();
                        
                    }
                }
            }
            $calendarDay = $calendarDay->add(new DateInterval('P1D'));
        }

        $firstDay = new DateTime(date("d-m-Y"));
        $calendar[$firstDay->format('d-m-Y')] = 'Распределительный день';

        return $calendar;
    }

    static function daysBetweenExams($examA, $examB)
    {
        $examA = new DateTime($examA);
        $examB = new DateTime($examB);

        $interval = $examB->diff($examA);

        return $interval->format('%R%a');
    }
}