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
        headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
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
    const uploadTypeSwitch = document.getElementById('uploadTypeSwitch');
    const uploadHelpText = document.getElementById('uploadHelpText');
    const uploadFilesList = document.querySelector('.upload-files-list');
    const filesList = document.getElementById('uploadFilesList');

    // Configura o switch de tipo de upload
    uploadTypeSwitch.addEventListener('change', function() {
        if (this.checked) {
            // Modo diretório
            fileInput.removeAttribute('accept');
            fileInput.setAttribute('webkitdirectory', '');
            fileInput.setAttribute('directory', '');
            uploadHelpText.textContent = 'Selecione um diretório para upload';
        } else {
            // Modo arquivo
            fileInput.setAttribute('accept', allowedExtensions.join(','));
            fileInput.removeAttribute('webkitdirectory');
            fileInput.removeAttribute('directory');
            uploadHelpText.textContent = 'Formatos aceitos: ' + allowedExtensions.join(', ');
        }
        // Limpa o input e a lista
        fileInput.value = '';
        filesList.innerHTML = '';
        uploadFilesList.classList.add('d-none');
    });

    // Atualiza a lista quando arquivos são selecionados
    fileInput.addEventListener('change', function() {
        filesList.innerHTML = '';
        if (this.files.length > 0) {
            uploadFilesList.classList.remove('d-none');
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                if (file.isDirectory) continue;

                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <span class="fs-7 text-truncate" style="max-width: 70%;">${file.webkitRelativePath || file.name}</span>
                    <span class="fs-10 badge bg-primary rounded-pill">${formatFileSize(file.size)}</span>
                `;
                filesList.appendChild(li);
            }
        } else {
            uploadFilesList.classList.add('d-none');
        }
    });

    // Função para formatar o tamanho do arquivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Limpa o formulário quando o modal é aberto
    uploadForm.reset();
    progressBar.classList.add('d-none');
    progressBarInner.style.width = '0%';
    filesList.innerHTML = '';
    uploadFilesList.classList.add('d-none');

    // Configura o evento de upload
    uploadButton.onclick = async function() {
        if (!uploadForm.checkValidity()) {
            uploadForm.reportValidity();
            return;
        }

        const files = fileInput.files;
        if (files.length === 0) {
            showAlert('Selecione um arquivo ou diretório', false);
            return;
        }

        // Mostra a barra de progresso
        progressBar.classList.remove('d-none');
        progressBarInner.style.width = '0%';

        try {
            if (uploadTypeSwitch.checked) {
                // Upload de diretório
                let totalFiles = 0;
                let processedFiles = 0;

                // Primeiro, conta o número total de arquivos (não diretórios)
                for (let i = 0; i < files.length; i++) {
                    if (!files[i].isDirectory) {
                        totalFiles++;
                    }
                }

                // Agora processa cada arquivo
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (file.isDirectory) continue; // Pula diretórios

                    // Atualiza o status do arquivo na lista
                    const listItem = filesList.children[i];
                    if (listItem) {
                        listItem.classList.add('list-group-item-primary');
                    }

                    // Obtém o caminho relativo do arquivo
                    const relativePath = file.webkitRelativePath || file.name;
                    const pathParts = relativePath.split('/');
                    
                    // Remove o nome do arquivo e o diretório raiz
                    pathParts.pop(); // Remove o nome do arquivo
                    pathParts.shift(); // Remove o diretório raiz
                    
                    // Constrói o caminho do diretório
                    const subDirPath = pathParts.join('/');
                    const targetPath = path ? `${path}/${subDirPath}` : subDirPath;

                    // Converte o arquivo para base64
                    const base64File = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result.split(',')[1]);
                        reader.onerror = reject;
                        reader.readAsDataURL(file);
                    });

                    const data = {
                        action: action,
                        path: targetPath,
                        file: base64File,
                        filename: file.name
                    };

                    const response = await fetch('/api/files/upload', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        throw new Error('Erro no upload');
                    }

                    // Atualiza o status do arquivo na lista
                    if (listItem) {
                        listItem.classList.remove('list-group-item-primary');
                        listItem.classList.add('list-group-item-success');
                    }

                    // Atualiza a barra de progresso
                    processedFiles++;
                    const progress = (processedFiles / totalFiles) * 100;
                    progressBarInner.style.width = progress + '%';
                }

                // Após upload de todos os arquivos do diretório
                progressBarInner.style.width = '100%';
                sessionStorage.setItem('message', JSON.stringify({ 
                    message: 'Diretório enviado com sucesso', 
                    success: true 
                }));
                
                // Fecha o modal e recarrega a página
                modal.hide();
                setTimeout(() => { window.location.reload(); }, 500);
            } else {
                // Upload de arquivo único
                const file = files[0];
                
                // Atualiza o status do arquivo na lista
                const listItem = filesList.children[0];
                if (listItem) {
                    listItem.classList.add('list-group-item-primary');
                }
                
                // Converte o arquivo para base64
                const base64File = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result.split(',')[1]);
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });

                const data = {
                    action: action,
                    path: path,
                    file: base64File,
                    filename: file.name
                };

                const response = await fetch('/api/files/upload', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }

                const responseData = await response.json();

                if (responseData.success) {
                    // Atualiza o status do arquivo na lista
                    if (listItem) {
                        listItem.classList.remove('list-group-item-primary');
                        listItem.classList.add('list-group-item-success');
                    }

                    progressBarInner.style.width = '100%';
                    sessionStorage.setItem('message', JSON.stringify({ message: responseData?.message, success: responseData?.success }));
                    
                    // Fecha o modal e recarrega a página
                    modal.hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showAlert(responseData.message || 'Erro ao fazer upload do arquivo', false);
                    progressBar.classList.add('d-none');
                    progressBarInner.style.width = '0%';
                    
                    // Atualiza o status do arquivo na lista
                    if (listItem) {
                        listItem.classList.remove('list-group-item-primary');
                        listItem.classList.add('list-group-item-danger');
                    }
                }
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('Erro ao fazer upload do arquivo', false);
            progressBar.classList.add('d-none');
            progressBarInner.style.width = '0%';
            
            // Atualiza o status de todos os arquivos na lista
            Array.from(filesList.children).forEach(item => {
                item.classList.remove('list-group-item-primary');
                item.classList.add('list-group-item-danger');
            });
        }
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
