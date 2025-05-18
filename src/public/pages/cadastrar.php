<div class="container">
    <div class="d-flex justify-content-center">
        <div class="w-100 bg-white rounded shadow p-4" style="max-width: 600px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="/" class="me-3">
                    <img src="/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 350px;">
                </a>
                <div class="text-end flex-grow-1">
                    <i class="fas fa-user-plus fa-1x text-secondary mb-3"></i>
                    <span class="mb-0">Cadastro</span>
                </div>
            </div>
            <form id="formRegister" method="post" action="/api/cadastrar">
                <div class="position-relative mb-3">
                    <i class="fas fa-user position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="name" id="name" class="form-control ps-5" placeholder="Seu nome" required autofocus>
                </div>
                <div class="position-relative mb-3">
                    <i class="fas fa-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="email" name="email" id="email" class="form-control ps-5" placeholder="Seu e-mail" required>
                </div>
                <div class="position-relative mb-3">
                    <i class="fas fa-lock position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control ps-5" placeholder="Sua senha" required>
                        <span class="input-group-text" style="cursor: pointer;" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="position-relative mb-3">
                    <i class="fas fa-phone position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="tel" name="phone" id="phone" class="form-control ps-5" placeholder="Seu celular (opcional)" data-inputmask="'mask': '(99) 99999-9999'">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-save"></i> Cadastrar
                    </button>
                    <a href="/login" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Inicializa a máscara do telefone
    Inputmask({
        mask: '(99) 99999-9999',
        clearIncomplete: true,
        showMaskOnHover: false
    }).mask(document.getElementById('phone'));

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

    document.getElementById('formRegister').addEventListener('submit', function(event) {
        event.preventDefault();
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const password = document.getElementById('password').value;

        fetch('/api/user/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, email: email, phone: phone, password: password })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log( data );
            if (data.success) {
                sessionStorage.setItem( 'message', JSON.stringify( { message: data?.message, success: data?.success } ) );
                window.location.reload();
            } else {
                showAlert(data.message || 'Erro ao fazer cadastro');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert(error.message || 'Erro ao tentar cadastrar. Tente novamente.');
        });
    });
</script>
