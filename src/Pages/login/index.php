<div class="container">
    <div class="row">
        <div class="rounded shadow shadow p-4 col-12 col-md-6 offset-md-3 mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="/" class="me-3">
                    <img src="/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 350px;">
                </a>
                <div id="divTitle" class="text-end flex-grow-1">
                    <i class="fas fa-right-to-bracket fa-1x text-primary mb-3"></i>
                    <span class="mb-0">Login</span>
                </div>
            </div>

            <form id="formLogin" method="post" action="/api/login">
                <div class="position-relative mb-3">
                    <i class="fas fa-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="email" name="email" id="email" class="form-control ps-5" placeholder="Seu e-mail" required autofocus>
                </div>
                <div class="position-relative mb-3">
                    <i class="fas fa-lock position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="password" name="password" id="password" class="form-control ps-5 pe-5" placeholder="Sua senha" required>
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                    <a href="/cadastrar" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-user-plus"></i> Cadastrar
                    </a>
                    <a href="/recuperar_senha" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-unlock-alt"></i> Recuperar Senha
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    document.getElementById('formLogin').addEventListener('submit', function(event) {
        event.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        showLoading( 'divTitle' );
        fetch('/api/login/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ email: email, password: password })
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
                window.location.href = '/';
            } else {
                if ( data.status === 402 ) {
                    showAlert('Usuário necessitando de ativação!');
                    document.location.href = '/ativar'
                }
                showAlert(data.message || 'Erro ao fazer login');
            }
            hideLoading();
        })
        .catch(error => {
            showAlert(error.message || 'Erro ao fazer login. Tente novamente.');
        });
    });
</script>
