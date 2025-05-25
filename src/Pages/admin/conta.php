<?php
try {
    $Db = new \CloudMoura\Includes\Db();
    $res = $Db->query("SELECT * FROM users WHERE email = :email", ['email' => $_SESSION['user']['email']]);
    
    if (empty($res)) {
        throw new Exception('Usuário não encontrado.');
    }

    $user = $res[0];
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: /admin');
    exit;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-user"></i> Conta
        </h5>
    </div>
    <div class="card-body">
        <form id="formAccount" method="post" action="/api/user/update">
            <div class="position-relative mb-3">
                <i class="fas fa-user position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="name" id="name" class="form-control ps-5" placeholder="Seu nome" value="<?= sanitizeInput($user['name']) ?>" required>
            </div>
            <div class="position-relative mb-3">
                <i class="fas fa-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="email" name="email" id="email" class="form-control ps-5" placeholder="Seu e-mail" value="<?= sanitizeInput($user['email']) ?>" required>
            </div>
            <div class="position-relative mb-3">
                <i class="fas fa-lock position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="password" name="password" id="password" class="form-control ps-5 pe-5" placeholder="Nova senha (deixe em branco para manter a atual)">
                <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <div class="position-relative mb-3">
                <i class="fas fa-phone position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="tel" name="phone" id="phone" class="form-control ps-5" placeholder="Seu celular (opcional)" value="<?= sanitizeInput($user['phone']) ?>" data-inputmask="'mask': '(99) 99999-9999'">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
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

    document.getElementById('formAccount').addEventListener('submit', function(event) {
        event.preventDefault();
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const phone = document.getElementById('phone').value;

        fetch('/api/user/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ name, email, password, phone })
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
            if (data.success) {
                sessionStorage.setItem('message', JSON.stringify({ message: data?.message, success: data?.success }));
                window.location.reload();
            } else {
                showAlert(data.message || 'Erro ao atualizar dados');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert(error.message || 'Erro ao tentar atualizar dados. Tente novamente.');
        });
    });

    // Inicializa a máscara do telefone quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Inputmask !== 'undefined') {
            Inputmask({
                mask: '(99) 99999-9999',
                clearIncomplete: true,
                showMaskOnHover: false
            }).mask(document.getElementById('phone'));
        } else {
            console.error('Inputmask não está carregado');
        }
    });
</script>