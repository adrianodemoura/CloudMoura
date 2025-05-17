function file(action, path) {
    let message = "";
    let title = "";
    let messageBtn = "";
    let getConfirmDelete = false;

    switch (action) {
        case "deleteDir":
            title = "Excluir Diretório";
            messageBtn = "Excluir Diretório";
            message = `Tem certeza que deseja excluir diretório "${path}"?`;
            getConfirmDelete = true;
            break;
        case "deleteFile":
            title = "Excluir Arquivo";
            messageBtn = "Excluir Arquivo";
            message = `Tem certeza que deseja excluir arquivo "${path}"?`;
            getConfirmDelete = true;
            break;
        case "rename":
            title = "Renomear Arquivo";
            messageBtn = "Renomear Arquivo";
            message = `Tem certeza que deseja renomear arquivo "${path}"?`;
            break;
        case "createSubdirectory":
            title = "Criar Subdiretório";
            messageBtn = "Criar Subdiretório";
            message = "Informe o nome do subdiretório:";
            break;
    }

    if ( getConfirmDelete ) {
        showModalDelete(message, title, messageBtn).then( result => { if (result) { getAjax(path, action); } } );
    } else if (action === 'upload') {
        showModalUpload(action, path);
    } else if (action === 'createSubdirectory') {
        showModalCreateSubdir(action, path);
    } else {
        getAjax(path, action);
    }
}

function getAjax(path, action) {
    fetch('/api/files/' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ path: path, action: action })
    })
    .then(response => {
        return response.json().then(data => {
            if (!response.ok) { throw data }
            return data
        } )
    } )
    .then( data => {
        if (data.success) {
            if ( action === 'download' ) {
                setDataDownload(data.data.content, path);
            } else {
                sessionStorage.setItem('message', JSON.stringify({ message: data?.message, success: data?.success }));
                window.location.reload();
            }
        } else {
            showAlert( data.message || 'Erro ao processar a requisição', false);
        }
    } )
    .catch( error => {
        showAlert(error.message || 'Erro ao processar a requisição', false);
    } )
}

function setDataDownload(content, path) {
    // Converte o conteúdo base64 para blob
    const byteCharacters = atob(content);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: 'application/octet-stream' });
    
    // Cria URL do blob
    const url = window.URL.createObjectURL(blob);
    // Cria link temporário
    const a = document.createElement('a');
    a.href = url;
    a.download = path.split('/').pop(); // Pega o nome do arquivo do path
    document.body.appendChild(a);
    a.click();

    // Limpa
    window.URL.revokeObjectURL(url);
    a.remove();
}

function showModalDelete(message, title, messageBtn) {
    const deleteModal = document.getElementById('deleteModal');
    const modalTitle = document.getElementById('deleteModalLabel');
    const modalBody = document.getElementById('modalBody');
    const confirmBtn = document.getElementById('confirmDelete');
    
    if (!deleteModal || !modalBody || !confirmBtn) {
        console.error('Elementos do modal não encontrados');
        return Promise.resolve(false);
    }
    
    const modal = new bootstrap.Modal(deleteModal);
    modalBody.textContent = message;
    modalTitle.textContent = title;
    confirmBtn.textContent = messageBtn;
    return new Promise((resolve) => {
        confirmBtn.onclick = () => {
            modal.hide();
            resolve(true);
        };
        modal.show();
    });
}

function showModalUpload(action, path) {
    const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const uploadButton = document.getElementById('uploadButton');
    const progressBar = document.querySelector('.progress');
    const progressBarInner = document.querySelector('.progress-bar');

    // Limpa o formulário quando o modal é aberto
    uploadForm.reset();
    progressBar.classList.add('d-none');
    progressBarInner.style.width = '0%';

    // Configura o evento de upload
    uploadButton.onclick = async function() {
        if (!uploadForm.checkValidity()) {
            uploadForm.reportValidity();
            return;
        }

        const file = fileInput.files[0];
        if (!file) {
            showAlert('Selecione um arquivo', false);
            return;
        }

        // Mostra a barra de progresso
        progressBar.classList.remove('d-none');
        progressBarInner.style.width = '0%';

        // Converte o arquivo para base64
        const reader = new FileReader();
        reader.readAsDataURL(file);
        
        // Atualiza o progresso durante a leitura do arquivo
        reader.onprogress = function(event) {
            if (event.lengthComputable) {
                const percentLoaded = Math.round((event.loaded / event.total) * 100);
                progressBarInner.style.width = percentLoaded + '%';
            }
        };

        reader.onload = async function() {
            const base64File = reader.result.split(',')[1]; // Remove o prefixo "data:application/octet-stream;base64,"
            
            const data = {
                action: action,
                path: path,
                file: base64File,
                filename: file.name
            };
            
            try {
                // Atualiza a barra para 50% antes de começar o upload
                progressBarInner.style.width = '50%';

                const response = await fetch('/api/files/upload', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }

                const responseData = await response.json();
                console.log(responseData);

                if (responseData.success) {
                    // Atualiza a barra para 100% quando o upload termina com sucesso
                    progressBarInner.style.width = '100%';
                    sessionStorage.setItem('message', JSON.stringify({ message: responseData?.message, success: responseData?.success }));
                    
                    // Aguarda um momento para mostrar o 100% antes de recarregar
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showAlert(responseData.message || 'Erro ao fazer upload do arquivo', false);
                    progressBar.classList.add('d-none');
                    progressBarInner.style.width = '0%';
                }
            } catch (error) {
                console.error('Erro:', error);
                showAlert('Erro ao fazer upload do arquivo', false);
                progressBar.classList.add('d-none');
                progressBarInner.style.width = '0%';
            }
        };

        reader.onerror = function() {
            showAlert('Erro ao ler o arquivo', false);
            progressBar.classList.add('d-none');
            progressBarInner.style.width = '0%';
        };
    };

    modal.show();
}

function showModalCreateSubdir(action, path) {
    const modal = new bootstrap.Modal(document.getElementById('createSubDirModal'));
    const createDirName = document.getElementById('createDirName');
    const confirmCreate = document.getElementById('confirmCreate');

    // Limpa o input quando o modal é aberto
    createDirName.value = '';
    
    // Foca no input quando o modal é aberto
    document.getElementById('createSubDirModal').addEventListener('shown.bs.modal', function () {
        createDirName.focus();
    });

    // Configura o evento de upload
    confirmCreate.onclick = async function() {
        if (!createDirName.checkValidity()) {
            createDirName.reportValidity();
            return;
        }

        // Fecha o modal antes de fazer a chamada AJAX
        modal.hide();

        // Faz a chamada AJAX
        getAjax(path + '/' + createDirName.value, action);
    };
    
    modal.show();
}
