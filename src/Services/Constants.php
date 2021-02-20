<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Constants
{
    //private $request;
    
    public function __construct(RequestStack $requestStack) {
        $this->request = $requestStack->getCurrentRequest();
    }
    
    public static function lojasNumeracoes($value = null) {
        $values = ['sequencia' => 'Seqüência', 'sequencia_ano' => 'Seqüência / Ano Atual', 'sequencia_ano_reiniciando' => 'Seqüência Anual / Ano Atual', 'sem_numeracao' => 'Sem numeração automática'];
        return $value ? $values[$value] : $values;
    }

    public static function lojasNumeracoesChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::lojasNumeracoes() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pessoasTipos($value = null) {
        /*  Values: 1. Pessoa Física, 2. Pessoa Jurídica */
        $values = [1 => 'Pessoa Física', 2 => 'Pessoa Jurídica'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function pessoasTiposChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pessoasTipos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pessoasSexos($value = null) {
        /***   Values: 0. Ignorado, 1. Masculino, 2. Feminino   ***/
        $values = [0 => 'Ignorado', 1 => 'Masculino', 2 => 'Feminino'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function pessoasSexosChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pessoasSexos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function candidatosMandatos($value = null) {
        /***   Values: 0. Ignorado, 1. Masculino, 2. Feminino   ***/
        $values = ['presidente' => 'Presidente'];
        return $value ? $values[$value] : $values;
    }

    public static function candidatosMandatosChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::candidatosMandatos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pessoasEstadosCivis($value = null) {
        $values = [1 => 'Solteiro', 2 => 'Casado', 3 => 'União Estável', 4 => 'Viúvo', 5 => 'Divorciado'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function pessoasEstadosCivisChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pessoasEstadosCivis() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pessoasEscolaridades($value = null) {
        $values = [1 => 'Analfabeto', 2 => 'Fundamental Incompleto', 3 => 'Fundamental Completo', 4 => 'Médio Incompleto', 5 => 'Médio Completo', 6 => 'Superior Incompleto', 7 => 'Superior Completo', 8 => 'Pós graduação (Lato senso) Incompleto', 9 => 'Pós graduação (Lato senso) Completo', 10 => 'Pós graduação (Stricto sensu, nível mestrado) Incompleto', 11 => 'Pós graduação (Stricto sensu, nível mestrado)  Completo', 12 => 'Pós graduação (Stricto sensu, nível doutor) Incompleto', 13 => 'Pós graduação (Stricto sensu, nível doutor) Completo'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function pessoasEscolaridadesChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pessoasEscolaridades() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function colaboradoresRoles($value = null) {
        /*  Níveis de Acesso de Colaboradores
         * 1. Administrador (ROLE_LOJA_ADMIN)
         * 2. Gerente (ROLE_LOJA_GERENTE)
         * 3. Caixa (ROLE_LOJA_CAIXA)
         * 4. Vendedor (ROLE_LOJA_VENDAS)
         * 4. Secretária (ROLE_LOJA_SECRETARIA)
         * 5. Setor de Compras (ROLE_LOJA_COMPRAS)
         * 6. Setor de Cobranças (ROLE_LOJA_COBRANCA)
         * 7. Comissionado (ROLE_LOJA_COMISSIONADO)
        */
        $values = [
            'ROLE_LOJA_ADMIN' => 'Administrador',
            'ROLE_LOJA_GERENTE' => 'Gerente',
            'ROLE_LOJA_CAIXA' => 'Caixa',
            'ROLE_LOJA_VENDAS' => 'Vendedor',
            'ROLE_LOJA_SECRETARIA' => 'Secretária',
            'ROLE_LOJA_COMPRAS' => 'Setor de Compras',
            'ROLE_LOJA_COBRANCA' => 'Setor de Cobranças',
            'ROLE_LOJA_COMISSIONADO' => 'Comissionado',
        ];
        return $value ? $values[$value] : $values;
    }

    public static function colaboradoresRolesChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::colaboradoresRoles() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function arquivosTipos($value = null) {
        /*  Values: 1. Pessoa Física, 2. Pessoa Jurídica */
        $values = ['sem_tipo' => 'Sem Tipo', 'documentos' => 'Documentos', 'imagens' => 'Imagens'];
        return $value ? $values[$value] : $values;
    }

    public static function arquivosTiposChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::arquivosTipos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }    
    
    public static function arquivosMimeTypes($escopo = null)
    {
        $texto = [
            'text/plain', 'text/rtf', 
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/rtf', 
        ];
        $imagem = [
            'image/jpeg', 'image/png', 'image/gif',
        ];
        $zip = [
            'application/zip', 'application/x-rar-compressed', 'application/x-rar', 
        ];
        $planilha = [
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
        ];
        $musica = [
            'audio/x-wav', 
            'audio/mpeg', 
            'audio/mp3', 
        ];
        $video = [
            'video/x-msvideo', 
            'audio/mpeg', 
        ];
        $geral = [
            'application/vnd.ms-powerpoint', 
            'text/html', 
        ];
        if($escopo == 'imagens' || $escopo == 'imagem') 
            $values = $imagem;
        else if ($escopo == 'documentos') 
            $values = array_merge($texto, $planilha);
        else if ($escopo == 'receita-oftalmologica') 
            $values = array_merge($texto, $imagem, $planilha, $zip);
        else
            $values = array_merge($texto, $imagem, $zip, $planilha, $musica, $video, $geral);
        
        return $values;
        //return $value ? $values[$value] : $values;
    }

    public static function arquivosExtIcons($ext = null)
    {
        $values = [
            'pdf' => 'file_pdf.png',
            'doc' => 'file_doc.png',
            'docx' => 'file_doc.png',
            'xls' => 'file_xls.jpg',
            'xlsx' => 'file_xls.jpg',
            'ppt' => 'file_ppt.jpg',
            'pptx' => 'file_ppt.jpg',
            'zip' => 'file_zip.jpg',
            'rar' => 'file_zip.jpg',
            'tar' => 'file_zip.jpg',
            '7z' => 'file_zip.jpg',
        ];
        return $ext?$values[$ext]:$values;
    }

    public static function categoriasEscopos()
    {
        $values = ['itens', 'pessoas', 'pedidos', 'cotacoes', 'compras', 'pagamentos', 'despesas', 'tarefas'];
        return $values;
    }

    public static function categoriasEscoposTitulos($escopo = null)
    {
        $values = ['itens' => 'Categoria de Itens de Estoque', 'pessoas' => 'Categoria de Pessoas', 'pedidos' => 'Categoria de Pedidos', 'cotacoes' => 'Categoria de Cotações', 'compras' => 'Categoria de Compras', 'pagamentos' => 'Categoria de Pagamentos', 'despesas' => 'Categoria de Despesas', 'tarefas' => 'Categoria de Tarefas'];
        return $escopo?$values[$escopo]:$values;
    }

    public static function categoriasEscoposChoiceArray()
    {
        $values = ['Categoria de Itens de Estoque' => 'itens', 'Categoria de Pessoas' => 'pessoas', 'Categoria de Pedidos' => 'pedidos', 'Categoria de Cotações' => 'cotacoes', 'Categoria de Compras' => 'compras', 'Categoria de Pagamentos' => 'pagamentos', 'Categoria de Despesas' => 'despesas', 'Categoria de Tarefas' => 'tarefas'];
        return $values;
    }

    public static function itensMedidas($value = null)
    {
        $values = ['AMPOLA' => 'AMPOLA', 'BALDE' => 'BALDE', 'BANDEJ' => 'BANDEJA', 'BARRA' => 'BARRA', 'BISNAG' => 'BISNAGA', 'BLOCO' => 'BLOCO', 'BOBINA' => 'BOBINA', 'BOMBONA' => 'BOMBONA', 'CAP' => 'CÁPSULAS', 'CARTELA' => 'CARTELA', 'CENTO' => 'CENTO', 'CJ' => 'CONJUNTO', 'CM' => 'CENTÍMETRO', 'CM2' => 'CENTIMETRO QUADRADO', 'CX' => 'CAIXA', 'DUZIA' => 'DUZIA', 'EMBAL' => 'EMBALAGEM', 'FARDO' => 'FARDO', 'FOLHA' => 'FOLHA', 'FRASCO' => 'FRASCO', 'GALAO' => 'GALÃO', 'GF' => 'GARRAFA', 'GRAMAS' => 'GRAMAS', 'JOGO' => 'JOGO', 'KG' => 'QUILOGRAMA', 'KIT' => 'KIT', 'LATA' => 'LATA', 'LITRO' => 'LITRO', 'M' => 'METRO', 'M2' => 'METRO QUADRADO', 'M3' => 'METRO CÚBICO', 'MILHEI' => 'MILHEIRO', 'ML' => 'MILILITRO', 'MWH' => 'MEGAWATT HORA', 'PCT' => 'PACOTE', 'PALETE' => 'PALETE', 'PAR' => 'PAR', 'PC' => 'PEÇA', 'K' => 'QUILATE', 'RESMA' => 'RESMA', 'ROLO' => 'ROLO', 'SACO' => 'SACO', 'SACOLA' => 'SACOLA', 'TAMBOR' => 'TAMBOR', 'TANQUE' => 'TANQUE', 'TON' => 'TONELADA', 'TUBO' => 'TUBO', 'UNI' => 'UNIDADE', 'VASIL' => 'VASILHAME', 'VIDRO' => 'VIDRO'];
        return $value?$values[$value]:$values;
    }

    public static function itensMedidasJsonData()
    {
        return json_encode(array_keys(self::itensMedidas()));
    }
    
    public static function itensAjustesTipos($value = null) {
        $values = [1 => 'Adição', 2 => 'Retirada'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function itensAjustesTiposChoiceArray($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::itensAjustesTipos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pessoasVinculosVinculos($escopo = null, $value = null) {
        $pfPf = ['pai' => 'Pai', 'mae' => 'Mãe', 'filho' => 'Filho(a)', 'avo' => 'Avô/Avó', 'neto' => 'Neto(a)', 'irmao' => 'Irmão(ã)', 'conjuge' => 'Cônjuge', 'amigo' => 'Amigo', 'outro' => 'Outro'];
        $pfPj = ['colaborador' => 'Colaborador', 'contato' => 'Contato', 'responsavel' => 'Responsável', 'proprietario' => 'Proprietário', 'outro' => 'Outro'];
        $pjPf = ['empresa' => 'Empresa'];
        $pjPj = ['filial' => 'Filial', 'matriz' => 'Matriz', 'empresa_associada' => 'Empresa Associada', 'outro' => 'Outro'];
        switch ($escopo) {
            case 'pf_pf':
                $values = $pfPf;
                break;
            case 'pf_pj':
                $values = $pfPj;
                break;
            case 'pj_pf':
                $values = $pjPf;
                break;
            case 'pj_pj':
                $values = $pjPj;
                break;
            default:
                $values = array_merge($pfPf, $pfPj, $pjPf, $pjPj);
                break;
        }
        
        return $value ? $values[$value] : $values;
    }

    public static function pessoasVinculosVinculosChoices($escopo = null, $firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pessoasVinculosVinculos($escopo) as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pedidosSituacoes($value = null) {
        $values = [1 => 'Ativo', 2 => 'Concluído', 3 => 'Cancelado'];
        return is_numeric($value) && $value ? $values[$value] : $values;
    }

    public static function pedidosSituacoesChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pedidosSituacoes() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pedidosCiclos($value = null) {
        // Valores modificados nesta função precisam ser atualizados em Constats::pedidosCiclosIntervalSpecs()
        $values = ['mensal' => 'Mensal', 'anual' => 'Anual', 'semestral' => 'Semestral', 'quinzenal' => 'Quinzenal', 'semanal' => 'Semanal', '10dias' => 'A cada 10 Dias', 'diario' => 'Diário', 'bienal' => 'Bienal'];
        return is_null($value) ? $values : $values[$value];
    }

    public static function pedidosCiclosChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pedidosCiclos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pagamentosFormas($value = null) {
        $values = ['dinheiro' => 'Dinheiro', 'transferencia' => 'Transferência/Depósito Bancário', 'cheque' => 'Cheque', 'cartao' => 'Cartão no Estabelecimento', 'boleto' => 'Boleto Externo', 'cobranca_online' => 'Cobrança Online', 'boleto_convenio' => 'Boleto Com Convênio', 'outra' => 'Outra forma de pagamento'];
        return $value ? $values[$value] : $values;
    }

    public static function pagamentosFormasChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pagamentosFormas() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pagamentosSituacoes($value = null) {
        $values = [0 => 'Em Aberto', 1 => 'Quitado', 2 => 'Cancelado'];
        return is_numeric($value) ? $values[$value] : $values;
    }

    public static function pagamentosSituacoesChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pagamentosSituacoes() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
    public static function pagamentosCartaoModos($value = null) {
        $values = ['credito' => 'Crédito', 'debito' => 'Débito'];
        return $value ? $values[$value] : $values;
    }

    public static function pagamentosCartaoModosChoices($firstItem = null) {
        $array = [];
        if ($firstItem)
            $array[$firstItem] = '';
        foreach (Constants::pagamentosCartaoModos() as $key => $value)
            $array[$value] = $key;
        
        return $array;
    }
    
}

