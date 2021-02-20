/*
 * Log:
 * 30/10/2016: Adição da Função especifica formProcessoAtualizarNumero: para dinamizar o form do Processo;
 * 31/10/2016: Adição da Função fixBootstrapForms(): Para a correção da redenderização da Library Twiter_Bootstrap nos RadioButtons
 * 15/11/2016: Adição do Recurso Limpar Filtros: função limparFiltros();
 * 08/01/2017: 
 * - Reorganização da ordem das funções para ajustar o que é genérico e o que é espeífico de cada projeto!
 * - Adição da descrição de cada função
 * - Ajustes na função toggleCpfCnpj: Alteração do nome e melhoria nos arrays de CPF e CNPJ da função
 * - Ajustes na função rtlMultSelect para rtlSelectMultLevel
 * - Adição da função rtlSelectToSelect
 * 20/07/2017: Adição da função RtlShowHideDependente para esconder ou mostrar campos de acordo com o valor de outros
 * 18/09/2017: Adição da função rtlFormCheckAll para Marcar/Descmarcar vários checkboxes de uma vez
 * 03/10/2017: Adição da função rtlMultiSelect para dinamizar Selects do tipo Multiselects
 * 25/02/2018: Adição da função rtlProtectUnload para proteger dados de um form de serem perdidos por uma mudança acidental de página
 * 22/05/2018: Refatoramento da função rtlMultiSelect para dinamizar Selects, adicionando o recusdo de autocomletar e reformulando os Selects
 * 06/09/2018: Adição da Função maskAsDate para adicionar máscara de data e calendário aos INPUTs
 *    Passos para atualizar o Licitare:
 *       Colcoar a chamada da função no onLoad;
 *       Na função "setMasks()", comentar a chamda antiga do maskAsDate;
 *       Adicionar código da Função nova (Encontrar uma posição pra ela;
 *       Registrar no registro do Licitare;
 * 20/07/2019: Ajuste em excluirItemAjax para ajustar a rota ao Symfony
 * 20/08/2019: Eliminei a Função "toggleCpfCnpj", substituindo pela "cpfCnpjForm"
 * 08/11/2019: RtlMultiSelect com o recurso de Multi Level Select
 */

/* OPERAÇÕES ON LOAD */
$(function() {
    setMasks();
    /*
    rtlProtectUnload();
    */
    $('.maskAsDate').each(function(index, value) {
        maskAsDate($(this));
    });
    /*
    $('.cpfCnpjForm').each(function(index, value) {
        cpfCnpjForm($(this));
    });
    $('.rtlSelectMultLevel').each(function(index, value){
        rtlSelectMultLevel($(this), true);
    });
    $(document).on('change', 'select.rtlSelectMultLevel', function(){
        rtlSelectMultLevel($(this), false);
    });
    $('.rtlSelectToSelect').each(function(index, value){
        rtlSelectToSelect($(this), true);
    });
    $(document).on('change', '.rtlSelectToSelect', function(){
        rtlSelectToSelect($(this), false);
    });
    $('.force-icon').each(function(index, value) {
        var html = '<i class="fa fa-' + $(this).data('icon') + '"></i> ';
        $(this).prepend(html);        
    });
    $('.rtlShowHideDependente').each(function(index, value) {
        var referencia = $(this).closest('form').find("#"+$(this).data('referencia'));
        var campo = $(this);
        if(referencia.attr('type') === 'text') {
            $(referencia).on('input', function() {
                rtlShowHideDependente(campo, referencia, false);
            });
        } else {
            $(referencia).on('change', function() {
                rtlShowHideDependente(campo, referencia, false);
            });
        }
        rtlShowHideDependente(campo, referencia, true);
    });
    */
    $('.rtlMultiSelect').each(function(index, value) {
        rtlMultiSelect($(this));
    });
    /*
    $('.rtlUploader').each(function(index, value) {
        rtlUploader($(this), index);
    });
    $('.rtlCopyText').each(function(index, value) {
        rtlCopyText($(this));
    });
    */
    $('.form-horizontal').each(function(index, value) {
        fixBootstrapForms($(this)); //                                             apagar?
    });
    $('.form-inline').each(function(index, value) {
        fixBootstrapForms($(this));//                                             apagar?
    });
    $('.form-pesquisa').each(function(index, value) {
        fixBootstrapFormPesquisa($(this));//                                             apagar?
    });
});
/* / OPERAÇÕES ON LOAD */

/* Recentes: achar posição depois */

/* rtlUploader */
function rtlUploader (dom, uploaderIndex) {
    /*
     * Doc:
     * passar input para associar a um form... esse input vai sempre retornar vazio
     * Dependencia: jquery, bootstrap css fine uploader css e js e um script text/template para layout do uploader (vem com os arquivos do fineuploader o projeto atual já tem uma versão 
     */
    /********************************************************************************************   INICIALIZAÇÃO   */
    var endpoint = dom.data('endpoint');
    var thumbWaitingPath = dom.data('thumb-waiting-path');
    var thumbNotAvailablePath = dom.data('thumb-not-available-path');
    var sessionEndpoint = dom.data('session-endpoint');
    var deleteEndpoint = dom.data('delete-endpoint');
    
    var template = 'fineuploader-template-' + (dom.data('template')?dom.data('template'):'default');
    var autoUpload = dom.data('auto-upload'); // boolean, default true
    var deleteForceConfirm = dom.data('delete-force-confirm');//boolean, default false
    
    var dropZones = dom.data('drop-zones');
    if(dropZones) {
        if(dropZones.constructor !== Array)
            dropZones = [dropZones];
    } else
        dropZones = ['wrapper'];
    for (var i = 0; i < dropZones.length; i++)
        dropZones[i] = document.getElementById(dropZones[i]);
    
    var pasteArea = parseInt(dom.data('paste-area')) === 0 || dom.data('paste-area') === false?false:(dom.data('paste-area')?document.getElementById(dom.data('paste-area')):document.getElementById('wrapper'));
    var pasteName = dom.data('paste-name')?dom.data('paste-name'):'Imagem da Área de Transferência';
    
    var limit = dom.data('limit')?parseInt(dom.data('limit')):0;
    var limitMin = dom.data('limit-min')?parseInt(dom.data('limit-min')):0;
    
    var form = dom.data('form')? $('#' + dom.data('form')):false;
    var formClass = dom.data('form-class')? $('.' + dom.data('form-class')):false;
    var formParentLoop = dom.data('form-parent-loop')?parseInt(dom.data('form-parent-loop')):0;
    if(!form.length && formClass.length)
        form = formClass;
    if(form.length && formParentLoop > 0)
        for (var i = 0; i < formParentLoop; i++)
            form = form.parent();
    
    var dropAreaText = dom.data('drop-area-text')? dom.data('drop-area-text') :false;
    
    var onUploadRefresh = dom.data('on-upload-refresh')?true:false;
    var onUploadUrl = dom.data('on-upload-url')?dom.data('on-upload-url'):false;
    
    if(!endpoint) {
        alert('Falha ao iniciar o Uploader: Endpoint não configurado...');
        return;
    }
    
    /******************************************************************************************   / INICIALIZAÇÃO   */
    /***************************************************************************************   MANIPULAÇÃO DO DOM   */
    // Ajusta o ID do DOM para o qq.FineUploader
    dom.attr('id', dom.attr('id')?dom.attr('id'):('rtlUploader' + uploaderIndex));
    // Verifica se DOM é input e ajusta os elementos
    var id = dom.attr('id');
    if(dom.is('input')) {
        id = id + '_uploader_component';
        dom.before('<div id="' + id + '" class="rtlUploader"></div>');
        dom.hide();
        dom = $('#' + id);
        
        //    if(!$(this).prop('required')){
        
        // Se o input está em um form, associa esse form ao componente
        if(!form && dom.closest('form').length) {
            form = dom.closest('form');
        }
    }
    // Instancia o Uploader...
    var formIsSubmited = false;
    var uploader = new qq.FineUploader({
        debug: false,
        element: document.getElementById(id),
        autoUpload: typeof autoUpload === 'undefined'?true:autoUpload, 
        template: template,
        request: {
            endpoint: endpoint
        },
        deleteFile: {
            enabled: deleteEndpoint?true:false,
            forceConfirm: typeof deleteForceConfirm === 'undefined'?false:deleteForceConfirm, 
            endpoint: deleteEndpoint,
            confirmMessage: 'Você confirma a exclusão do arquivo?',
            deletingFailedText: 'Não foi possível excluir o arquivo',
            deletingStatusText: 'Excluindo...'
        },
        dragAndDrop: {
            extraDropzones: dropZones
        },
        paste: {
            defaultName: pasteName,
            targetElement: pasteArea
        },
        thumbnails: {
            placeholders: {
                waitingPath: thumbWaitingPath,
                notAvailablePath: thumbNotAvailablePath
            }
        },
        session: {
            endpoint: sessionEndpoint
        },
        messages: {
            noFilesError: 'Não há arquivos para enviar...',
            tooManyItemsError: 'Quantidade máxima de arquivos alcançada.'
        },
        callbacks: {
            onAllComplete: function(succeeded, failed) {
                if(form && formIsSubmited) {
                    if(failed.length > 0) {
                        formIsSubmited = false;
                        alert(failed.length + ' arquivo(s) com erro! Por favor, tente reenviá-los ou cancele-os');
                    } else {
                        formIsSubmited = false;
                        form.submit();
                    }
                }
                console.log(failed);
                if(onUploadRefresh && !failed.length)
                    location.reload();
                if(onUploadUrl && !failed.length)
                    window.location.replace(onUploadUrl);
            },
            onComplete: function(id, name, responseJson, xhr) {
                //Table...
                dom.find('tr.qq-upload-success').toggleClass('qq-upload-success success');
                dom.find('tr.qq-upload-fail').toggleClass('qq-upload-fail danger');
                // Lista
                dom.find('li.qq-upload-success').toggleClass('qq-upload-success list-group-item-success');
                dom.find('li.qq-upload-fail').toggleClass('qq-upload-fail list-group-item-danger');
            },
            onSubmit: function(id, name) {
                this.setName(id, name.replace(new RegExp('.' + qq.getExtension(name) + '$'), ''));
            },
            onSubmitted: function(id, name) {
                this.setName(id, name.replace(new RegExp('.' + qq.getExtension(name) + '$'), ''));
            }
        },
        validation: {
            itemLimit: limit
        }
    });
    // Ajustes nos templates...
    if(dropAreaText)
        dom.children(":first").children(":first").attr('qq-drop-area-text', dropAreaText);
    if(typeof autoUpload === 'undefined' || parseInt(autoUpload) === 1 || autoUpload === true)
        dom.find('.rtlUploaderUploadFiles').remove();
    if(limit && limit < 2)
        dom.find('.rtlClearArea').remove();
    /*************************************************************************************   / MANIPULAÇÃO DO DOM   */
    /************************************************************************************************   LISTENERS   */
    // Submit do form relacionado    
    if(form) {
        form.submit(function(event) {
            var uploads_successful = 0;
            var uploads_failed = 0;
            var uploads_submitted = 0;
            var uploads_uploading = 0;

            var uploads = uploader.getUploads();
            for(var i = 0; i < uploads.length; i++) {
                console.log(uploads[i].status);
                if(uploads[i].status == 'upload successful')
                    uploads_successful++;
                // || uploads[i].status == 'rejected' -->> Ignora os rejecteds...
                if(uploads[i].status == 'upload failed' || uploads[i].status == 'delete failed')
                    uploads_failed++;
                if(uploads[i].status == 'submitted')
                    uploads_submitted++;
                if(uploads[i].status == 'uploading' || uploads[i].status == 'retrying upload' || uploads[i].status == 'deleting' || uploads[i].status == 'submitting')
                    uploads_uploading++;
            }

            if(uploads_failed) {
                event.preventDefault();
                alert('Há arquivos com erro! Por favor, tente reenviá-los ou cancele-os');
                return;
            }
            if(uploads_uploading) {
                event.preventDefault();
                alert('Uploads em progresso... Aguarde a conclusão das transferências');
                return;
            }
            if(uploads_submitted) {
                event.preventDefault();
                formIsSubmited = true;
                uploader.uploadStoredFiles();
                return;
            }
            // Modificado para baixo...
            if(limitMin) {
                if(uploads_successful < limitMin) {
                    event.preventDefault();
                    alert('Selecione pelo menos ' + limitMin + ' arquivo(s)');
                    return;
                }
            }
            // Submit...
            
            /*
            if(!uploads_successful) {
                event.preventDefault();
                alert('Nenhum arquivo para salvar. Por favor, envie algum arquivo');
                return;
            }
            */
            
        });
    }
    // Botão Enviar Arquivos
    dom.on('click', '.rtlUploaderUploadFiles', function() {
        uploader.uploadStoredFiles();
    });
    // Botão Enviar Arquivos
    dom.on('click', '.rtlUploaderCancelAll', function() {
        cancelAll();
    });
    // Botão Enviar Arquivos
    dom.on('click', '.rtlUploaderDeleteAll', function() {
        deleteAll();
    });
    // Botão Enviar Arquivos
    dom.on('click', '.rtlUploaderClearAll', function() {
        clearAll();
    });
    /**********************************************************************************************   / LISTENERS   */
    /**************************************************************************************************   FUNÇÕES   */
    function cancelAll() {
        /* Se tiver alguma transferencia em progresso, pede pra esperar */
        var uploads = uploader.getUploads();
        for(var i = 0; i < uploads.length; i++) {
            if(uploads[i].status == 'uploading' || uploads[i].status == 'retrying upload' || uploads[i].status == 'deleting' || uploads[i].status == 'submitting') {
                alert('Uploads em progresso... Aguarde a conclusão das transferências');
                return;
            }
        }
        uploader.cancelAll();
    }
    function deleteAll() {
        var uploads = uploader.getUploads();
        for(var i = 0; i < uploads.length; i++) {
            if(uploads[i].status == 'upload successful')
                uploader.deleteFile(uploads[i].id);
        }
    }
    function clearAll() {
        cancelAll();
        deleteAll();
        var uploads = uploader.getUploads();
        for(var i = 0; i < uploads.length; i++) {
            if(uploads[i].status == 'upload failed')
                uploader.cancel(uploads[i].id);
        }
    }

    // onAllComplete...
    //    onAllCompleteRefresh...
    //    onAllCompleteUrl...
    
    /************************************************************************************************   / FUNÇÕES   */
}
/* / rtlUploader */




/* rtlCopyText */
function rtlCopyText (dom) {
    /* Função para criar um elemento clicável para copiar textos para Àrea de Transferência e manipular os ícones indicativos
     * Qualquer TAG pode ter essa funcionalidade
     * Por padrão os ícones ficam em qualquer elemento dentro de DOM com a class "icon". Se não houver um elemento "icon" o icones são colocados no fim ou no inícion do DOM. É possível difinir um outro local em DOM para inserção dos ícones através dos atributos data-iconId, data-iconClass e data-data_tag, descritos abaixo;
     * DOM: uma TAG que vai servir para ser clicada e ativar o componente
     *    USO 1: Básico...
     * <span class="rtlCopyText" data-text="Texto A Ser Copiado">Nome do Campo: <b>Valor do Campo</b></span>
     *    USO 2: Sem data-text: Copia o .text() do DOM
     * <a class="btn btn-default rtlCopyText" data-icon_left='1'>Copia o valor do A</a>
     *    USO 3: Manipulando icones
     * <span class="rtlCopyText" data-text="{{ 'Texto A ser' }}" data-icon_left="1" data-icon_class="iconplace">Campo: <b class="iconplace">Valor do Campo</b></span>
     * <span class="rtlCopyText" data-text="{{ 'Texto A ser' }}" data-icon_tag="div">Campo: <strong>Informação</strong> <div></div> <b>Valor do Campo</b></span>
     *    USO 4: INPUT text
     * <input class="rtlCopyText" name="texto" value="Valor a Ser Copiado"/>
     * Explicação sobre os atributos do elemento:
     *    title: OPCIONAL. Não interfere na funcionalidade do componente
     *    class: rtlCopyText (OBRIGATÓRIO), para ativar o componente no DOM desejado
     *    data-text: STRING: O valor a ser copiado para área de transferência; NAO INFORMADO: O valor copiado será dom.val() ou dom.text();
     *    data-icon: INT 0: Não manipular os ícones no componente; NÃO INFORMADO: Manipular os ícones do componente de acordo com as demais configurações
     *    data-icon_id: O ID de um elemento para servir de container para os ícones. STRING: ID do elemento que receberá os ícones; '' ou NÃO INFORMADO: Usar outro container
     *    data-icon_class: A CLASS de um elemento para servir de container para os ícones. STRING ("icon" DEFAULT): CLASS do elemento que receberá os ícones; '' ou NÃO INFORMADO: Usar outro container
     *    data-icon_tag: A TAG de um elemento para servir de container para os ícones. STRING: Tag do elemento que receberá os ícones; '' ou NÃO INFORMADO: Usar outro container
     *    data-icon_left: Por padrão os ícones são adicionados a direita no container (append). 1: Adicionar ícones a esquerda (prepend); 0 ou NÃO INFORMADO: Usar o valor padrão
     *    data-icon_html: o ícone padrão é um FontAwsome da class fa-copy. STRING (Elemento HTML): Para usar um outro ícone de Copia (pode usar texto também entre <span>); NÃO INFORMADO: Usar o ícone padrão
     *    data-icon_html_copied: Quando o DOM for clicado, o componente dá uma resposta visual trocando o ícone do copy por um check. O ícone padrão é um FontAwsome da class fa-check. STRING (Elemento HTML): Para usar um outro ícone para informar que foi copiado com sucesso (pode usar texto também entre <span>); NÃO INFORMADO: Usar o ícone padrão
     *    data-cursor_default: O elemento recebe o cursor de link por padrão. 1: Para evitar esse comportamento e manter o cursor padrão; NÃO INFORMADO: Usar o cursor do tipo POINTER;
     *    
     */
    /*******************************************************************************   Inicialização dos Atributos   */
    var text = dom.data('text')?dom.data('text'):(dom.val()?dom.val():dom.text());
    var icon = parseInt(dom.data('icon')) === 0?false:true;
    var iconId = dom.data('icon_id')?dom.data('icon_id'):false;
    var iconClass = dom.data('icon_class')?dom.data('icon_class'):false;
    var iconTag = dom.data('icon_tag')?dom.data('icon_tag'):false;
    var iconLeft = parseInt(dom.data('icon_left')) === 1?true:false;
    var iconHtml = dom.data('icon_html')?dom.data('icon_html'):'<i class="fa fa-copy"></i>';
    var iconHtmlCopied = dom.data('icon_html_copied')?dom.data('icon_html_copied'):'<i class="fa fa-check"></i>';
    var cursorDefault = parseInt(dom.data('cursor_default')) === 1?true:false;
    
    iconHtml = $(iconHtml);
    iconHtmlCopied = $(iconHtmlCopied);
    /*****************************************************************************   / Inicialização dos Atributos   */
    /*********************************************************************************   Criação do HTML estrutual   */
    var iconContainer = false;
    if(!cursorDefault)
        dom.css('cursor', 'pointer');
    if(icon) {
        if(iconId && dom.find('#' + iconId).length) {
            iconContainer = dom.find('#' + iconId);
        } else if(iconClass && dom.find('.' + iconClass).length) {
            iconContainer = dom.find('.' + iconClass);
        } else if(iconTag && dom.find(iconTag).length) {
            iconContainer = dom.find(iconTag);
        } else if(dom.find('.icon').length) {
            iconContainer = dom.find('.icon');
        } else {
            iconContainer = dom;
        }
        if(iconLeft) {
            iconContainer.prepend(iconHtml);
            iconContainer.prepend(iconHtmlCopied);
            $('<span>&nbsp;</span>').insertAfter(iconHtml);
            $('<span>&nbsp;</span>').insertAfter(iconHtmlCopied);
            iconHtmlCopied.hide();
        } else {
            iconContainer.append(iconHtml);
            iconContainer.append(iconHtmlCopied);
            iconHtmlCopied.hide();
            $('<span>&nbsp;</span>').insertBefore(iconHtml);
            $('<span>&nbsp;</span>').insertBefore(iconHtmlCopied);
        }
    }
    /*******************************************************************************   / Criação do HTML estrutual   */
    /*************************************************************************************************   Eventos   ***/
    dom.on('click', function() {
        copyTextToClipboard();
    });
    /***********************************************************************************************   / Eventos   ***/
    /*************************************************************************************************   Métodos   ***/
    /***************************************************************************************   COPY TO CLIPBOARD   ***/
    function copyTextToClipboard () {
        // Copia texto para área de transferência...
        var tempInput = $("<input>");
        $('body').append(tempInput);
        tempInput.val(text).select();
        document.execCommand("copy");
        tempInput.remove();
        // Ajusta o Ícone
        if(icon && iconHtml.is(':visible')) {
            iconHtml.fadeOut(100, function () {
                iconHtmlCopied.fadeIn(200);
            });
            setTimeout(function () {
                iconHtmlCopied.fadeOut(300, function () {
                    iconHtml.fadeIn(300);
                });
            }, 5000);
        }
    }
    /*************************************************************************************   / COPY TO CLIPBOARD   ***/
    /***********************************************************************************************   / Métodos   ***/
}
/* / rtlCopyText */

function copyTextClipboard(text, prependCheck) { // added 28/05/2019... documentação pendente...
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
    
    if(prependCheck) {
        if(prependCheck.find('.fa-copy').length) {
            var icon = prependCheck.find('.fa-copy');
            icon.fadeOut(100, function () {
                icon.toggleClass("fa-copy fa-check").fadeIn(200);
            });
            setTimeout(function () {
                icon.fadeOut(300, function () {
                    icon.toggleClass("fa-copy fa-check").fadeIn(300);
                });
            }, 5000);
        } else if(!prependCheck.find('.fa-check').length) {
            var check = $('<span class="text-success" style="display: none; margin-right: 3px;"><i class="fa fa-check"></i></span> ');
            prependCheck.prepend(check);
            check.show(250); //*********************************************************************
            setTimeout(function () {
                check.hide(250, function() { //****************************************************
                    check.remove();
                });
            }, 5000);//*****************************************************************************
        }
    }
            
}

function maskAsDate (dom) {
    /* Função para refinar o uso de INPUTs com data:
     *    Máscara de data: Adiciona ao INPUT (dom) uma máscara de data;
     *    Calendário: Adiciona ao INPUT (dom) um calendário para facilitar a seleção das datas;
     * Funcionamento:
     *    Ao focar no INPUT (dom) o calendário aparecerá. É possível navegar entre dias, meses, anos e ao selecionar uma data o valor do INPUT (dom) será preenchido;
     * Dependencias:
     *    Jquery;
     *    JQuery Maskedinput
     *    Jquery Datepicker (.js, .css e o .js do Idioma)
     *       https://github.com/fengyuanchen/datepicker
     * Parametros:
     *    DOM: o INPUT que receberá o componente, com a class "maskAsDate";
     * Funcionamento:
     *    <input name="data" id="data" value="" class="maskAsDate" type="text" maxlength="" data-next="id" data-date="01/02/2018" data-today="1" data-...="">
     *    Sobre os atributos:
     *       type: Tem que ser INPUT;
     *       id, name, value, maxlength: Não interferem nas funcionalidades do componente em si.
     *       class: "maskAsDate", para ativar o componente no DOM desejado
     *       autofocus: Foca o INPUT e mostra o calendário no onLoad
     *          Se houver "autofocus" o valor de "data-auto_show" SEMPRE será TRUE;
     *       data-next: id do campo NO MESMO FORM que receberá o foco após a data ser selecionada no calenário. 'String com a ID do Próximo campo': Para focar no próximo campo após seleção, '' ou null: Nenhum campo será focado após seleção da data;
     *       data-start_date: Data Inicial disponível para seleção no calendário. As data anteriores não estarão disponíveis para seleção.
     *          Valores: String no Formado de Data (Ex.: 23/01/2018): Data para o Inicio do Intervalo, NULL (default): Sem limitação de Data;
     *       data-end_date: Data Final disponível para seleção no calendário. As data posteriores não estarão disponíveis para seleção.
     *          Valores: String no Formado de Data (Ex.: 23/01/2018): Data para o Fim do Intervalo, NULL (default): Sem limitação de Data;
     *       data-date: Data inicial selecionada no calendário. String no Formado de Data (Ex.: 23/01/2018), NULL (default);
     *          Caso "data-date" não seja setada, o calendário terá sua data inicial selecionada pelo valor do INPUT ou data corrente;
     *       data-today: Forçar a data inicial do calendário para Hoje, indenpendente do valor do INPUT. 1: Forçar, 0 (Default): Não força;
     *       data-auto_show: Inicia com o calendário aberto (Diferente de autofocus). 1: Inicia aberto, 0 (default): Não inicia;
     *          Se houver "autofocus" o valor de "data-auto_show" SEMPRE será TRUE;
     *       data-auto_hide: Esconde o calendário quando uma data for selecionada. 1 (default): Esconder, 0: Não esconde o calendário ao selecionar uma data;
     *       data-format: O formato de data recebido no INPUT ao selecionar a data. "dd/mm/yyyy" (default);
     *       data-start_view: Visualização inicial do calendário. 0 (default): Começa mostrandos os dias; 1: Começa mostrando os meses; 2: Começa mostrando os anos;
     *       data-year_first: Mostrar o ano antes do mês. 1: Mostra o ano antes do mês, 0 (default): Mostra o Mês antes do Ano;
     *       data-offset: Espaçamento em Pixels entre o INPUT e o Calendário. Número Inteiro (O valor default é 10);
     *       data-z_index: O zIndex do css do calendário. Número Inteiro (O valor default é 9999);
     *       data-ignore_calendar: NÃO ativa o calendário no INPUT. 1: Não ativa, 0 (default): Ativa o calendário;
     *       data-ignore_mask: NÃO ativa a Máscara de data no INPUT. 1: Não ativa, 0 (default): Ativa a máscara;
     *       data-language: O idioma para o calendário. Valor padrão: "pt-BR";
     *          Para outro idioma além do "pt-BR" e do "en-GB" será necessário carregar um .js adicional com os dados do idioma
     *       
     * USO NO ZEND FORM
        $this->addElement('text', 'data');
        $this->getElement('data')
                ->setLabel('Data')
                ->setRequired()
                ->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->setAttribs(array('maxlength' => '255', 'class' => 'form-control maskAsDate', 'autofocus' => 'autofocus', 'data-next' => 'id_do_proximo', 'data-start_date' => '99/99/9999', 'data-end_date' => '99/99/9999'));
     *
     */
    // Opções Locais
    var today = parseInt(dom.data('today')) === 1?true:false; // Valor inicial do calendário ser a data atual independente do valor do INPUT
    var ignoreCalendar = parseInt(dom.data('ignore_calendar')) === 1?true:false; // Não ativar o calendário no INPUT;
    var ignoreMask = parseInt(dom.data('ignore_mask')) === 1?true:false; // Não ativar a Máscara de data no INPUT;
    var focusNext = dom.data('next') === undefined || dom.data('next') == ''?null:dom.data('next');
    // Opções do Plugin
    var autoShow = parseInt(dom.data('auto_show')) === 1?true:false;
    var autoHide = parseInt(dom.data('auto_hide')) === 0?false:true;
    var format = dom.data('format') === undefined || dom.data('format') == ''?'dd/mm/yyyy':dom.data('format');
    var date = dom.data('date') === undefined || dom.data('date') == ''?null:(dataValida(dom.data('date'))?dom.data('date'):null);
    var startDate = dom.data('start_date') === undefined || dom.data('start_date') == ''?null:(dataValida(dom.data('start_date'))?dom.data('start_date'):null);
    var endDate = dom.data('end_date') === undefined || dom.data('end_date') == ''?null:(dataValida(dom.data('end_date'))?dom.data('end_date'):null);
    var startView = dom.data('start_view') === undefined || dom.data('start_view') == ''?0:parseInt(dom.data('start_view'));
    var yearFirst = parseInt(dom.data('year_first')) === 1?true:false;
    var offset = dom.data('offset') === undefined || dom.data('offset') == ''?10:parseInt(dom.data('offset'));
    var zIndex = dom.data('z_index') === undefined || dom.data('z_index') == ''?9999:parseInt(dom.data('z_index'));
    var language = dom.data('language') === undefined || dom.data('language') == ''?'pt-BR':dom.data('language');
    // Não aplicados: trigger, inline, container e autoPick;
    // Focar no próximo campo após seleção da data
    if(focusNext) {
        dom.on('pick.datepicker', function (e) {
            dom.closest('form').find('#' + focusNext).focus();
        });
    }
    // Testa se deve forçar a data corrente do calendário...
    if(!date && today) {
        var data = new Date();
        var mes = data.getMonth() + 1;
        date = (data.getDate().toString().length === 1?'0' + data.getDate():data.getDate()) + '/' +
                (mes.toString().length === 1?'0' + mes:mes) + '/' +
                data.getFullYear();
    }
    // Verifica o autofocus...
    if(dom.prop('autofocus') && dom.is(':visible')) {
       autoShow = true;
    }
    if(!ignoreMask) {
        dom.mask("99/99/9999",{placeholder:" "});
    }
    if(!ignoreCalendar) {
        dom.datepicker({
            autoShow: autoShow, // Iniciar com o calendário aberto: True ou False;
            autoHide: autoHide, // Esconder calendário ao selecionar uma data: True ou False;
            format: format, // Formato de exibição das datas;
            date: date, // Data inicial selecionada no calendário;
            startDate: startDate, // Intervalo INICIAL de datas disponíveis para seleção: DATA ou NULO;
            endDate: endDate, // Intervalo FINAL de datas disponíveis para seleção: DATA ou NULO;
            startView: startView, // Visualização inicial do calendário: 0: Começa mostrandos os dias; 1: Começa mostrando os meses; 2: Começa mostrando os anos;
            yearFirst: yearFirst, // Mostrar o Ano antes do Mês: True ou False;
            offset: offset, // Espaçamento em PXs entre o calendário e o INPUT;
            zIndex: zIndex, // O estilo "z-index" do calendário: INT (Default 1);
            language: language // O idioma...
            //trigger: '#tr', // Se String (ID de um Elemento): Usa o elemento para quando clicar mostrar o calendário; Se NULL: Não usa elementos externos para mostrar o calendário, mostra quando o input for clicado ou no "ONFOCUS";
            //inline: true, // Calendário exibido no corpo do HTML. Melhor explicado nas documentações do plugin;
            //container: null, // Container para colocar o calendário, casao "inline" for "true". Melhor explicado nas documentações do plugin;
            //autoPick: false, // Pega automaticamente a data selecionada no calendário e seta no INPUT; 
        });
    }
    // Função para validar datas
    function dataValida(data) {
        if(!data)
            return false;
        //Declare Regex 
        var rxDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
        var dtArray = data.match(rxDatePattern); // is format OK?
        if (dtArray == null)
            return false;
        //Checks for mm/dd/yyyy format.
        var dtDay = dtArray[1];
        var dtMonth = dtArray[3];
        var dtYear = dtArray[5];
        if (dtMonth < 1 || dtMonth > 12)
            return false;
        else if (dtDay < 1 || dtDay> 31)
            return false;
        else if ((dtMonth==4 || dtMonth==6 || dtMonth==9 || dtMonth==11) && dtDay ==31)
            return false;
        else if (dtMonth == 2) {
            var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
            if (dtDay> 29 || (dtDay ==29 && !isleap))
                return false;
        } else if (dtYear < 1)
            return false;
        return true;
    }
}

/* rtlMultiSelect */
function rtlMultiSelect (dom) {
    /* Componente para manipular Inputs(Text) e Selects com ou sem "multiple".
     * 1. Input: Faz o recuro AUTOCOMPLETAR para ajudar a escrita de valores. Sugestões alimentadas via Dados Locais ou URL (Ajax-POST). Ao selecionar uma das opções o atributo data-value é criada no dom, para coletar o value do LI e o evento CHANGE do dom é disparado
     * 2. Select SEM MULTIPLE: Vira um Select normal com opção de filtragem dos options. Dados locais através dos OPTIONS ou via URL (Ajax-POST)
     * 3. Select COM MULTIPLE: Um painel de multiplas seleções de resultados locais filtráveis ou vindos de uma URL (Ajas-POST);
     * 4. Select MultiLevel: Para seleção de Entidades auto associadas formando um Hierarquia, como a seleção de categorias;
     * 5. Quando Select, possibilidade de adição de novos itens não mostrados na listagem. Adiciona um OPTION com o val = "InsertInto:[valor digitado]"
     * 6. Atalhos: Tecla UP: Seleciona sugestão anterior; Tecla DOWN: seleciona proxima sugestão ou mostra lista; Tecla ESC: Esconde lista; Tecla ENTER: Seleciona sugestão marcada, submita o form ou adiciona novo quando puder; Tecla Shift + "+": Adiciona NOVO valor à lista (Quando data-insert habilitado - Mesma função do Enter); Tecla Tab: Fecha a lista e muda o foco para o próximo campo;
     * 7. DEPENDE DE CSS EXTERNO
     * DOM: um INPUT, SELECT SEM MULTIPLE ou um SELECT COM MULTIPLE, com a class "rtlMultiSelect".
     *    USO 1: Um Input no formato abaixo
     * <input type="text(Obrigatorio) class="rtlMultiSelect" name="cliente(Não Interfere)" id="cliente(Não Interfere)" maxlength="255(Não Interfere)" autofocus="autofocus(Opcional)" data-local_data='["Valor 1", "Valor 2", "Valor 3"](Opcional)' data-insert="(Não Interfere)" autocomplete="off(Não Interfere)" placeholder="(Opcional)" data-filtros_externos='["input1","input2"](Opcional)' data-url="[Valor](Opcional)">
     *    USO 2: Um Select no formato abaixo
     * <select name="nome_campo[](Não Interfere)" multiple="multiple"(opcional) class="rtlMultiSelect" id="id_campo(Não Interfere)" data-insert="[0 ou 1(Opcional)]" data-filtros_externos='["input1","input2"](Opcional)' placeholder="[Valor](Opcional)" autofocus="autofocus(Opcional)" data-maxselecionados="[Valor](Opcional)" data-url="[Valor](Opcional)" data-width="400px">
     *    <option value="[Valor]" label="[Valor]" selected="selected">[Valor]</option>
     *    ... os OPTIONS são os valores iniciais para a listagem
     * </select>
     *    USO 3: Um select para uso de MultiNíveis
     * <select id="categorias_categoria" name="categorias[categoria]" class="rtlMultiSelect form-control" data-url="/api/categoriasDinamico" data-nivel="categoria" data-url_lista_onload="1" data-filtros_externos="categorias_filtro_externo" data-parents='[{"value": "1", "label": "Produtos"}, {"value": "12", "label": "Armações"}]' autofocus="autofocus">
     *    <option value="8">Nylon</option>
     *    <option value="17">Aparafusadas</option>
     *    <option value="13" selected="selected">Receituário</option>
     * </select>
     * Explicação sobre os atributos:
     *    type: Se INPUT, é o brigatório que o "type" seja "text"
     *    id, maxlength, autocomplete: Não interferem nas funcionalidades do componente em si.
     *    name: valor do campo para o post. Adicionar o [] para poder retornar uma Array (Multiple)
     *    class: rtlMultiSelect, para ativar o componente no DOM desejado
     *    multiple="multiple": Para funcionar o painel de seleções. Envia um Array no SUBMIT do FORM ou Nada
     *    data-local_data: Se INPUT e não tiver URL, passa os dados da lista via atributo no formato: data-local_data='["Valor 1", "Valor 2", "Valor 3"]' : Não funciona com o MultiNível
     *    data-nivel: STRING: Para usar o componente como uma seleção de multi nível (como em categorias). A String passada será usada como post no AJAX. VAZIO ou NÃO informar: Não usar o recurso multi nível
     *    data-url: Não referenciar: Usa apenas os dados nos OPTIONS do Select ou no "data-local_data" do INPUT; Adicionar uma URL: Usa os dados retornados do PHP na listagem
     *       O PHP deve retornar um json no formato... $json = array('ok' => 1, 'message'='', 'data' => array(array('valor' => 1, 'label' => 'Label 1'), array('valor' => 2, 'label' => 'Label 2'), array('valor' => 3, 'label' => 'Label 3')...));
     *          ok: 1: Sucesso; 2: Erro no servidor
     *          message: se "ok" for 2, erro a ser mostrado para o usuário
     *          data: Os valores para a listagem, no formato abaixo:
     *             array(array('valor' => 1, 'label' => 'Label 1'), array('valor' => 2, 'label' => 'Label 2'))...
     *       Os valores postados via POST são "input", com o texto no campo do componente e os valores dos campos nos "filtros_externos", referenciados pelo ID do campo "filtro externo"
     *       R E C O M E N D A D O: Colocar um "LIMIT" na Query do Banco de Dados com o retorno, para não retornar uma grande quantidade de valores para o elemento
     *    data-url_lista_onload: Valores possíveis: "1". Se tem URL, carrega lista na inicilização do elemento; "0" ou não mencionar no select: Não carrega a lista na inicialização;
     *    data-insert: valores possíveis: "1". habilita a possibilidade de adicionar um novo item aos Selecionados; "0" ou não mencionar no select: Não habilita novas adições;
     *       É criado um OPTION com o value no padrão "InsertInto:[valor digitado]". No PHP pega essa informação e processa
     *       QUANDO INPUT, ATRIBUTO É IGNORADO
     *    data-filtros_externos: São os IDs de outros campos DENTRO DO MESMO Form que servirão como filtros além do Termo da Pesquisa;
     *       Valores Possíveis: Uma string, se for apenas um campo, ou um Array se forem mais, no formato demonstrado no modelo acima
     *    data-maxselecionados: Limita o número de itens que poderão ser selecionados. Os valores possíveis são um número inteiro ou não referenciar o atributo para não limitar. ATRIBUTO IGNORADO QUANDO FOR "Input" OU Select sem "multiple" OU MultiNível
     *    data-nivel_last: 1: Permite seleção apenas quando o úlmimo nível for alcançado; 0 ou não informar: Permite a seleção do valor no em qualquer nível;
     *    data-parents: Relação de Selects pai para inicializar o componente. STRING JSON: Inicializa o componente com Selects pai. Formato: '[{"value":99,"label":"Item 99 Label"},{"value":99,"label":"Item 99 Label"}]' ; VAZIO: Não adiciona Selects pai na inicialização;
     *    data-width: A largura do componente todo. O Padrão é "100%".
     *    data-inline: 1: Força o componente a estruturar o Layout para ficar inline; 0 Força o componente a estruturar Horizontal; NÂO INFORMAR: O componente vai procurar se o form do componente tem a classe form-inline e se tiver estrutura o componente para inline;
     *    placeholder: Opcional: Texto customizado para o input de pesquisa ou novo item
     *    autofocus: Se houver essa propriedade, o campo de pesquisa é focado no OnLoad
     * Data do OPTION:
     *    value: Valor do OPTION. Será enviado no submit
     *    label: Texto para exibição
     *    selected="selected": Opcional, caso o OPTION esteja selecionado
     *    entre <option> e </option>: Texto para exibição. O mesmo do label
     * USO NO ZEND FORM
     * SELECT
          $this->addElement('multiselect', 'nome_do_campo');
          $this->getElement('nome_do_campo')
             ->setLabel('Nome do Campo')
                ->setMultiOptions(array(...))
                ->setAttribs(array('class' => 'rtlMultiSelect', 'data-habilitar_insert' => 0, 'data-filtros_externos' => '["input1","input2"]', 'data-maxselecionados' => '0', 'data-pesquisa_placeholder' => 'Custom text...', 'data-maxheight' => '200px', 'autofocus' => 'autofocus'));
     * INPUT
        $this->addElement('text', 'nome_do_campo');
        $this->getElement('nome_do_campo')
                ->setLabel('Nome Do Campo')
                ->setRequired()
                ->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->setAttribs(array('class' => 'form-control rtlMultiSelect', 'maxlength' => '255', 'autofocus' => 'autofocus', 'data-local_data' => '["Renan","Trinta","Layme","Renato"]'));
    
        Nem todos os atributos são obrigatórios, o Attrib data-url é posto no Controller:
        $form->nome_do_campo->setAttribs(array('data-url' => $this->view->url(array('controller' => 'ajax', 'action' => 'action'), null, true)));
     */
    // Testa se o elemento é do tipo correto (input:text ou select)
    var tipo = dom.is("input:text")?'input':(dom.is("select")?(dom.prop('multiple')?'multiple':'select'):false);
    tipo = dom.data('nivel')?'nivel':tipo;
    if(!tipo) {
        alert('Não foi possível inicialiar o RtlMultiSelect: o Elemento alvo não é um Input:Text ou Select (#'+dom.attr('id')+')');
        return false;
    }
    /*******************************************************************************   Inicialização dos Atributos   */
    var url = dom.data('url');
    var url_lista_onload = parseInt(dom.data('url_lista_onload')) === 1?true:false;
    var insert = parseInt(dom.data('insert')) === 1?true:false;
    var placeholder = dom.attr('placeholder')?dom.attr('placeholder'):'';
        if(dom.attr('placeholder')) {
            placeholder = dom.attr('placeholder');
        } else if(tipo == 'select' && !url) {
            placeholder = dom.find('option:first').text();
        } 
    var urlRequest;  // Variável glabal para controlar o .post()
    var local_data = dom.data('local_data');
    if(local_data) {
        if(local_data.constructor !== Array) {
            local_data = [local_data];
        }
    } else {
        local_data = [];
    }
    var filtros_externos = dom.data('filtros_externos');
    if(filtros_externos) {
        if(filtros_externos.constructor !== Array) {
            filtros_externos = [filtros_externos];
        }
    } else {
        filtros_externos = [];
    }
    var maxselecionados = (tipo == 'input' || tipo == 'select'?1:(dom.data('maxselecionados')?parseInt(dom.data('maxselecionados')):0));
    var nivel = (tipo == 'nivel'?dom.data('nivel'):null);
    var select_last  = parseInt(dom.data('nivel_last')) === 1?true:false;
    var parents = dom.data('parents');
    if(parents) {
        if(parents.constructor !== Array) {
            parents = [parents];
        }
    } else {
        parents = [];
    }
    var area_width = dom.data('width')?dom.data('width'):(tipo == 'nivel'?'200px':'100%');
    var inline = parseInt(dom.data('inline')) === 1?true:(parseInt(dom.data('inline')) === 0?false:(dom.closest('form').has('.form-inline').length?true:false));
    // o input-group deve ter: style="width: inherit"
    /*****************************************************************************   / Inicialização dos Atributos   */
    /*********************************************************************************   Criação do HTML estrutual   */
    dom.before('<div class="rtlMultiSelectArea"' + (area_width?' style="width: '+area_width+';"':'') + '>' + 
        '<div class="rtlMultiSelectSelecionadosArea">' +
            '<span class="rtlMultiSelectSelecionados">' +
                '<a class="rtlMultiSelectRemoverTodos btn btn-secondary btn-sm" title="Remover todos os selecionados" tabindex="-1"><i class="fa fa-fw fa-times"></i></a>' +
            '</span>' + 
            '<span class="input-group" style="">' + 
                '<input class="form-control" placeholder="' + placeholder + '" maxlength="255" autocomplete="off" type="text">' +
                '<span class="input-group-btn">' + 
                    '<a class="rtlMultiSelectSelecionarTodos btn btn-secondary" title="Selecionar todos" tabindex="-1"><i class="fa fa-fw fa-check-square-o"></i></a>' +
                    '<a class="rtlMultiSelectNovoItem btn btn-secondary" title="Adicionar novo item à lista" tabindex="-1"><i class="fa fa-fw fa-plus"></i></a>' +
                    '<a class="rtlMultiSelectAtulizar btn btn-secondary" href="javascript: " title="Mostrar/Esconder lista com os resultados" tabindex="-1"><i class="fa fa-fw fa-caret-down"></i></a>' +
                '</span>' +
            '</span>' +
        '</div>' +
        '<ul class="rtlMultiSelectLista list-unstyled" tabindex="-1"></ul>' +
    '</div>');
    var area = dom.prev();
    var lista = area.find('.rtlMultiSelectLista');
    lista.hide();
    if(tipo == 'input') {
        area.find('.rtlMultiSelectSelecionados').remove();
        area.find('.rtlMultiSelectSelecionarTodos').remove();
        area.find('.rtlMultiSelectNovoItem').remove();
        area.find('input.form-control').replaceWith(dom);
        dom.attr('autocomplete', 'off');
        dom.attr('placeholder', placeholder);
    } else if(tipo == 'select' || tipo == 'nivel') {
        area.find('.rtlMultiSelectSelecionados').remove();
        area.find('.rtlMultiSelectSelecionarTodos').remove();
        area.find('input').css('color', '#cc0000'); // red: cc0000
        if(tipo == 'nivel')
            area.find('.rtlMultiSelectNovoItem').remove();
    } else if(tipo == 'multiple') {
        area.find('.rtlMultiSelectRemoverTodos').hide();
        var selecionados = area.find('.rtlMultiSelectSelecionados');
    }
    if(tipo == 'select' || tipo == 'multiple' || tipo == 'nivel')
        dom.hide();
    if(tipo == 'select' || tipo == 'multiple') {
        if(!insert)
            area.find('.rtlMultiSelectNovoItem').remove();
    }
    /*****************************************************************************   / Criação do HTML estrutual   ***/
    /****************************************************************************************   Valores Iniciais   ***/
    if(tipo == 'input') {
        for(i = 0; i < local_data.length; i++) {
            listaAdd(lista, '', $.trim(local_data[i]));
        }
        listaFiltrar(area);
    } else {
        dom.find('option').each(function( index ) {
            listaAdd(lista, $(this).val(), $.trim($(this).text())); // Add itens na Lista
            // Se o OPTION estiver SELECTED, mostra seleção no visual
            if($(this).attr("selected") === "selected") {
                if(tipo == 'select' || tipo == 'nivel') {
                    area.find('input').val($.trim($(this).text()));
                    area.find('input').css('color', '#555'); // red: cc0000, form-secondary: 555 
                } else if(tipo == 'multiple') {
                    selecionados.append('<a class="rtlMultiSelectRemoverItem btn btn-primary btn-sm" title="Remova a seleção deste item" data-value="' + $(this).val() + '" tabindex="-1">' + $.trim($(this).text()) + ' <i class="fa fa-fw fa-times"></i></a>');
                    lista.find('li:last').hide('');   // Esconde o LI mais recente da lista
                    removerTodosVisibilidade();
                }
            }
        });
    }
    /**************************************************************************************   / Valores Iniciais   ***/
    /******************************************************************************************   Cria os Níveis   ***/
    if(tipo == 'nivel') {
        // Adiciona a parte dos valores a Area
        area.prepend('<span class="rtlMultiSelectNivelValue text-muted" title="Clique para editar valor">' +
                    '<span class="rtlMultiSelectNivelValueSpan" data-value=""></span> <i class="fa fa-caret-right"></i>' + 
                '</span>');
        // Manipula o Select dependendo do valor do DOM: Se val(): escode as opções e mostra o valor; Se !val(): Esconde a área do valor e mostra as opções
        if(dom.val()) {
            area.find('.rtlMultiSelectNivelValue').find('.rtlMultiSelectNivelValueSpan').data('value', dom.val());
            area.find('.rtlMultiSelectNivelValue').find('.rtlMultiSelectNivelValueSpan').text(area.find('input').val());
            area.css('width', 'auto');
            area.find('.rtlMultiSelectNivelValue').toggleClass('text-primary text-muted');
            area.find('.rtlMultiSelectNivelValue').find('.fa').attr('class', 'fa fa-caret-down');
            area.find('.rtlMultiSelectSelecionadosArea').hide();
        } else {
            area.find('.rtlMultiSelectNivelValue').hide();
        }
        // Cria a área de Nível, inserindo o primeiro select
        dom.before('<div class="rtlMultiSelectAreaNivel ' + (inline?'form-inline':'form-control') + '" style="height: auto;"></div>');
        var area2 = dom.prev();
        area2.append(area);
        area = area2;
        // Se tem valor selecionado, adiciona os Selects PAI
        if(!dom.val())
            parents = [];
        for(var i = 0; i < parents.length; i++) {
            if(area.find('.rtlMultiSelectArea').length > 1) {
                area.find('.rtlMultiSelectArea').eq(i-1).clone(true).insertAfter(area.find('.rtlMultiSelectArea').eq(i-1));
            } else {
                area.prepend(area.find('.rtlMultiSelectArea').first().clone(true));
                area.find('.rtlMultiSelectArea').first().find('.rtlMultiSelectNivelValue').toggleClass('text-primary text-muted');
                area.find('.rtlMultiSelectArea').first().find('.rtlMultiSelectNivelValue').find('.fa').attr('class', 'fa fa-caret-right');
            }
            area.find('.rtlMultiSelectArea').eq(i).find('.rtlMultiSelectNivelValue').find('.rtlMultiSelectNivelValueSpan').data('value', parents[i].value); 
            area.find('.rtlMultiSelectArea').eq(i).find('.rtlMultiSelectNivelValue').find('.rtlMultiSelectNivelValueSpan').text(parents[i].label);
            area.find('.rtlMultiSelectArea').eq(i).find('input').val(parents[i].label);
            area.find('.rtlMultiSelectArea').eq(i).find('.rtlMultiSelectLista').find('li').remove();
            listaAdd(area.find('.rtlMultiSelectArea').eq(i).find('.rtlMultiSelectLista'), parents[i].value, parents[i].label);
        }
    }
    /****************************************************************************************   / Cria os Níveis   ***/
    /******************************************************************************************   Outros Recuros   ***/
    // Verifica o autofocus
    if(dom.prop('autofocus')) {
        area.find('input').last().focus();
    }
    // Se tem URL e é pra carregar lista na inicialização, então carrega dados iniciais na lista...
    if(url && url_lista_onload) {
        if(tipo == 'nivel')
            listaFiltrarUrl(area.find('.rtlMultiSelectArea').last(), true, true);
        else
            listaFiltrarUrl(area, true, true);
    }    
    /****************************************************************************************   / Outros Recuros   ***/
    /*************************************************************************************************   Eventos   ***/
    // Eventos ao digitar valores no Input
    area.on('input', 'input', function(e) {
        inputAtualizar($(this).closest('.rtlMultiSelectArea'), false, true);
    });
    // Detecção do teclado
    area.find('input').on('keydown', function(e) { //keydown
        var selectArea = $(this).closest('.rtlMultiSelectArea');
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        var keyCode = e.keyCode || e.which;
        var shifted = e.shiftKey;
        if (keyCode === 40) { //Down
            listaAlternarVisibilidade(selectArea, true);
            listaFocarProximo(selectArea);
        } else if (keyCode === 38) { // UP            
            listaAlternarVisibilidade(selectArea, true);
            listaFocarAnterior(selectArea);
        } else if (keyCode === 13) { // Enter...
            if(selectLista.is(':visible') && selectLista.find('li.rtlMultiSelectItemFocado').length > 0) {
                e.preventDefault();
                selectLista.find('li.rtlMultiSelectItemFocado').click();
                return;
            }
            if(tipo == 'select' && dom.find('option:selected').length < 1) {
                e.preventDefault();
                if($.trim(selectArea.find('input').val()) && insert) {
                    adicionarItem();
                }
                return;
            }
            if(tipo == 'multiple' && $.trim(selectArea.find('input').val())) {
                e.preventDefault();
                if(insert) {
                    adicionarItem();
                }
                return;
            }
            if(tipo == 'nivel' && dom.find('option:selected').length < 1) {
                e.preventDefault();
                return;
            }
        } else if (shifted && keyCode === 43) { // Sinal de Mais...
            if((tipo == 'select' || tipo == 'multiple') && insert) {
                e.preventDefault();
                selectArea.find('.rtlMultiSelectNovoItem').click();
            } 
        } else if (keyCode === 27 || keyCode === 9) { // Esc ou Tab...
            listaAlternarVisibilidade(selectArea, false);
        } 
    });
    // Adiciona os Listeners Externos caso haja filtros externos e tenha URL...
    if(url && filtros_externos.length > 0) {
        var form = area.closest('form');
        var form_dom;
        for(var i = 0; i < filtros_externos.length; i++) {
            form_dom = form.find('#' + filtros_externos[i]);
            if(form_dom.is('input:text')) {
                form_dom.on('input', function(e) {
                    inputAtualizar(area, true, true, true);
                });
            } else {
                form_dom.on('change', function(e) {
                    inputAtualizar(area, true, true, true);
                });
            }
        }
    }
    area.on('click', '.rtlMultiSelectAtulizar', function() {
        var selectArea = $(this).closest('.rtlMultiSelectArea');
        listaAlternarVisibilidade(selectArea);
        selectArea.find('input').focus();
    });
    area.on('click', 'input[type="text"]', function() {
        listaAlternarVisibilidade($(this).closest('.rtlMultiSelectArea'));
    });
    area.on('click', 'li:not(.rtlMultiSelectItemNaoClicavel)', function() {
        selecionarItem($(this));
    });
    $('body').on('click', function(event) {
        var element = $(".rtlMultiSelectArea");
        if(element !== event.target && !element.has(event.target).length) {
            // Esconde outras Listas
            $('.rtlMultiSelectLista:visible').each(function(index, value) {
                listaAlternarVisibilidadeDom($(this), false);
            });
        }        
    });
    area.on('click', '.rtlMultiSelectNovoItem', function() {
        adicionarItem();
    });
    if(tipo == 'multiple') {
        selecionados.on('click', '.rtlMultiSelectRemoverItem', function() {
            var item_selecionado = $(this);
            // Remove a selecção do OPTION do Multiselect e dispara o OnChange do MultiSelect
            dom.find('option').each(function( index ) {
                if($(this).val() == item_selecionado.data('value')) {
                    $(this).prop("selected", false);
                    dom.change();
                }
            });
            // Se o item sai dos Selecionados, reaparece na Lista
            lista.find('li').each(function( index ) {
                if($(this).data('value') == item_selecionado.data('value')) {
                    $(this).show();
                }
            });
            // Tira o item dos Selecionados e testa a Visibilidade do "Remover Todos"
            item_selecionado.remove();
            removerTodosVisibilidade();
            area.find('input').focus();
        });
        
        area.on('click', '.rtlMultiSelectSelecionarTodos', function() {
            // Percorre todos os Itens visíveis da Lista para adiconar nos Selecionados
            lista.find('li:visible').each(function( index ) {
                $(this).click();
            });
            listaAlternarVisibilidade(area, false);
        });
    
        selecionados.on('click', '.rtlMultiSelectRemoverTodos', function() {
            // Percorre todos os Itens Selecionados para adiconar na Lista
            selecionados.find('.rtlMultiSelectRemoverItem').each(function( index ) {
                $(this).click();
            });
        });
    }
    if(tipo == 'nivel') {
        area.on('click', '.rtlMultiSelectNivelValue', function() {
            nivelEditar($(this).closest('.rtlMultiSelectArea'));
        });
    }
    /***********************************************************************************************   / Eventos   ***/
    /*************************************************************************************************   Métodos   ***/
    /*****************************************************************************************   SELECIONAR ITEM   ***/
    function selecionarItem (li) {
        var selectArea = li.closest('.rtlMultiSelectArea');
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        // Selecionar um Item da Lista
        if(tipo == 'input') {
            selectArea.find('input').val(li.text());
            selectArea.find('input').data('value', li.data('value'));
            selectArea.find('input').trigger("change");
            listaAlternarVisibilidade(selectArea, false);
        } else if(tipo == 'select') {
            // Se URL remove todos os Options!
            if(url)
                dom.find('option').remove();
            var optionExiste = false;
            dom.find('option').each(function( index ) {
                if(li.data('value') == $(this).val()) {
                    $(this).prop("selected", true);
                    optionExiste = true;
                }
            });
            // Se não existe, adiciona com select
            if(!optionExiste)
                dom.append('<option value="' + li.data('value') + '" selected="selected">' + li.text() + '</option>');
            dom.change();
            listaAlternarVisibilidade(selectArea, false);
            selectArea.find('input').val(''); // Reseta pra no filtro mostrar tudo
            listaFiltrar(selectArea); // Indenpendente de ULR, filtra as LIs
            selectArea.find('input').val(li.text()).focus();
            selectArea.find('input').css('color', '#555'); // red: cc0000, form-default: 555 
        } else if(tipo == 'nivel') {
            nivelSelecionarItem(selectArea, li);
        } else if(tipo == 'multiple') {
            // Se não tiver valor ou label, não seleciona o item
            if(!li.data('value') || !li.text()) {
                alert('Não é possível adicionar esse item');
                return;
            }
            // Testa se o valor a ser adicionado não já foi selecionado antes
            var valor_inedito = true;
            selecionados.find('.rtlMultiSelectRemoverItem').each(function( index ) {
                if($(this).data('value') == li.data('value')) {
                    valor_inedito = false;
                }
            });
            if(!valor_inedito) {
                alert(li.text() + ' já está selecionado');
                return;
            }
            // Número Máximo: Se 0, adiciona sem restrição; Se 1: Substitui; Se > 1: Testa se o número máximo de selecionados foi alcançado e retorna erro;
            /*if(maxselecionados && maxselecionados === 1) { //--------------------------------- AVALIAR DEPOIS NECESSIDADE
                selectArea.find('.rtlMultiSelectRemoverTodos').click();
            } else*/ if(maxselecionados && selecionados.find('a').length > maxselecionados) {
                alert('Não foi possível adicionar item ' + li.text() + '. Número máximo de itens selecionados alcançado (' + maxselecionados + ')');
                return;
            }
            // Se ainda não foi selecionado, adiciona item nos Selecionados
            selecionados.append('<a class="rtlMultiSelectRemoverItem btn btn-primary btn-sm" title="Remova a seleção deste item" data-value="' + li.data('value') + '" tabindex="-1">' + li.text() + ' <i class="fa fa-fw fa-times"></i></a>');
            var optionExiste = false;
            // Seleciona OPTION no Multiselect e dispara o evento Change do MultiSelect
            dom.find('option').each(function( index ) {
                if(li.data('value') == $(this).val()) {
                    $(this).prop("selected", true);
                    optionExiste = true;
                }
            });
            // Se não existe, adiciona com select
            if(!optionExiste) {
                dom.append('<option value="' + li.data('value') + '" selected="selected">' + li.text() + '</option>');
            }
            dom.change();
            selectArea.find('input').val('').focus();  // Reseta e Focus no Input
            li.hide();   // Se selecionado, esconde o item na lista
            listaFocarProximo(selectArea);   // Foca o proximo item da lista
            removerTodosVisibilidade();
        } 
    }
    /***************************************************************************************   / SELECIONAR ITEM   ***/
    /*****************************************************************************************   Input Atualizar   ***/
    function inputAtualizar(selectArea, ignoraLista, ignorarValorVazio, resetInput) {
        // se for NIVEL, seleciona qual selectArea vai atuar
        if(selectArea.is('.rtlMultiSelectAreaNivel') ) {
            selectArea = selectArea.find('.rtlMultiSelectArea').first();
        }
        // Mostra a Lista
        if(!ignoraLista) {
            listaAlternarVisibilidade(selectArea, true);
        }
        // Sendo INPUT, reseta o valor de dom.data-value
        if(tipo == 'input' && dom.data('value')) {
            dom.data('value', '');
            dom.change();
        }
        // Sendo SELECT, atualizar o INPUT causa um reset do valor do dom(select)
        if((tipo == 'select' || tipo == 'nivel') && dom.val()) {
            selectArea.find('input').css('color', '#cc0000'); // red: cc0000, form-default: 555 
            dom.val('');
            if(resetInput === true)
                selectArea.find('input').val('');
            dom.change();
        }
        // Filtra atualizando os resultados
        if(url) {
            listaFiltrarUrl(selectArea, ignorarValorVazio);
        } else {
            listaFiltrar(selectArea);
        }
    }
    /***************************************************************************************   / Input Atualizar   ***/
    /****************************************************************************************   Mostrar Esconder   ***/
    function listaAlternarVisibilidade(selectArea, mostrar) {
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        // Esconde outras Listas
        $('.rtlMultiSelectLista:visible').not(selectLista).each(function(index, value) {
            listaAlternarVisibilidadeDom($(this), false);
        });
        // Mostra ou Esconde a Lista Atual
        listaAlternarVisibilidadeDom(selectLista, mostrar);
    }
    function listaAlternarVisibilidadeDom(lista_temp, mostrar) {
        // Mostra ou Esconde a lista
        var area_temp = lista_temp.closest('.rtlMultiSelectArea');
        var vel = 250;
        if(typeof mostrar === 'undefined') {
            if(lista_temp.is(':visible'))
                lista_temp.find('.rtlMultiSelectItemFocado').removeClass('rtlMultiSelectItemFocado');
            lista_temp.toggle(vel);
            area_temp.find('.rtlMultiSelectAtulizar i').toggleClass('fa-caret-down fa-caret-up');
        } else if(mostrar) {
            lista_temp.show(vel);
            area_temp.find('.rtlMultiSelectAtulizar i').addClass('fa-caret-up').removeClass('fa-caret-down');
        } else if(!mostrar) {
            lista_temp.hide(vel);
            area_temp.find('.rtlMultiSelectAtulizar i').addClass('fa-caret-down').removeClass('fa-caret-up');
            lista_temp.find('.rtlMultiSelectItemFocado').removeClass('rtlMultiSelectItemFocado');
        }
    }
    /**************************************************************************************   / Mostrar Esconder   ***/
    /*******************************************************************************************   Lista: Add LI   ***/
    function listaAdd (selectLista, value, label, naoClicavel) {
        // Adiciona item na Lista
        selectLista.append('<li class="rtlMultiSelectItem' + (naoClicavel?' rtlMultiSelectItemNaoClicavel':'') + '" title="Selecione este item" data-value="' + value + '">' + label + '</li>');
    }
    /*****************************************************************************************   / Lista: Add LI   ***/
     /*************************************************************************************   Filtrar Lista LOCAL   ***/
    function listaFiltrar (selectArea) {
        var val = selectArea.find('input').val();
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        if(selectLista.find('li').length < 1) return;
        selectLista.find('li').each(function( index ) {
            if (inputValSimples($(this).text().substr(0, val.length)) == inputValSimples(val)) {
                $(this).show();
            } else {
                $(this).hide();
                $(this).removeClass('rtlMultiSelectItemFocado');
            }
        });
    }
    // Retira acento e deixa em minúsculo
    function inputValSimples (str) {
        // Deixa em minúsculo e retira os acentos
        var ACENTO_REGEX = {'a': /[\xE0-\xE6]/g, 'e': /[\xE8-\xEB]/g, 'i': /[\xEC-\xEF]/g, 'o': /[\xF2-\xF6]/g, 'u': /[\xF9-\xFC]/g, 'c': /\xE7/g};
        str = str.toLowerCase();
        for(var re in ACENTO_REGEX) {
            str = str.replace(ACENTO_REGEX[re], re);
        }
        return str;
    }
    
    /***********************************************************************************   / Filtrar Lista LOCAL   ***/
    /***************************************************************************************   Filtrar Lista URL   ***/
    function listaFiltrarUrl (selectArea, ignorarValorVazio, manterOptions) {
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        var selectValue = selectArea.find('.rtlMultiSelectNivelValue');
        var val = $.trim(selectArea.find('input').val());
        manterOptions = (manterOptions === undefined?false:manterOptions);
        if(!ignorarValorVazio && !val) return;
        // Se já houver outros post em adamento, aborta!
        if (urlRequest) {
            urlRequest.abort();
        }
        // Se está função for chamada, limpa a Lista e adiciona a mensagem de "Carregando";
        selectLista.find('li').remove();
        listaAdd(selectLista, '', '<span class="text-primary"><i class="fa fa-spinner fa-pulse"></i> Carregando...</span>', true);
        // Se for Select, remove todos os options...
        if((tipo == 'select' || tipo == 'nivel') && !manterOptions) {
            dom.find('option').remove();
            dom.change();
            if(tipo == 'nivel') {
                nivelDestroyChild(selectArea);
                nivelValueToMuted();
                if(selectValue.is(':visible'))
                    nivelEditar(selectArea, true);
            }
        }
        
        //Prepara o Objeto para os filtros da pesquisa...
        var filtros = {input: val};
        if(tipo == 'nivel' && nivel) {
            var parentVal = null;
            var parentSelectArea = selectArea.prev();
            if(parentSelectArea.length) {
                var parentSelectAreaValueSpan = parentSelectArea.find('.rtlMultiSelectNivelValueSpan');
                parentVal = parentSelectAreaValueSpan.data('value');
            }
            filtros[nivel] = parentVal;
        }
            
        if(filtros_externos.length > 0) {
            var form = selectArea.closest('form');
            for(var i = 0; i < filtros_externos.length; i++) {
                var filtro_externo = form.find('#' + filtros_externos[i]);
                if(filtro_externo.is(':checkbox')) {
                    if (filtro_externo.is(":checked")) {
                        filtros[filtros_externos[i]] = 1;
                    } else {
                        filtros[filtros_externos[i]] = 0;
                    }
                } else if(filtro_externo.val()) {
                    filtros[filtros_externos[i]] = filtro_externo.val();
                }
            }
        }
        urlRequest = $.post(url, filtros, function(data) {
            selectLista.find('li').remove(); // Limpa lista
            if(data.ok == 2) { // Se erro mostra a mensagem de erro
                alert(data.message?data.message:'Erro não identificado!');
                return;
            }
            // Adiciona o resultado vindo do PHP na Lista
            $.each(data.data, function( index, value ) {
                listaAdd(selectLista, value['value'], value['label']);
            });
            listaFocarProximo(selectArea);
            // Se não hover resultados vindos do PHP, adiciona um Item sem valor com a mensagem "Sem Resultados"
            if(!data.data.length){
                listaAdd(selectLista, '', '<span class="text-danger"><i class="fa fa-fw fa-warning"></i> Nenhum resultado encontrado. Tente alterar o termo da pesquisa.</span>', true);
            }
        }, 'json');
    }
    /*************************************************************************************   / Filtrar Lista URL   ***/
    /***************************************************************************************   Novo Item a Lista   ***/
    function adicionarItem () {
        if(tipo == 'nivel') {
            alert('Não é possível adicionar valores em um elemento MultiLevel');
            return;
        }
        var val = $.trim(area.find('input').val());
        if(!val || tipo == 'input')
            return;
        // Se Multiple, verifica a quantidade Maxima (SelecionarItem())...
        if(tipo == 'multiple' && maxselecionados && selecionados.find('a').length > maxselecionados) {
            alert('Não foi possível adicionar item ' + val + '. Número máximo de itens selecionados alcançado (' + maxselecionados + ')');
            return;
        }
        // Adiciona novo termo na lista
        var selectLista = area.find('.rtlMultiSelectLista');
        listaAdd(selectLista, 'InsertInto:' + val, val);
        // Adiciona novo termo no dom selecionado
        selectLista.find('li:last').click();
        area.find('input').focus();
        if(tipo == 'multiple') {
            area.find('input').val('');
            if(url) {
                listaFiltrarUrl(area, true);
            } else {
                listaFiltrar(area);
            }
        }
    }
    /*************************************************************************************   / Novo Item a Lista   ***/
    /********************************************************************   Visibilidade do "Botão RemoverTodos"   ***/
    function removerTodosVisibilidade() {
        if(selecionados.find('.rtlMultiSelectRemoverItem').length > 0) {
            selecionados.append(area.find('.rtlMultiSelectRemoverTodos'));
            area.find('.rtlMultiSelectRemoverTodos').show();
        } else {
            area.find('.rtlMultiSelectRemoverTodos').hide();
        }
    }
    /******************************************************************   / Visibilidade do "Botão RemoverTodos"   ***/
    /*****************************************************************************   Focar Próximo Item na LIsta   ***/
    function listaFocarProximo(selectArea) {
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        if(selectLista.find('li:visible').length < 1) {
            selectLista.find('li').removeClass('rtlMultiSelectItemFocado');
            return;
        }
        if(selectLista.find('li.rtlMultiSelectItemFocado').length > 0) {
            var selecionado = selectLista.find('li.rtlMultiSelectItemFocado');
            selecionado.removeClass('rtlMultiSelectItemFocado');
            if(selecionado.nextAll(':visible').length > 0) {
                selecionado.nextAll(':visible:first').addClass('rtlMultiSelectItemFocado');
            } else {
                selectLista.find('li:visible:first').addClass('rtlMultiSelectItemFocado');
            }
        } else {
            selectLista.find('li:visible:first').addClass('rtlMultiSelectItemFocado');
        }
    }
    /***************************************************************************   / Focar Próximo Item na LIsta   ***/
    /****************************************************************************   Focar Item Anterior na LIsta   ***/
    function listaFocarAnterior (selectArea) {
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        if(selectLista.find('li:visible').length < 1) {
            selectLista.find('li').removeClass('rtlMultiSelectItemFocado');
            return;
        }
        if(selectLista.find('li.rtlMultiSelectItemFocado').length > 0) {
            var selecionado = selectLista.find('li.rtlMultiSelectItemFocado');
            selecionado.removeClass('rtlMultiSelectItemFocado');
            if(selecionado.prevAll(':visible').length > 0) {
                selecionado.prevAll(':visible:first').addClass('rtlMultiSelectItemFocado');
            } else {
                selectLista.find('li:visible:last').addClass('rtlMultiSelectItemFocado');
            }
        } else {
            selectLista.find('li:visible:last').addClass('rtlMultiSelectItemFocado');
        }
    }
    /**************************************************************************   / Focar Item Anterior na LIsta   ***/
    /***************************************************************************************   CRIAR NOVO SELECT   ***/
    function nivelSelecionarItem (selectArea, li) {
        var selectLista = selectArea.find('.rtlMultiSelectLista');
        var selectAreaValue = selectArea.find('.rtlMultiSelectNivelValue');
        var selectAreaValueSpan = selectAreaValue.find('.rtlMultiSelectNivelValueSpan');
        var selectSelecionados = selectArea.find('.rtlMultiSelectSelecionadosArea');
        // Teste se tem valor pra adicionar
        if(!li.data('value') || !li.text()) {
            alert('Não é possível adicionar esse item');
            return;
        }
        // Atualiza parte visual do select
        listaAlternarVisibilidade(selectArea, false);
        selectArea.find('input').val(''); // Reseta pra no filtro mostrar tudo
        listaFiltrar(selectArea); // Indenpendente de ULR, filtra as LIs
        selectArea.find('input').val(li.text()).focus();
        selectArea.find('input').css('color', '#555'); // red: cc0000, form-default: 555
        // Esconde o select e mostra o SPAN
        selectArea.css('width', 'auto');
        selectAreaValueSpan.data('value', li.data('value'));
        selectAreaValueSpan.text(li.text());
        selectAreaValue.find('.fa').toggleClass('fa-caret-right fa-spinner fa-spin');        
        selectAreaValue.show();
        selectSelecionados.hide();
        
        // Pega os dados para o próximo nível
        var val = selectAreaValueSpan.data('value');
        
        //Prepara o Objeto para os filtros da pesquisa...
        var filtros = {input: ''};
        filtros[nivel] = val;
        if(filtros_externos.length > 0) {
            var form = selectArea.closest('form');
            for(var i = 0; i < filtros_externos.length; i++) {
                var filtro_externo = form.find('#' + filtros_externos[i]);
                if(filtro_externo.is(':checkbox')) {
                    if (filtro_externo.is(":checked")) {
                        filtros[filtros_externos[i]] = 1;
                    } else {
                        filtros[filtros_externos[i]] = 0;
                    }
                } else if(filtro_externo.val()) {
                    filtros[filtros_externos[i]] = filtro_externo.val();
                }
            }
        }
        if (urlRequest) {
            urlRequest.abort();
        }
        urlRequest = $.post(url, filtros, function(data) {
            selectAreaValue.find('.fa').toggleClass('fa-caret-right fa-spinner fa-spin');
            if(data.ok == 2) { // Se erro mostra a mensagem de erro
                selectAreaValue.find('.fa').toggleClass('fa-caret-right fa-warning');
                selectAreaValue.removeClass('text-muted text-primary');
                selectAreaValue.addClass('text-danger');
                alert(data.message?data.message:'Erro não identificado!');
                return;
            }
            
            nivelDestroyChild(selectArea);
            nivelValueToMuted();
            dom.find('option').remove();
            if(data.data.length) {
                // Cria um select
                area.append(selectArea.clone(true));
                var novoSelectArea = selectArea.next();
                var novoSelectLista = novoSelectArea.find('.rtlMultiSelectLista');
                var novoSelectAreaValue = novoSelectArea.find('.rtlMultiSelectNivelValue');
                var novoSelectAreaValueSpan = novoSelectAreaValue.find('.rtlMultiSelectNivelValueSpan');
                var novoSelectSelecionados = novoSelectArea.find('.rtlMultiSelectSelecionadosArea');
                novoSelectArea.css('width', area_width);
                novoSelectAreaValueSpan.data('value', '');
                novoSelectAreaValueSpan.text('');
                novoSelectAreaValue.find('.fa').removeClass('fa-warning fa-spinner fa-spin');
                novoSelectAreaValue.find('.fa').addClass('fa-caret-right');
                novoSelectAreaValue.hide();
                novoSelectSelecionados.show();
                novoSelectLista.find('li').remove();
                $.each(data.data, function( index, value ) {
                    listaAdd(novoSelectLista, value['value'], value['label']);
                });
                novoSelectArea.find('input').val('').focus();
                novoSelectArea.find('input').css('color', '#cc0000'); // red: cc0000, form-default: 555
                listaAlternarVisibilidade(novoSelectArea, true);
                //setTimeout(function(){ listaFocarProximo(selectArea); }, 500);
                // Manipula o DOM
                if(!select_last) {// Se seleciona qualquer nível, atualiza DOM
                    dom.append('<option value="' + li.data('value') + '" selected="selected">' + li.text() + '</option>');
                    selectAreaValue.toggleClass('text-primary text-muted');
                }
            } else {
                // Manipula o DOM
                dom.append('<option value="' + li.data('value') + '" selected="selected">' + li.text() + '</option>');
                selectAreaValue.toggleClass('text-primary text-muted');
                selectAreaValue.find('.fa').attr('class', 'fa fa-caret-down');
            }
            dom.change();
        }, 'json');
    }
    /*************************************************************************************   / CRIAR NOVO SELECT   ***/
    /*******************************************************************************************   Editar Select   ***/
    function nivelEditar (selectArea, resetInput) {
        //var selectLista = selectArea.find('.rtlMultiSelectLista');
        var selectAreaValue = selectArea.find('.rtlMultiSelectNivelValue');
        //var selectAreaValueSpan = selectAreaValue.find('.rtlMultiSelectNivelValueSpan');
        var selectSelecionados = selectArea.find('.rtlMultiSelectSelecionadosArea');
        resetInput = resetInput === undefined?false:resetInput;
        
        selectAreaValue.find('.fa').removeClass('fa-spin fa-spinner fa-warning');
        selectAreaValue.find('.fa').addClass('fa-caret-right');
        selectAreaValue.removeClass('text-danger text-primary');
        selectAreaValue.addClass('text-muted');
        selectArea.css('width', area_width);
        selectAreaValue.hide();
        selectSelecionados.show();
        if(resetInput)
            selectArea.find('input').val('');
        else {
            selectArea.find('input').focus();
            listaAlternarVisibilidade(selectArea, true); // talvez dê problema...
            listaFocarProximo(selectArea);
        }
    }
    /*****************************************************************************************   / Editar Select   ***/
    /**********************************************************************************   Selects Value To Muted   ***/
    function nivelValueToMuted () {
        area.find('.rtlMultiSelectNivelValue').each(function( index ) {
            $(this).removeClass('text-primary text-danger');
            $(this).addClass('text-muted');
        });
    }
    /********************************************************************************   / Selects Value To Muted   ***/
    /***********************************************************************************   Destroy Select Childs   ***/
    function nivelDestroyChild(selectArea) {
        if(selectArea.nextAll().length)
            selectArea.nextAll().remove();
    }
    /*********************************************************************************   / Destroy Select Childs   ***/
    
    /***********************************************************************************************   / Métodos   ***/
}
/* / rtlMultiSelect */

function rtlFormCheckAll (checkbox, checkboxes_class) {
    /* Um recurso para automatizar a seleção de vários checkboxes ao mesmo tempo. Clica em um Checkbox e marca/desmarca todos os outros da mesma coleção, definidos por uma class
     * CHECKBOX: O dom que chama a função, que servirá como referencia para identificar o form contendo os demais checkboxes
    /* CHECKBOCEX_CLASS: Uma classe contida nos demais checkboxes para defi-los como uma coleção
     *    USO:
     * 1. <label><input type="checkbox" onchange="rtlFormCheckAll($(this), 'classe_que_define_a_colecao')"> Marcar Todos</label>
     *    O checkbox, que será clicado para marcar/desmarcar os demais. O LABEL é opcional. 
     *    Um INPUT type CHECKBOX com um ONCHANGE chamando essa função.
     *    Na função passa o THIS e o nome da classe que estará nos demais checkboxes
     * 2. <label class="btn btn-success"> <input name="nome_para_o_post[]" class="classe_que_define_a_colecao" value="<?php echo $item->get('id')?>" type="checkbox"> Marcar </label>
     *    Os checkboxes que serão marcados serão como acima. Vários desses nesse formato, alterando apenas o VALUE DO INPUT. O LABEL é opcional...
     *    NAME é o nome para passar o ARRAY pelo POST
     *    CLASS é a classe que define a coleção. 
     */
    var form = checkbox.closest('form');
    if(!checkboxes_class) return;// se classe que define a coleção não for passada ignora a função
    form.find('input.' + checkboxes_class).each(function () {
        $(this).prop("checked", checkbox.prop("checked"));
    });
}

/* FUNÇÕES GERÉRICAS */
function setMasks() {
    /* Adiciona as máscaras em cada input com a classe referenciada
     *    USO:
     * Qualquer input text com uma das classes abaixo:
     * maskAsMoney: Máscara de moeda, no formato R$ 0,00 - Usuário digita apenas os números
     * maskAsDate: Máscara de data, no formato 99/99/9999 - Usuário digita apenas os números
     * maskAsHora: Máscara de hora, no formato 99:99 - Usuário digita apenas os números
     * maskAsDayMonth: Máscara de data apenas com Dia e Mês, no formato 99/99 - Usuário digita apenas os números
     * maskAsCnpj: Máscara de CNPJ, no formato 99.999.999/9999-99 - Usuário digita apenas os números
     * maskAsCpf: Máscara de CPF, no formato 999.999.999-99 - Usuário digita apenas os números
     * maskAsCep: Máscara de CEP, no formato 99.999-999 - Usuário digita apenas os números
     * maskAsProcessoJudicial: Máscara de número de processo no padrão CNJ, no formato 9999999-99.9999.9.99.9999 - Usuário digita apenas os números
     * maskAsGrau: Máscara de grau de receita oftalmológia, no formato 9,99 com possibilidade de valores negativos - Usuário digita apenas os números
     * maskAsTel: Máscara de Telefone, no formato(99) 9999-9999 com 8 ou 9 dígitos - Usuário digita apenas os números
     */
    $('.maskAsMoney').maskMoney({prefix:'R$ ', affixesStay: true, thousands: '.', decimal: ',', showSymbol: true, allowZero: true});    
    //$(".maskAsDate").mask("99/99/9999",{placeholder:" "}); // Função própria...
    $(".maskAsHora").mask("99:99",{placeholder:" "});
    $(".maskAsDayMonth").mask("99/99",{placeholder:" "});
    $(".maskAsCnpj").mask("99.999.999/9999-99",{placeholder:" "});
    $(".maskAsCpf").mask("999.999.999-99",{placeholder:" "});
    $(".maskAsCep").mask("99.999-999",{placeholder:" "});
    $(".maskAsProcessoJudicial").mask("9999999-99.9999.9.99.9999",{placeholder:" "});
    $('.maskAsGrau').maskMoney({thousands: '.', decimal: ',', allowZero: true, allowNegative: true});
    $(".maskAsGrauEixo").mask("999º",{placeholder:" ", autoclear: false});
    $('.maskAsTel').each(function(index, value) {
        var tel = $(this).val().replace(/\D/g, '');
        if(tel.length > 10)
            $(this).mask("(99) 9 9999-999?9",{placeholder:" "});
        else
            $(this).mask("(99) 9999-9999?9",{placeholder:" "});
    });
    $('body').on('keyup', '.maskAsTel', function(e) {
        var tel = $(this).val().replace(/\D/g, '');
        if(tel.length === 0) {
            $(this).unmask();
            $(this).mask("(99) 9999-9999?9",{placeholder:" "});
        }
        if(tel.length < 10)
            return;
        $(this).unmask();
        if(tel.length > 10) {
            $(this).mask("(99) 9 9999-999?9",{placeholder:" "});
        } else {
            $(this).mask("(99) 9999-9999?9",{placeholder:" "});
        }
    });
}

function confirmUrl(url, message, loadindIcon) {
    /* Um pedido de confirmação para acessar determinada página. Só acessa a URL perante confirmação. Não havendo confirmação nada acontece 
     * URL: A url que deseja confirma antes de acessar 
    /* MESSAGE: Uma mensagem opcional para aparecer na mensagem de confirmação 
     *    USO:
     * Camada em javacript, como no exemplo abaixo...
     * <a href="javascript: " onclick="confirmUrl('[URL]', 'MESSAGE');" OUTROS_ATRIBUTOS_SÃO_OPCIONAIS>  </a>   
     * ... ou em qualquer parte do JAVASCRIPT
     */
    if(!message) message = 'Você realmente deseja concluir esta operação?';
    if(!confirm(message))
        return false;
    if(loadindIcon) {
        loadindIcon.find('i.fa').attr('class', 'fa fa-spin fa-spinner');
    }
    
    $(window.document.location).attr('href', url);
    return false;
}

function excluirItemAjax(url, fade_dom, message, animation) {
    /* Uma função para excluir registros através de Ajax. Uma url é chamada que exclui o registro no banco de dados e retorna um Json
     * URL: A url que processará a exclusão do registro e retornará o sucesso ou não da exclusão 
     * FADE_DOM: O DOM HTML para desaparecer em caso de exclusão bem sucedida 
     * MESSAGE: Uma mensagem opcional para aparecer na mensagem de confirmação de exclusão
     * ANIMATION: NULL ou TRUE: Adiciona um spinner no icone "fa-times"; FALSE: Não faz a animação
     *    USO:
     * Camada em javacript, como no exemplo abaixo...
     *    <a href="javascript: " onclick="excluirItemAjax('[URL]', [FADE_DOM], '[MESSAGE]');"></a>
     * ... ou em qualquer parte do JAVASCRIPT
     * 
     * Uma URL que retorne um Json com as seguintes variáveis: 
     *    ok: 1. Sucesso e desapare o DOM; 2: Nada modifica e mostra mensagem de erro;
     *    message = Caso OK seja 2, uma mensagem de erro para ser exibida ao usuário
     */
    message = message?message:'Você realmente deseja excluir este registro?';
    animation = animation?animation:true;
    if(!confirm(message))
        return;
    if(animation) {
        fade_dom.find('.fa-times').toggleClass("fa-times fa-spinner fa-spin");
        fade_dom.find('.fa-check').toggleClass("fa-check fa-spinner fa-spin");
        fade_dom.find('.fa-warning').toggleClass("fa-warning fa-spinner fa-spin");
    }
    
    //console.log(url + (url.indexOf("?") < 0?'?':'&') + 'ajax=1');// Ajustes para o Symfony Router
    $.post(url + (url.indexOf("?") < 0?'?':'&') + 'ajax=1', null, function(data) {
        if(!data.ok){
            alert('Não foi possível acessar recurso. Por favor, avise à administração a ocorrência deste erro');
            fade_dom.find('.fa-times').toggleClass("fa-times fa-warning");
            fade_dom.find('.fa-spinner').toggleClass("fa-spinner fa-spin fa-warning");
        } else {
            if(data.ok == 1) {
                if(animation)
                    fade_dom.find('.fa-spinner').toggleClass("fa-spinner fa-spin fa-check");
                if(fade_dom)
                    fade_dom.remove();
                if(data.message != ''){
                    alert(data.message);
                }
            } else {
                if(animation)
                    fade_dom.find('.fa-spinner').toggleClass("fa-spinner fa-spin fa-warning");
                if(data.message != ''){
                    alert(data.message);
                } else {
                    alert('Não foi possível excluir registro');
                }
            }
        }
    }, 'json');
}

function toogleDiv(div, focus){
    /* Uma função para esconder e mostrar algum elemento HTML, preferencialmente um DIV. Muito usado nos forms que aparecem e desaparecem das páginas dos registros 
     * DIV: O elemento que irá aparecer ou desaparecer (passar sem o #) 
     * FOCUS: opcionalmente, o ID de um input para focar depois da operação (passar sem o #) 
     *    USO:
     * Camada em javacript, como no exemplo abaixo...
     *    <a href="javascript: " onclick="toogleDiv('[DIV]', '[FOCUS]')" OUTROS_ATRIBUTOS_SAO_OPCIONAIS> </a>
     * ... ou em qualquer parte do JAVASCRIPT 
     */
    var vel = 1000;
    div = $('#' + div);
    if(div.css('display') == 'none') {
        div.removeClass('d-none');
        div.hide();
        div.slideDown(vel);
        if(focus) setTimeout("$('#" + focus + "').focus();", vel/2);
    } else {
        div.slideUp(vel);
    }
}

function cpfCnpjForm (dom) {
    /* Função para esconder e mostrar os Inputs de um Form de acordo com os dados relacionados a CPF ou CNPJ. 
     * DOM: O input dentro de um form que representa o Tipo (CPF ou CNPJ) dentro do form a ser modificado 
     * Modificar os Vetores "cpfElementos" e "cnpjElementos" de acordo com os atributos de CPF e CNPJ de cada projeto
     * 
     * Opções:
     *    data-id_prefix: O prefixo para os IDs do elementos do form;
     *       NULO, VAZIO ou NÂO REFERENCIAR:
     *          Form tem Attr Name: pega o attr name do form
     *          Form NÃO tem Attr Name: prefixo fica vazio
     *       STRING: usa a STRING como prefixo para os atributos do form;
     *       '' (string vazia): Não usa prefixo
     *       
     * Ex.:
     * Um SELECT no Form a ser modificado como no exemplo abaixo:
     * <select name="tipo" id="tipo" class="cpfCnpjForm">
     *    <option value="1" label="Pessoa Física" selected="selected">Pessoa Física</option>
     *    <option value="2" label="Pessoa Jurídica">Pessoa Jurídica</option>
     * </select>
     * 
     *           SYMFONY FORM + BOOTSTRAP
     */
    
    var cpfElementos = ['apelido', 'rg', 'matricula', 'aposentado', 'nit', 'titulo_eleitor', 'ctps', 'nascimento', 'sexo', 'mae_nome', 'pai_nome', 'nacionalidade', 'profissao', 'estado_civil', 'escolaridade'];
    var cnpjElementos = ['razao_social', 'insc_esta', 'insc_muni', 'genero', 'contato_nome', 'contato_cargo', 'contato_email', 'contato_tel'];
    var form = dom.closest('form');
    var id_prefix = dom.data('id_prefix') === undefined?form.attr('name'):(dom.data('id_prefix') == ''?'':dom.data('id_prefix'));
    id_prefix+= id_prefix?'_':'';
    
    updateForm(false);
    
    function updateForm(clean) {
        var tipo = parseInt(dom.val()?dom.val():1);
        var addElementos;
        var removeElementos;
        if(tipo == 1) {
            form.find("label[for='" + id_prefix + "nome']").text('Nome Completo');
            form.find("label[for='" + id_prefix + "doc']").text('CPF');
            if (clean) form.find("#" + id_prefix + "doc").val('');
            form.find("#" + id_prefix + "doc").mask("999.999.999-99",{placeholder:" "});
            addElementos = cpfElementos;
            removeElementos = cnpjElementos;
        } else if(tipo == 2) {
            form.find("label[for='" + id_prefix + "nome']").text('Nome Fantasia');
            form.find("label[for='" + id_prefix + "doc']").text('CNPJ');
            if (clean) form.find("#" + id_prefix + "doc").val('');
            form.find("#" + id_prefix + "doc").mask("99.999.999/9999-99",{placeholder:" "});
            addElementos = cnpjElementos;
            removeElementos = cpfElementos;
        }
        var vel = 500;
        for(i=0; i < removeElementos.length; i++) {
            form.find('#' + id_prefix + removeElementos[i]).closest('.form-group').hide();
        }
        for(i=0; i < addElementos.length; i++){
            form.find('#' + id_prefix + addElementos[i]).closest('.form-group').slideDown(vel);
            if (clean) form.find('#' + id_prefix + addElementos[i]).val('');
        }
        form.find('#' + id_prefix + 'nome').focus();
    }
    
    dom.on('change', function(e) {
        updateForm(true);
    });
    
}

function rtlProtectUnload() {
    /* Protege dados de um form de serem perdidos por uma mudança acidental de página
     * SEM PARÂMETROS
     *    USO:
     * Basta adicionar a classe "rtlProtectUnload" no campo que precisa ser protegido;
     * Campos com a classe "ckeditor", o Editor de Textos, já serão protegidos por padrão;
     *    <input name="***" id="***" value="***" class="rtlProtectUnload [e outras opcionalmente]" type="text">
     */
    // Inicializa os Inputs com classe a "rtlProtectUnload"
    $('.rtlProtectUnload').each(function(index, value) {
        if($(this).attr('type') === 'text') {
            $(this).on('input', function() {
                $(window).bind("beforeunload", function() {
                    // Validações para saber se confirma o Unload da Página
                    return "Você deseja realmente sair dessa página?";
                });
                $(this).off('input');
            });
        } else {
            $(this).on('change', function() {
                $(window).bind("beforeunload", function() {
                    // Validações para saber se confirma o Unload da Página
                    return "Você deseja realmente sair dessa página?";
                });
                $(this).off('change');
            });
        }
        var form = $(this).closest("form");
        form.on("submit", function(event) {
            $(window).unbind('beforeunload');
        });
    });
    // Inicializa os CKEDITORs, que por padrão já vem protegidos
    for (var i in CKEDITOR.instances) {
        CKEDITOR.instances[i].on('change', function( ev ) {
            $(window).bind("beforeunload", function() {
                // Validações para saber se confirma o Unload da Página
                return "Você deseja realmente sair dessa página?";
            });
            ev.removeListener();
        });
        var form = $('#' + i).closest("form");
        form.on("submit", function(event) {
            $(window).unbind('beforeunload');
        });
    }
}

function rtlShowHideDependente (campo, referencia, setUp) {
    /* Esconde ou mostra um campo de um form de acordo com o valor de um outro campo no mesmo form
     * CAMPO: Elemento a ser escondido ou exposto.
     * REFERENCIA: O campo que pelo seu valor, determinara se o campo esconde ou mostra.
     *    USO:
     * O campo que será escondido ou mostrado precisa possuir os atributos mínimos, como abaixo:
     *    <input name="NOME_DO_CAMPO" id="NOME_DO_CAMPO" value="COM_OU_SEM_VALOR" class="rtlShowHideDependente [e outras opcionalmente]" data-referencia="[ID_ELEMENTO_DO_FORM]" data-valores="[valor] ou [["valor1","valor2"]]" type="text">
     *       data-referencia: O id do elemento que servirá de base para esconder ou mostrar o campo
     *       data-valores: O valor necessário para mostrar o campo
     *          Se o valor do campo de referencia for diferente de data-valores o campo será escondido...
     *          Se o valor do campo de referentia for igual ao valor de data-valores o campo será exibido
     *       data-focus: Para focar no campo selecionado após mudança do valor do campo de referencia
     * Para mais de uma valor para mostrar o campo, basta passar em data valores um array no formato: ["valor1","valor2","..."]
     * Os tipos dos campos de referencia podem ser Select, Checkbox e Input:Text
     */
    //alert('Campo ' + campo.attr('name') + "\n referencia: " + campo.data('referencia') + "\n ref.val(): " + referencia.val() + "\n campo.valores: " + campo.data('valores'));
    var mostrar = false;
    var valores = campo.data('valores');
    if(valores.constructor !== Array)
        valores = [valores];
    for(i = 0; i < valores.length; i++) {
        if(referencia.is(':checkbox')) {
            if (valores[i] == 1 && referencia.is(":checked")) {
                mostrar = true;
            } else if (valores[i] == 0 && !referencia.is(":checked")) {
                mostrar = true;
            }
        } else if(referencia.val() == valores[i]) {
            mostrar = true;
        }
    }
    
    var vel = setUp?0:500;
    if(mostrar) {
        campo.closest('.form-group').slideDown(vel, function(){
            if(!setUp && campo.data('focus')) {campo.focus();}
        });
    } else {
        campo.closest('.form-group').slideUp(vel);
    }
}

function rtlSelectMultLevel(dom, setUp) {
    /* Cria vários selects aninhados para entidades relacionadas a elas mesmo. Para situações de Hierarquia.
     * DOM: Elemetno HTML responsável pela chamada da função.
     * SETUP: Indentificar se a chamada é no início ou não do carregamento da página.
     *    USO:
     * Um input text para receber o valor do atributo, como abaixo:
     *    <input name="NOME_DO_CAMPO" id="NOME_DO_CAMPO" value="COM_OU_SEM_VALOR" class="rtlSelectMultLevel [e outras opcionalmente]" data-select_any="1 ou 2]" data-url="pagina php que alimentará os SELECTS" type="text">
     *       data-select_any: 1. Qualquer nível selecionado o valor é considerado; 0. Seleciona apenas o último nível;
     *       data-url: Url em php para criar o Json de retorno com os SELECTS
     * Uma url que retorne um Json com as seguintes variáveis: 
     *    ok: 1. Sucesso e cria os selects; 2: Erro e mostra mensagem de erro;
     *    select = o vetor com os options do select a ser criado. Vetor no formato VetorOptions = array(array('key' => VALOR_DO_ATRIB, 'value' => LABEL PARA O OPTION), array('key' => VALOR_DO_ATRIB, 'value' => LABEL PARA O OPTION), ...); 
     *    selecionado = O label do item selecionado;
     *    message = Caso OK seja 2, uma mensagem de erro para ser mostrada
     */
    setUp = setUp?true:false;
    var form = dom.closest("form");
    if(setUp) {
        var input = dom;
        input.css('display', 'none');
        var acessorios = '<p class="form-control-static"> <span id="rtlSelectMultLevelLabel"> Aguarde... </span> ';
        acessorios+= '<a class="btn btn-warning btn-sm rtlSelectMultLevelUpdate" href="javascript: " rel="' + input.attr('id') + '" onclick="rtlSelectMultLevel($(this));" title="Alterar Seleção"><i class="fa fa-refresh"></i></a> ';
        acessorios+= '<a class="btn btn-danger btn-sm rtlSelectMultLevelStatus" title="Carregando informações"><i class="fa fa-spinner fa-pulse"></i></a>';
        acessorios+= '</p>';
        if(form.hasClass('form-inline'))
            acessorios+= '<br/>';
        input.parent().append(acessorios);
    } else {
        var input = form.find('#' + dom.attr('rel'));
        if(dom.hasClass('rtlSelectMultLevelUpdate')) {// se o botão do update for clicado
            input.val('');
            input.parent().find('#rtlSelectMultLevelLabel').text('Selecione abaixo');
            input.parent().find('.rtlSelectMultLevelUpdate').hide();
            input.parent().find('select').remove();
        } else if (dom.val() == 0) {//Se um SELECT for selecionado e estiver sem valor, apagar os selects filhos
            var index = input.parent().find('select.rtlSelectMultLevel').index(dom);
            input.parent().find('select.rtlSelectMultLevel:gt('+index+')').remove();
            return;
        }
        input.parent().find('.rtlSelectMultLevelStatus').show(500);
    }
    var id = setUp?input.val():dom.val();
    $.post(input.data('url'), {id: id}, function(data) {
        input.parent().find('.rtlSelectMultLevelStatus').hide();
        if(!data.ok) {
            alert('Serviço indisponível: ' + input.data('url'));
            if(setUp) {
                input.css('display', 'block');
                input.parent().find('a').remove();
                input.parent().find('p').remove();
                input.parent().find('select').remove();
            }
        } else if(data.ok == 1) {
            input.parent().find('#rtlSelectMultLevelLabel').text('Selecione abaixo');
            input.parent().find('.rtlSelectMultLevelUpdate').css('display', 'none');
            if(data.select) {
                if(!setUp) {
                    var index = input.parent().find('select.rtlSelectMultLevel').index(dom);
                    input.parent().find('select.rtlSelectMultLevel:gt('+index+')').remove();
                }                
                var select = '<select rel="' + input.attr('id') + '" class="form-control rtlSelectMultLevel" name="">';
                for (var i = 0; i < data.select.length; ++i) {
                    select+= '<option label="' + data.select[i]['value'] + '" value="' + data.select[i]['key'] + '">' + data.select[i]['value'] + '</option>';
                }
                select+= '</select>';
                input.parent().append(select);
                if(setUp && data.selecionado) {
                    input.parent().find('select').remove();
                }
            }
            if(data.selecionado) {
                if(input.data('select_any') == 1 || setUp) {
                    input.parent().find('#rtlSelectMultLevelLabel').text(data.selecionado);
                    input.parent().find('.rtlSelectMultLevelUpdate').show(500);
                    if(!setUp) { // Por que se setUp for true dom não existe!!!
                        input.val(dom.val());
                    }
                } else {
                    if(!data.select) {
                        input.parent().find('#rtlSelectMultLevelLabel').text(data.selecionado);
                        input.parent().find('.rtlSelectMultLevelUpdate').show(500);
                        if(!setUp) { // Por que se setUp for true dom não existe!!!
                            input.val(dom.val());
                        }
                    } 
                }
                if(!data.select) {
                    input.parent().find('select').remove();
                }
            }
        } else if (data.ok == 2) {
            alert(data.message);
        }
    }, 'json');
}

function rtlSelectToSelect(select, setUp) {
    /* Função para alterar os valores de um Select quando outro é modificado
     * SELECT: o selec que modifica o target
     * SETUP: Indentificar se a chamada é no início ou não do carregamento da página.
     *    USO:
     * Um select com os valores originais e um input text que será um select com os valores dependentes do select original
     *    O Select original é como abaixo:
     *       <select name="[NOME_DO_CAMPO]" id="NOME_DO_CAMPO" class="rtlSelectToSelect [E mais outras classes opcionais]" data-target="[O ID DO INPUT TEXT QUE RECEBERÁ NOVO SELECT]" data-first="[UM TEXTO INICIAL PARA O TARGET]" data-url="[URL QUE ALIMENTARÁ O NOVO SELECT]">
     *          Lista de ... <option value="" label=""> Selecione um valor </option>
     *       </select>
     *       data-target: É o id do text que será o select com os valores
     *       data-first: Um texto inicial para o select do target
     *       data-url: Url em php para criar o Json de retorno com os SELECTS
     * Um input text para ser substitudio por um select com valores vindos de data-url:
     *    <input name="[NOME_DO_CAMPO]" id="[NOME_DO_TARGET_NO_SELECT_ORIGINAL]" value="[COM_OU_SEM]" class="estará escondido]" type="text">
     * Uma url que retorne um Json com as seguintes variáveis: 
     *    ok: 1. Sucesso e alimenta os selects; 2: Erro e mostra mensagem de erro;
     *    select = o vetor com os options do select a ser criado. Vetor no formato VetorOptions = array(array('key' => 'VALOR_DO_OPTION', 'value' => 'LABEL DO OPTION'), array('key' => VALOR_DO_OPTION, 'value' => LABEL DO OPTION), ...); 
     *    message = Caso OK seja 2, uma mensagem de erro para ser mostrada
     */
    
    setUp = setUp?true:false;
    var target = select.closest("form").find('#' + select.data('target'));
    if (setUp) {
        target.css('display', 'none');
        target.parent().append('<select id="' + target.attr('id') + '_select" data-target="'+target.attr('id')+'" class="form-control" name=""><option value="0">Carregando...</option></select>');
    } 
    var target_select = select.closest("form").find('#' + target.attr('id') + '_select');
    
    if (setUp) {
        target_select.change(function() { 
            var tmp_target = $(this).closest("form").find('#' + $(this).data('target'));
            tmp_target.val($(this).val()).change();
        });
    }
    
    if (!select.val()) {
        target_select.html('<option value="" label="' + select.data('first') + '">' + select.data('first') + '</option>');
        target.val('').change();
        return true;
    } else {
        target_select.html('<option value="" label="Carregando...">Carregando...</option>');
    }
    $.post(select.data('url'), {id: select.val()}, function(data) {
        if(!data.ok) {
            alert('Serviço indisponível: ' + select.data('url'));
            target_select.html('<option value="">Serviço indisponível...</option>');
        } else if(data.ok == 1) {
            if(data.select) {
                var options = '';
                for (var i = 0; i < data.select.length; ++i) {
                    options+= '<option label="' + data.select[i]['value'] + '" value="' + data.select[i]['key'] + '">' + data.select[i]['value'] + '</option>';
                }
                target_select.html(options);
                if (setUp) {
                    target_select.val(target.val());
                } else {
                    target.val(target_select.val()).change();
                }
            } else {
                alert('Select não identificado');
            }
        } else if (data.ok == 2) {
            alert(data.message);
            target_select.html('<option value="">' + data.message + '</option>');
            target.val(target_select.val()).change();
        }
    }, 'json');
}

/* / FUNÇÕES GERÉRICAS */

/* FUNÇÕES DA LIBRARY Twitter_Bootstrap3_Form_Horizontal */
function limparFiltros(dom) {
    /* Função para automatizar o recurso de limprar filtros em um Form de Pesquisa. 
     * DOM: o elemento BUTTON que serve como gatilho da limpeza do form de pesquisa 
     *    USO:
     * Um BUTTON no form, como abaixo:
     *    <button name="limpar_filtros" id="limpar_filtros" type="button" onclick="limparFiltros($(this));">  </button>
     *    É obrigatório como estão no exemplo os atributos NAME, ID, TYPE e o ONCLICK
     * A Library RTL_BUSCA com o recurso "limpar_filtros" nela
     */
    var form = dom.closest("form");
    form.append('<input id="limpar_filtros" name="limpar_filtros" value="1" type="hidden">');
    form.submit();
}

function fixBootstrapForms(form){ 
    /* Função para remover os DTS e DDs criados erroneamente pelo Twitter_Bootstrap3_Form  
     * FORM: O form a ser corrigido 
     */
    form.find('dt').remove();
    form.find('dd').remove();
}

function fixBootstrapFormPesquisa(dom){ 
    /* Função para remover os DTS e DDs criados erroneamente pelo Twitter_Bootstrap3_Form  
     * DOM: A DIV do form a ser corrigido
     *    ATÉ EU CONSEGUIR COLOCAR A SOLUÇÃO NO PROPRIO FORM DO TWIG   *** 
     */
    dom.append(dom.closest('form').find('.pesquisa_buttons').html());
    dom.closest('form').find('.pesquisa_buttons').remove();
}
/* / FUNÇÕES DA LIBRARY Twitter_Bootstrap3_Form_Horizontal */

/* FUNÇÕES ESPECIFICAS: Aguiar Sistema */
function formProcessoAlternarNumero(checkbox, setUp) {
    /* FUNÇÃO ESPECÍFICA PARA O FORMULÁRIO PROCESSOS
     * Numero 99999
     * Temp 414141
     * _temp 99999
     */
    var form = checkbox.closest('form');
    var numero = form.find('#numero');
    var temp = form.find('#numero_temporario');
    var _temp = '';
    if (checkbox.is(":checked")) {
        numero.unmask();
        numero.prop('readonly', true);
        form.find('#numero_alternativo').focus();
        if(!setUp) {
            _temp = temp.val();
            temp.val(numero.val());
            numero.val(_temp);
        }
    } else {
        if(!setUp) {
            _temp = temp.val();
            temp.val(numero.val());
            numero.val(_temp);
            numero.focus();
        }
        numero.mask("9999999-99.9999.9.99.9999",{placeholder:" ", autoclear: false}); // Padrão igual ao em setMasks()
        numero.prop('readonly', false);
    }
}
/* / FUNÇÕES ESPECIFICAS: Aguiar Sistema */