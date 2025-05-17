document.addEventListener('DOMContentLoaded', function() {
    // Elementos arrastáveis (arquivos e diretórios não estáticos)
    const draggableItems = document.querySelectorAll('.draggable-file, .draggable-dir');
    
    // Elementos que podem receber o drop (todos os diretórios)
    const droppableDirs = document.querySelectorAll('.droppable-dir');
    
    // Adiciona eventos de drag para os itens
    draggableItems.forEach(item => {
        item.setAttribute('draggable', 'true');
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
    });

    // Adiciona eventos de drop para os diretórios
    droppableDirs.forEach(dir => {
        dir.addEventListener('dragover', handleDragOver);
        dir.addEventListener('dragenter', handleDragEnter);
        dir.addEventListener('dragleave', handleDragLeave);
        dir.addEventListener('drop', handleDrop);
    });

    // Funções de manipulação do drag and drop
    function handleDragStart(e) {
        e.target.classList.add('dragging');
        const itemType = e.target.classList.contains('draggable-dir') ? 'dir' : 'file';
        e.dataTransfer.setData('text/plain', JSON.stringify({
            path: e.target.dataset.filePath || e.target.dataset.dirPath,
            type: itemType
        }));
        e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragEnd(e) {
        e.target.classList.remove('dragging');
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDragEnter(e) {
        e.preventDefault();
        e.target.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        e.target.classList.remove('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.target.classList.remove('drag-over');

        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        const targetDir = e.target.dataset.dirPath;

        // Não permitir mover um diretório para dentro dele mesmo
        if (data.type === 'dir' && data.path === targetDir) {
            showAlert('Não é possível mover um diretório para dentro dele mesmo', 'warning');
            return;
        }

        // Não permitir mover um diretório para dentro de um de seus subdiretórios
        if (data.type === 'dir' && targetDir.startsWith(data.path)) {
            showAlert('Não é possível mover um diretório para dentro de um de seus subdiretórios', 'warning');
            return;
        }

        // Chama a função de mover item
        moveItem(data.path, targetDir, data.type);
    }

    // Função para mover o item via API
    function moveItem(path, targetDir, type) {
        fetch('/api/files/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                path: path,
                targetDir: targetDir,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Atualiza a lista de arquivos
                location.reload();
            } else {
                showAlert('Erro ao mover o item: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro ao mover o item', 'danger');
        });
    }
}); 