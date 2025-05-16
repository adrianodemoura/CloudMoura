<?php
    if ( file_exists( DIR_ROOT . '/config.json' ) ) {
        $config = json_decode( file_get_contents( DIR_ROOT . '/config.json' ), true );
    }
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-cogs"></i> Configurações
        </h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="w-25">Configuração</th>
                    <th class="w-25">Valor</th>
                    <th class="w-50">Descrição</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <i class="fas fa-bug"></i>
                        DEBUG
                    </td>
                    <td>
                        <a href="#" onclick="toggleDebug('<?= $_SESSION['user']['email'] ?>')">
                            <i class="fas fa-yes text-primary">
                                <?= DEBUG ? 'Ativo' : 'Desativado' ?>
                            </i>
                        </a>
                    </td>
                    <td>
                        Aqui você pode ativar ou desativar o modo de depuração.
                    </td>
                </tr>

                <tr>
                    <td>
                        <i class="fas fa-lock"></i>
                        BLOQUEIO
                    </td>
                    <td>
                        <a href="#" onclick="toggleBlockSite('<?= $_SESSION['user']['email'] ?>')">
                            <i class="fas fa-yes text-primary">
                                <?= $config['block'] ? 'Bloqueado' : 'Desbloqueado' ?>
                            </i>
                        </a>
                    </td>
                    <td>
                        Aqui você pode bloquear ou desbloquear o acesso ao sistema. Útil para manutenções.<br /><br />Quando bloqueado apenas os usuparios com perfil <strong>"admin"</strong> podem acessar o site.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleDebug( email ) {
        fetch('/api/configurations/toggledebug', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify( { email: email } )
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log( data );
                sessionStorage.setItem('message', JSON.stringify({ message: data?.message, success: data?.success }));
                window.location.reload();
            } else {
                showAlert(data.message, false);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro ao excluir usuário', false);
        });
    }

    function toggleBlockSite( email ) {
        fetch('/api/configurations/toggleblocksite', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify( { email: email } )
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log( data );
                sessionStorage.setItem( 'message', JSON.stringify( { message: data?.message, success: data?.success } ) );
                window.location.reload();
            } else {
                showAlert(data.message, false);
            }
        })
    }
</script>