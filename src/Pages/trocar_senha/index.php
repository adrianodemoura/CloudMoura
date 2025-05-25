<?php
    require_once DIR_ROOT . '/Pages/helper/View.php';
    $View = new View();
?>

<div class="container">
    <div class="row rounded shadow bg-white p-4 w-50 mx-auto">
        <?=  $View->getHeaderSite( 'fa-key', 'Senha' ); ?>

        <div class="row p-3">
            <h1 class="text-center">Resetar Senha</h1>
            <form id="formResetarSenha" method="POST" action="/api/user/resetPassword">
                <div class="mb-3">
                    <input 
                        type="text" 
                        class="form-control text-center" 
                        id="code" 
                        name="code" 
                        value="<?= !empty( $arrUri[2] ) ? $arrUri[2] : ''; ?>"
                        placeholder="Código de recuperação"
                        readonly
                        required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" autofocus required>
                </div>
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha</label>
                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Enviar</button>
                    <a href="/login" class="btn btn-secondary flex-grow-1"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('formResetarSenha').addEventListener('submit', function(event) {
        event.preventDefault();
        const email = document.getElementById('email').value;
        const novaSenha = document.getElementById('nova_senha').value;
        const code = document.getElementById('code').value;

        showLoading('divTitle');
        fetch('/api/user/resetPassword', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ email: email, password: novaSenha, code: code })
        })
        .then( response => response.json() )
        .then(data => {
            sessionStorage.setItem( 'message', JSON.stringify( { message: data?.message, success: data?.success } ) );
            if (data.success) {
                window.location.href = '/login';
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            showAlert(error.message || 'Erro ao redefinir senha. Tente novamente.');
        });
    });
</script>
