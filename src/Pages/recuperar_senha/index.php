<div class="container">
    <div class="row rounded shadow bg-white p-4 w-50 mx-auto">
        <?=  $View->getHeaderSite( 'fa-key', 'Senha' ); ?>
        <div class="row p-3">
            <h2 class="text-center">Recuperar Senha</h2>
            <p class="p-3 bg-yellow-50 rounded-3 small">
                Informe seu e-mail para receber o link de recuperação de senha.
                <br>
                Caso não tenha recebido o e-mail, verifique a caixa de spam ou lixo eletrônico.
                <br>
                Se o problema persistir, entre em contato com o suporte.
            </p>
            <form id="formRecuperarSenha" class="form" method="POST" action="/api/recuperar_senha">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" autofocus required>
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
    document.getElementById('formRecuperarSenha').addEventListener('submit', function(event) {
        event.preventDefault();
        const email = document.getElementById('email').value;

        showLoading('divTitle');
        fetch('/api/user/recoverPassword', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            sessionStorage.setItem('message', JSON.stringify({ message: data?.message, success: data?.success } ) );
            if (data.success) {
                window.location.href = '/login';
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            showAlert(error.message || 'Erro ao enviar e-mail de recuperação. Tente novamente.');
        });
    });
</script>