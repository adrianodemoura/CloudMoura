<?php
    if (!isset($_SESSION['user'])) {
        header('Location: /login');
        exit;
    }

    $uriAdminContent = isset( $arrUri[2] ) ? $arrUri[2] : "conta";
    $arrPages = [
        "conta" => [ "icon" => "fas fa-user", "title" => "Conta" ],
        "configuracoes" => [ "icon" => "fas fa-gear", "title" => "Configurações" ],
        "files" => [ "icon" => "fas fa-file", "title" => "Arquivos" ],
        "usuarios" => [ "icon" => "fas fa-users", "title" => "Usuários" ],
        "sair" => [ "icon" => "fas fa-right-to-bracket", "title" => "Sair" ],
    ];

    // Verifica se a página existe, se não, redireciona para conta
    if (!isset($arrPages[$uriAdminContent])) {
        $uriAdminContent = "conta";
    }

    if ( $_SESSION['user']['roles'] !== 'admin' ) {
        unset( $arrPages['usuarios'] );
        unset( $arrPages['configuracoes'] );
    }
?>

<div class="d-flex justify-content-center align-items-center mt-5">
    <div class="w-100 bg-white rounded shadow p-4" style="max-width: 1200px;">
        <div class="d-flex justify-content-between align-items-center border-bottom mb-3 p-3 bg-light">
            <a href="/" class="me-3">
                <img src="/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 350px;">
            </a>
            <div class="text-end flex-grow-1">
                <i class="<?= $arrPages[$uriAdminContent]['icon']; ?> fa-1x text-primary mb-3"></i>
                <span><?= $arrPages[$uriAdminContent]['title']; ?></span>                
                &nbsp;|&nbsp;
                <span><?= $_SESSION['user']['email']; ?></span>
            </div>
        </div>

        <div class="d-flex">
            <div id="divMenu" class="w-25 bg-light p-1">
                <div class="d-flex flex-column">
                    <?php foreach ( $arrPages as $key => $value ) : ?>
                        <a href="/admin/<?= $key; ?>" class="btn <?= $key === $uriAdminContent ? 'btn-primary' : 'btn-outline-primary' ?> mb-1 d-flex align-items-center">
                            <i class="<?= $value['icon']; ?> fa-1x <?= $key === $uriAdminContent ? 'text-white' : 'text-secondary' ?> me-1"></i>
                            <span class="mb-0"><?= $value['title']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="divContent" class="w-75 bg-white p-1">
                <?php
                    $file = dirname(__DIR__) . "/pages/admin/{$uriAdminContent}.php";
                    if (file_exists($file)) {
                        require_once $file;
                    } else {
                        echo "<div class='alert alert-warning'>Página não encontrada.</div>";
                    }
                ?>
            </div>
        </div>

        
    </div>
</div>

<!-- Modal de Confirmação de Saída -->
<div class="modal fade" id="logoutModal" role="dialog" aria-labelledby="logoutModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirmar Saída</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja sair do sistema?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmLogout">Sim, Sair</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Configuração do modal de saída
    document.addEventListener('DOMContentLoaded', function() {
        const logoutLink = document.querySelector('a[href="/admin/sair"]');
        const logoutModal = document.getElementById('logoutModal');
        
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = new bootstrap.Modal(logoutModal);
                modal.show();
            });
        }

        document.getElementById('confirmLogout').addEventListener('click', function() {
            window.location.href = '/admin/sair';
        });

        // Remove o foco antes de fechar o modal
        logoutModal.addEventListener('hide.bs.modal', function () {
            document.activeElement.blur();
        });
    });
</script>