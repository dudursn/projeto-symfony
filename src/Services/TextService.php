<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

/* Log de alterações
 * 05/07/2019: Criado copiando funções já usadas pelo JusPROC
*/

class TextService
{
    public function __construct() {
        /* ... Classe Statica ... */ 
    }
    
    public static function spaceToPercent($string, $percentBefore = true, $percentAfter = true) {
        return ($percentBefore?'%':'') . str_replace(' ', '%', trim($string)) . ($percentAfter?'%':'');
    }
    
    public static function mask($val, $mask) {
        /* Use:
         *      mask('201206001', '####/##/##'); result: 2012/06/01
         */     
        $masked = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if($mask[$i] == '#') {
                if(isset($val[$k]))
                    $masked.= $val[$k++];
            } else {
                if(isset($mask[$i]))
                    $masked.= $mask[$i];
            }
        }
        return $masked;
    }

    public static function maskAsCpf($val) {
        return TextService::mask($val, '###.###.###-##');        
    }

    public static function maskAsCnpj($val) {
        return TextService::mask($val, '##.###.###/####-##');
    }

    public static function intToReal($valor, $showZero = true, $showSymbol = true) {
        $valor = (int)$valor;
        if ($valor == 0)
            return $showZero ? ($showSymbol ? 'R$ 0,00' : '0,00') : '';
        return ($showSymbol ? 'R$ ' : '') . number_format($valor / 100, 2, ',', '.');
    }

    public static function realToInt($valor) {
        $retirar = array('R$ ', ',', '.');
        return (int)str_replace($retirar, "", $valor);
    }

    public static function floatToReal($valor) {
        return TextService::intToReal(TextService::floatToInt($valor));
    }

    public static function realToFloat($valor) {
        $retirar = array('R$ ', 'R$', '.');
        $valor = str_replace($retirar, "", $valor);
        return TextService::textToFloat($valor);
    }

    public static function textToFloat($valor) {
        return floatval(str_replace (',', '.', $valor));
    }

    public static function floatToText($valor) {
        return str_replace ('.', ',', $valor);
    }

    public static function floatToInt($valor) {
        return $valor * 100;
    }

    /* ..... Confirmar uso .... */
    
    public static function textToUrl($string, $slug='-') {
        
        $string = utf8_decode(mb_strtolower($string, 'utf-8'));
        $ascii['a'] = range(224, 230);
        $ascii['e'] = range(232, 235);
        $ascii['i'] = range(236, 239);
        $ascii['o'] = array_merge(range(242, 246), array(240, 248));
        $ascii['u'] = range(249, 252);
        $ascii['b'] = array(223);
        $ascii['c'] = array(231);
        $ascii['d'] = array(208);
        $ascii['n'] = array(241);
        $ascii['y'] = array(253, 255);
        
        foreach ($ascii as $key=>$item) {
            $acentos = '';            
            foreach ($item AS $codigo) $acentos .= chr($codigo);
            $troca[$key] = '/['.$acentos.']/i';
        }
        $string = preg_replace(array_values($troca), array_keys($troca), $string);
        
        if ($slug) {
            $string = preg_replace('/[^a-z0-9]/i', $slug, $string);
            $string = preg_replace('/' . $slug . '{2,}/i', $slug, $string);
            $string = trim($string, $slug);
        }
        
        return $string;

    }
    
    public static function makeATag ($href, $label = '', $title = '', $atribs = array()) {
        // $atribs: array("nome_doatributo" => "valor do atributo"): class, style, target...
        $label = $label?$label:$href;
        $a = '<a title="' . $title . '" href="' . $href . '"';
        if(is_array($atribs) && count($atribs)) {
            foreach ($atribs as $key => $value) {
                $a.= ' ' . $key . '="' . $value . '"';
            }
        }
        $a.= '>' . $label . '</a>';
        return $a;
    }

    public static function cutText($text, $size, $ellipsis= true) {
        if (strlen($text) > $size) {
            if ($ellipsis) $text = substr($text, 0, $size - 2) . '...'; else $text = substr($text, 0, $size);
        }
        return $text;
    }

    public static function zeroFill($numero, $tamanho = 6){
        $zeros = '';
        for($n = $tamanho - strlen($numero); $n > 0; $n--) $zeros.= '0';
        return $zeros . $numero;
    }
    
    public static function replaceChars($string, $search, $replace = null){
        if($search == 'escape'){
            $search = array('"', "'");
            $replace = ' ';
        }
        return str_replace($search, $replace, $string);
    }
    
    public static function desconto($valor, $porcentagem) {
        return ceil(($porcentagem * (int)$valor) / 100);
    }

    public static function mkDir($dir) {
        if (!file_exists($dir))
            mkdir($dir);
        return true;
    }

    public static function fileDatePath($base_path) {
        $path = date('/Y/m');
        if(!file_exists ($base_path . $path))
            mkdir ($base_path . $path, 0777, true);
        return $path;
    }

    public static function fileEscapeName($filename) {
        $forbidden = array('"', "'", '/', "\\", ':', '*', '?', '<', '>', '|', '%', '&', '@');
        // $+!{=}
        return str_replace($forbidden, "_", $filename);
    }

    public static function renameUploadedFile($file_path, $new_name, $return = 'base_name') {
        $base = pathinfo($file_path, PATHINFO_BASENAME);
        $dir = pathinfo($file_path, PATHINFO_DIRNAME) . '/';
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $name = pathinfo($file_path, PATHINFO_FILENAME);
        $new_file_path = $dir . $new_name . '.' . $ext;
        $new_base_name = $new_name . '.' . $ext;

        $filterRename = new Zend_Filter_File_Rename(array('target' => $new_file_path, 'overwrite' => true));
        $filterRename->filter($file_path);

        if ($return == 'file_path')
            return $new_file_path;
        else if ($return == 'base_name')
            return $new_base_name;
        else {
            return array('file_path' => $new_file_path, 'base_name' => $new_base_name);
        }
    }

    public static function copyFile($file_path, $new_file_name, $return = 'ext') {
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $new_file_path = $new_file_name . '.' . $ext;
        if(!copy($file_path, $new_file_path)){
            return null;
        }
        switch ($return){
            case 'basename': return pathinfo($new_file_path, PATHINFO_BASENAME);
            case 'dirname': return pathinfo($new_file_path, PATHINFO_DIRNAME);
            case 'filename': return pathinfo($new_file_path, PATHINFO_FILENAME);
            case 'ext': return pathinfo($new_file_path, PATHINFO_EXTENSION);
            default: return $new_file_path;
        }
    }

    public static function removeFile($file_path) {
        if (file_exists($file_path))
            return unlink($file_path);
        return false;
    }
    
    public static function filterUrl($url){
        if(!$url = trim($url)) return '';
        $strs = array('http://', 'https://');
        foreach ($strs as $str){
            if(substr($url, 0, strlen($str)) == $str)
                return $url;
        }
        return $strs[0] . $url;
    }
    
    public static function filterRedeSocialConta ($url, $rede = 'facebook') {
        $rede = strtolower($rede);
        $links = array('facebook.com/', 'fb.com/', 'twitter.com/', 'youtube.com/');
        foreach ($links as $link) {
            if(strpos($url, $link) !== false){
                return substr($url, strpos($url, $link) + strlen($link));
            }
        }
        return $url;       
    }
}
