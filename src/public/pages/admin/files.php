<?php
    $userId = $_SESSION['user']['id'];
    $Files = new \CloudMoura\Includes\Files();
?>
<link rel="stylesheet" href="/css/drag-drop.css">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-film"></i> Meus Arquivos
        </h5>
    </div>
    <div class="card-body">
        <div class="overflow-auto" style="max-height: 600px;">
            <?= $Files->listDirectoryTree( DIR_UPLOAD . "/{$userId}" ); ?>
        </div>
    </div>
</div>

<!-- Modal de Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload de Arquivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm">
                    <div class="mb-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="uploadTypeSwitch">
                            <label class="form-check-label" for="uploadTypeSwitch">Upload de Diretório</label>
                        </div>
                        <label for="fileInput" class="form-label">Arquivo</label>
                        <input type="file" class="form-control" id="fileInput" name="file" required accept="<?= implode( ',', ALLOWED_EXTENSIONS ); ?>">
                        <div class="form-text text-uppercase" id="uploadHelpText">
                            Formatos aceitos: <?= implode( ', ', ALLOWED_EXTENSIONS ); ?>
                        </div>
                    </div>

                    <div class="progress mb-3 d-none">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="uploadButton">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="modalBody">
                Tem certeza que deseja excluir este item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Sim, Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Criação de Subdiretório -->
<div class="modal fade" id="createSubDirModal" tabindex="-1" aria-labelledby="createSubDirModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Criar Subdiretório</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="createDirName" placeholder="Nome do Subdiretório" autofocus required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmCreate">Criar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPath = '<?= isset($_GET['path']) ? $_GET['path'] : '' ?>';
    const allowedExtensions = <?= json_encode(ALLOWED_EXTENSIONS) ?>;
</script>
<script src="/js/files.js"></script>
<script src="/js/drag-drop.js"></script>