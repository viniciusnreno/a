<?php
if (! function_exists('dateBR')) {
    function dateBR($date, $hasTime){
        $expTime = explode( ' ', $date );
        $expDate = explode( '-', $expTime[0] );
        
        $out = $expDate[2] . '/' . $expDate[1] . '/' . $expDate[0];
        
        if( $hasTime === true ){
            $out .= ' ' . $expTime[1];
        }
        return $out;
    }
}

if (! function_exists('dateEN')) {
    function dateEN( $data, $showHours = true ){
        $expHora = explode( ' ', $data );
        $expData = explode( '/', $expHora[0] );
    
        $out =  $expData[2] . '-' . $expData[1] . '-' . $expData[0];
        if( $showHours === true )
        {
            $out .= ' ' . $expHora[1] . ':00';
        }
        return $out;
    }
}

if (! function_exists('timeFromDate')) {
    function timeFromDate($date){
        $expTime = explode( ' ', $date );
        
        return $expTime[1];
    }
}

if (! function_exists('validCPF')) {
    function validCPF($cpf) {

        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
}

if (! function_exists('validCNPJ')) {
    function validCNPJ($cnpj){

        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        if (strlen($cnpj) != 14)
            return false;

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)){
            return false;
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}

if (! function_exists('couponStatus')) {
    function couponStatus($status){
        switch($status){
            case '1': 
                $out = '<span class="text-success">Validado</span>';
            break;
            case '0': 
                $out = '<span class="text-danger">Inválido</span>';
            break;
            default:
                $out = '<span class="text-dark">Em Análise</span>';
        }
        return $out;
    }
}

if (! function_exists('selectedValue')) {
    function selectedValue($seekVal, $currentVal){
        
        $out = "";

        if($seekVal == $currentVal){
            $out = 'selected="selected"';
        }
        
        return $out;
    }
}

if (! function_exists('readCSV')) {
    function readCSV($csvFile, $array)
    {
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 0, $array['delimiter']);
        }
        fclose($file_handle);
        return $line_of_text;
    }
}
