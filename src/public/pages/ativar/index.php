<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="text-center">Ativar Conta</h2>
            <form id="formActivate" method="POST" action="/api/user/update">
                <div class="mb-3">
                    <label for="code" class="form-label">Código de Ativação</label>
                    <input type="text" class="form-control text-center" id="code" name="code" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ativar Conta</button>
                <div class="mt-3 text-center">
                    <a href="/login" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-user-plus"></i> Voltar para Login
                    </a>
                </div>
            </form>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="col-md-6 text-center">
            <p>Não recebeu o código? <a href="/resend-code">Reenviar Código</a></p>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    const codeActivation = document.getElementById('code');
    const email = sessionStorage.getItem('user') ? JSON.parse(sessionStorage.getItem('user')).email : '';


    document.getElementById('formActivate').addEventListener('submit', function(event) {
        event.preventDefault();
        const code = document.getElementById('code').value;

        fetch('/api/user/activateByCode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ email: email, code: code })
        })
        .then(async response => {
            const data = await response.json();
            data.status = response.status; // Adiciona o status ao objeto data
            return data;
        })
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('user', JSON.stringify(data.user));
                sessionStorage.setItem('message', JSON.stringify({ message: data?.message, success: data?.success }));
                window.location.href = '/login';
            } else {
                showAlert(data.message || 'Erro ao fazer login');
            }
        })
        .catch(error => {
            showAlert(error.message || 'Erro ao fazer login. Tente novamente.');
        });
    });
</script>
