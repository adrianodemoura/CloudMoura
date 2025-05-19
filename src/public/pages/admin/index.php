<?php
    $uriAdminContent = isset( $arrUri[2] ) ? $arrUri[2] : "conta";
?>

<div class="container">
    <div class="w-100 rounded shadow p-4">
        <div class="d-flex rounded-top border-bottom mb-3 p-3" style="background-image: url('/img/bg-header-001.png'); background-repeat: no-repeat; background-position: top; background-size: cover;">
            <div class="d-flex">
                <a href="/" class="me-3">
                    <img src="/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 350px;">
                </a>
            </div>
            <div class="d-flex align-items-center justify-content-end text-end flex-grow-1 text-muted p-3">
                <span title="<?= $_SESSION['user']['name']; ?>" onclick="window.location.href = '/admin/conta';" class="cursor-pointer">
                    <i class="fas fa-user me-1"></i>
                    <?= $_SESSION['user']['email']; ?>
                </span>
                &nbsp;|&nbsp;
                <span title="Último login">
                    <i class="fas fa-clock"></i>
                    <?= date('d/m/Y H:i', strtotime($_SESSION['user']['last_login'])); ?>
                </span>
            </div>
        </div>

        <div class="d-flex">
            <div id="divMenu" class="w-25 bg-light p-1">
                <div class="d-flex flex-column">
                    <a href="/admin/conta" class=" mb-1 d-flex align-items-center btn 
                    <?php echo $uriAdminContent === 'conta' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-user me-1"></i>
                        <span class="mb-0">Conta</span>
                    </a>

                    <?php if ( $_SESSION['user']['role'] === 'admin' ) : ?>
                        <a href="/admin/configuracoes" class="mb-1 d-flex align-items-center btn 
                            <?php echo $uriAdminContent === 'configuracoes' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-gear me-1"></i>
                            <span class="mb-0">Configurações</span>
                        </a>
                    <?php endif; ?>

                    <a href="/admin/files" class="mb-1 d-flex align-items-center btn 
                        <?php echo $uriAdminContent === 'files' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-file me-1"></i>
                        <span class="mb-0">Arquivos</span>
                    </a>

                    <?php if ( $_SESSION['user']['role'] === 'admin' ) : ?>
                        <a href="/admin/usuarios" class="mb-1 d-flex align-items-center btn 
                            <?php echo $uriAdminContent === 'usuarios' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-users me-1"></i>
                            <span class="mb-0">Usuários</span>
                        </a>
                    <?php endif; ?>

                    <a href="#" onclick="showModalAdmin('/admin/sair'); return false;" class="mb-1 d-flex align-items-center btn 
                        <?php echo $uriAdminContent === 'sair' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-right-to-bracket me-1"></i>
                        <span class="mb-0">Sair</span>
                    </a>

                </div>
            </div>
            <div id="divContent" class="w-75 bg-white p-1">
                <?php
                    $file = dirname( __DIR__ ) . "/admin/{$uriAdminContent}.php";
                    if  ( file_exists( $file ) ) {
                        require_once $file;
                    } else {
                        echo "<div class='alert alert-warning'>Página não encontrada</div>";
                    }
                ?>
            </div>
        </div>

    </div>
</div>

<!-- Modal de Confirmação de Saída -->
<div id="divModalAdmin" class="modal fade" role="dialog" aria-labelledby="logoutModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="divModalAdminHeader" class="modal-header bg-primary text-white">
                <h5 class="modal-title w-100 text-center" id="logoutModalLabel">Confirmar Saída</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div id="divModalAdminBody" class="modal-body bg-info-50 text-white">
                Você tem certeza que deseja sair?
            </div>
            <div id="divModalAdminFooter" class="modal-footer">
                <button id="btnAdminCanel" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnAdminConfirm" type="button" class="btn btn-primary">Sim</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const adminModal = new bootstrap.Modal(document.getElementById('divModalAdmin'), { backdrop: 'static', keyboard: false });
        window.adminModal = adminModal;
    });

    function showModalAdmin(target, data = {} ) {
        const modalBody = document.getElementById('divModalAdminBody');

        switch (target) {
            case '/admin/sair':
                modalBody.textContent = 'Você tem certeza que deseja sair?';
                document.getElementById('btnAdminConfirm').onclick = function() {
                    window.location.href = target;
                }
                break;
            case '/api/configurations/toggleblocksite':
                modalBody.textContent = 'Você tem certeza que deseja Bloquear/Desbloquear o site?';
                document.getElementById('btnAdminConfirm').onclick = function() {
                    getAjaxAdmin( target, data );
                }
                break;
            case '/api/configurations/toggledebug':
                modalBody.textContent = 'Você tem certeza que deseja Ativar/Desativar o modo de depuração?';
                document.getElementById('btnAdminConfirm').onclick = function() {
                    getAjaxAdmin( target, data );
                }
                break;
            default:
                modalBody.textContent = 'Você tem certeza de algo?';
        }

        adminModal.show();
        return false;
    }

    function getAjaxAdmin( url, data = {} ) {
        return fetch( url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify( data )
        } )
        .then( response => response.json() )
        .then(data => {
            if ( data.success ) {
                sessionStorage.setItem( 'message', JSON.stringify( { message: data?.message, success: data?.success } ) );
                window.location.reload();
            } else {
                adminModal.hide();
                showAlert(data.message, false);
            }
        })
    }
</script>