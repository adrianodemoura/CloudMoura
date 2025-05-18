<div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="w-100 bg-white rounded shadow shadow p-4" style="max-width: 600px;">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">
                    <i class="fas fa-lock"></i> Desbloquear Site
                </h2>
                <form id="formDesbloquear" action="/desbloquear" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha de Acesso</label>
                        <div class="input-group">
                            <input type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    required 
                                    autocomplete="off"
                                    placeholder="Digite a senha">
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    id="toggleSenha">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-unlock me-2"></i>Desbloquear
                        </button>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Voltar para página inicial
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

    document.getElementById('toggleSenha').addEventListener('click', function() {
        const senhaInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            senhaInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    document.getElementById('formDesbloquear').addEventListener('submit', function(event) {
        event.preventDefault();
        const password = document.getElementById('password').value;

        fetch('/api/user/desbloquear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: password })
        })
        .then(response => {
            if ( !response.ok ) {
                return response.json().then(data => {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                } );
            }
            return response.json();
        })
        .then(data => {
            console.log( data );
            if (data.success) {
                sessionStorage.setItem( 'message', JSON.stringify( { message: data?.message, success: data?.success } ) );
                window.location.reload();
            } else {
                showAlert(data.message || 'Erro ao tentar desbloquear o site!');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert(error.message || 'Erro ao tentar desbloquear o site!');
        });
    } );
</script>
