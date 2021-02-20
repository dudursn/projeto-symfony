<?php

namespace App\Services;

/* Log de alterações
 * 05/07/2019: Criado copiando funções já usadas pelo JusPROC
*/

class DateService
{
    public static function now($formato = 'pt') {
        switch ($formato) {
            case 'pt': return date('d/m/Y');
            case 'en': return date('Y-m-d');
            case 'bd': return date('Y-m-d H:i:s');
            case 'file': return date('Y_m_d__H_i_s');
        }
        return '';
    }    

    public static function formata_datahora($dh, $par) {
        /* dh é a variavel data-hora a ser formatada
          par é o parametro do tipo String que pode ser passado com os seguintes caracteres:
          '*' como curinga, 'd' para pegar o dia, 'm' para pegar o mes numero, 'M' para pegar o mes texto, 'a' para pegar o ano
          'h' para pegar a hora, 'n' para pegar o minuto, 's' para pegar segundo;
          formataDataHora('1989-06-23 00:52:26', 'd/m/a h:n:s'); retorna '23/06/1989 00:52:26'
          formataDataHora('1989-06-23 00:52:26', '*Meu *a*niver*s*ario é e*m : d *de M'); retorna 'Meu aniversario é em : 23 de Junho' */
        if (strlen($dh) != 19)
            return "";
        $dia = substr($dh, 8, 2);
        $mes = substr($dh, 5, 2);
        $ano = substr($dh, 0, 4);
        $hor = substr($dh, 11, 2);
        $min = substr($dh, 14, 2);
        $seg = substr($dh, 17, 2);
        $meses = array("", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");
        $res = "";
        $i = (int) -1;
        while ($i < strlen($par)) {
            $i++;
            if (substr($par, $i, 1) == '*')
                $res.= substr($par, ++$i, 1);
            else if (substr($par, $i, 1) == 'd')
                $res.= $dia;
            else if (substr($par, $i, 1) == 'm')
                $res.= $mes;
            else if (substr($par, $i, 1) == 'M')
                $res.= $meses[(int) ($mes)];
            else if (substr($par, $i, 1) == 'a')
                $res.= $ano;
            else if (substr($par, $i, 1) == 'h')
                $res.= $hor;
            else if (substr($par, $i, 1) == 'n')
                $res.= $min;
            else if (substr($par, $i, 1) == 's')
                $res.= $seg;
            else
                $res.= substr($par, $i, 1);
        }
        return $res;
    }

    public static function formata_data($dt, $par) {
        return Rtl_Data::formata_datahora($dt . " **:**:**", $par);
    }

    public static function formata_hora($hr, $par) {
        return Rtl_Data::formata_datahora("****-**-** " . $hr, $par);
    }
    
    public static function isValid($data, $tipo = '/')
    {
        if(!$tipo) {
            if(strpos($data, '/'))
                $tipo = '/';
            else if(strpos($data, '-'))
                $tipo = '-';
            else 
                return false;
        }
        $data_vetor = explode($tipo, $data);
        if ($tipo == '/') {
            $dia = isset($data_vetor[0]) ? (int) $data_vetor[0] : 0;
            $mes = isset($data_vetor[1]) ? (int) $data_vetor[1] : 0;
            $ano = isset($data_vetor[2]) ? (int) $data_vetor[2] : 0;
        } else if ($tipo == '-') {
            $dia = isset($data_vetor[2]) ? (int) $data_vetor[2] : 0;
            $mes = isset($data_vetor[1]) ? (int) $data_vetor[1] : 0;
            $ano = isset($data_vetor[0]) ? (int) $data_vetor[0] : 0;
        }

        if (checkdate($mes, $dia, $ano)) return true;
        return false;
    }
    
    public static function converte($data, $para = null, $returnIfNotValid = null) {
        if(!$para) {
            if(strpos($data, '/'))
                $para = 'en';
            else if(strpos($data, '-'))
                $para = 'br';
            else 
                return $returnIfNotValid;
        }
        if ($para == "br") {
            if (!DateService::isValid($data))
                return $returnIfNotValid;
            $data_vetor = explode("-", $data);
            return $data_vetor[2] . '/' . $data_vetor[1] . '/' . $data_vetor[0];
        } else if ($para == "en") {
            if (!DateService::isValid($data))
                return $returnIfNotValid;
            $data_vetor = explode("/", $data);
            return $data_vetor[2] . '-' . $data_vetor[1] . '-' . $data_vetor[0];
        } else {
            return "Parametro 'para' incorreto: " . $para;
        }
    }

    public static function addMonthOnly(\DateTime $dateTime, $months = 1, $fixedDay = null): ?\DateTime
    {
        $day = $fixedDay?$fixedDay:$dateTime->format('d');
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), 1);
        $dateTime->add(new \DateInterval('P' . $months . 'M'));
        if($day > 28 && (int)$dateTime->format('m') == 2)
            $day = 28;
        if($day > 30 && in_array($dateTime->format('m'), [4, 6, 9, 11]))
            $day = 30;
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), $day);
        return $dateTime;
    }    

    public static function addYearOnly(\DateTime $dateTime, $years = 1, $fixedDay = null): ?\DateTime
    {
        $day = $fixedDay?$fixedDay:$dateTime->format('d');
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), 1);
        $dateTime->add(new \DateInterval('P' . $years . 'Y'));
        if($day > 28 && (int)$dateTime->format('m') == 2)
            $day = 28;
        if($day > 30 && in_array($dateTime->format('m'), [4, 6, 9, 11]))
            $day = 30;
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), $day);
        return $dateTime;
    } 
    
    public static function addSemesterOnly(\DateTime $dateTime, $semesters = 1, $fixedDay = null): ?\DateTime
    {
        $day = $fixedDay?$fixedDay:$dateTime->format('d');
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), 1);
        $dateTime->add(new \DateInterval('P' . ($semesters * 6) . 'M'));
        if($day > 28 && (int)$dateTime->format('m') == 2)
            $day = 28;
        if($day > 30 && in_array($dateTime->format('m'), [4, 6, 9, 11]))
            $day = 30;
        $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), $day);
        return $dateTime;
    }    

    public static function addHalfMonthOnly(\DateTime $dateTime, $halfMonths = 1, $fixedDay = null): ?\DateTime
    {
        for($i = 0; $i < $halfMonths; $i++) {
            $day = $dateTime->format('d');
            if($day > 30)
                $day = 30;
            if($fixedDay) {
                if((int)$dateTime->format('d') > 15 && $fixedDay <= 15) {
                    $day = $fixedDay + 15;
                } else if((int)$dateTime->format('d') > 15 && $fixedDay > 15) {
                    $day = $fixedDay;
                } 
            }
            $dayPlus15 = $day <= 15?$day + 15:$day - 15;

            if((int)$dateTime->format('d') <= 15) {
                if($dayPlus15 > 28 && (int)$dateTime->format('m') == 2)
                    $dayPlus15 = 28;
                $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), $dayPlus15);
                
            } else {
                $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), 1);
                $dateTime->add(new \DateInterval('P1M'));
                $dateTime->setDate($dateTime->format('Y'), $dateTime->format('m'), $dayPlus15);
            }
        }
        return $dateTime;
    }    

    public static function countDiasUteis(\DateTime $inicialDate, \DateTime $finalDate, $holidaysRegion = 'br'): ?int
    {
        if($inicialDate > $finalDate)
            return 0;
        $holidays = $holidaysRegion?DateService::getHolidaysArray($holidaysRegion):[];
        // Cria um periodo de data para iterar em um loop ('P1D' é igual a i dia)
        $period = new \DatePeriod($inicialDate, new \DateInterval('P1D'), $finalDate);
        $interval = $inicialDate->diff($finalDate);
        $days = $interval->days;
        
        foreach($period as $date) {
            if ($date->format('D') == 'Sat' || $date->format('D') == 'Sun') {
                $days--;
            } elseif (in_array($date->format('m-d'), $holidays)) {
                $days--;
            } elseif (in_array($date->format('Y-m-d'), $holidays)) {
                $days--;
            }
        }
        return $days;
    }    

    public static function getHolidaysArray($holidaysRegion = 'br')
    {
        $holidays = [
            'br' => [
                '01-01',
                '02-24',
                '02-25',
                '04-10',
                '04-21',
                '05-01',
                '06-11',
                '09-07',
                '10-12',
                '11-02',
                '11-15',
                '12-25',
            ],
        ];
        return $holidays[$holidaysRegion];
    }    

    /* ..... Confirmar uso .... */
       

//5- FUNÇÃO QUE VALIDA A DATA
    /* todas a verificações necessárias como por exemplo: se mês está entre 1 e 12,
      verificar se o dia está dentro dos dias permitidos para aquele mês (leva em consideração os anos bissextos) e verificar se o ano é válido. */
//OBS.: As datas enviadas podem estar no formato inglês (en) ou brasileiro (pt).
//valida_data('pt', '27/07/2009'); retorna: true
//valida_data('en', '2009-07-27'); retorna true
    public static function difference($d1, $d2, $type = 'd', $sep = '-') {
        if (!Rtl_Data::valida_data($d1, '-')) {
            if (Rtl_Data::valida_data($d1, '/'))
                $d1 = Rtl_Data::converte($d1, 'en');
            else
                return 'Rtl_Data::difference Error D1: Invalid Date Format';
        }
        if (!Rtl_Data::valida_data($d2, '-')) {
            if (Rtl_Data::valida_data($d2, '/'))
                $d2 = Rtl_Data::converte($d2, 'en');
            else
                return 'Rtl_Data::difference Error D2: Invalid Date Format';
        }
        $d1 = explode($sep, $d1);
        $d2 = explode($sep, $d2);
        switch ($type) {
            case 'a':
                $X = 31536000;
                break;
            case 'm':
                $X = 2592000;
                break;
            case 'd':
                $X = 86400;
                break;
            case 'h':
                $X = 3600;
                break;
            case 'mi':
                $X = 60;
                break;
            default:
                $X = 1;
        }
        return floor(((mktime(0, 0, 0, $d2[1], $d2[2], $d2[0]) - mktime(0, 0, 0, $d1[1], $d1[2], $d1[0])) / $X));
    }
    
    public static function addToDate($data, $dias = 0, $meses = 0, $ano = 0){
        $data = explode("/", $data);
        return date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses, $data[0] + $dias, $data[2] + $ano));
    }

}