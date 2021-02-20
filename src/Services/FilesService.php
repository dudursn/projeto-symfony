<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

/* Log de alterações
 * 05/07/2019: Criado copiando funções já usadas pelo JusPROC
*/

class FilesService
{
    public function __construct() {
        /* ... Classe Statica ... */ 
    }
    
    public static function mkDir($dir) {
        if (!file_exists($dir))
            mkdir($dir);
        return true;
    }
    
    public static function getDatePath($basePath, $returnOnlyDatePath = false) {
        $datePath = date('/Y/m');
        $path = $basePath . $datePath;
        if(!file_exists ($path))
            mkdir ($path, 0777, true);
        return $returnOnlyDatePath ? $datePath : $path;
    }
    
    public static function getFileDatePrefix() {
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        return $now->format("Y_m_d_H_i_s_u") . '_';
        return '/' . \App\Services\DateService::now('file') . '_' . $key . '_';
    }
        
    public static function removeFile($filePath, $basePath = null) {
        if(!$filePath)
            return false;
        if($basePath)
            $filePath = $basePath . $filePath;
        if (file_exists($filePath))
            return unlink($filePath);
        return false;
    }
    
    public static function removeFiles(array $filePaths, $basePath = null) {
        $erros = [];
        if(!count($filePaths))
            return false;
        foreach ($filePaths as $filePath) {
            if(!FilesService::removeFile($filePath, $basePath))
                $erros[] = $filePath . ': Arquivo não deletado;';
        }
        return count($erros)?$erros:true;
    }
    
    public static function fileEscapeName($filename) {
        $forbidden = array('"', "'", '/', "\\", ':', '*', '?', '<', '>', '|', '%', '&', '@');
        // $+!{=}
        return str_replace($forbidden, "_", $filename);
    }
    
    /*
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
     * 
     */
    
    /*
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
    }*/

    
}
