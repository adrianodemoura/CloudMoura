<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users"></i> Usuários
        </h5>
    </div>
    <div class="card-body"> 
        <div id="usersTable" class="table-responsive" style="overflow-x: auto; min-height: 300px;">
            <table id="usersList" class="table table-bordered table-hover">
                <thead class="sticky-top bg-white">
                    <tr>
                        <th style="min-width: 100px; white-space: nowrap;" class="text-center">Ações</th>
                        <th style="min-width: 200px; white-space: nowrap;">Nome</th>
                        <th style="min-width: 200px; white-space: nowrap;">Email</th>
                        <th style="min-width: 150px; white-space: nowrap;">Telefone</th>
                        <th style="min-width: 60px; white-space: nowrap;">Ativo</th>
                        <th style="min-width: 60px; white-space: nowrap;">Perfil</th>
                        <th style="min-width: 150px; white-space: nowrap;">Data de criação</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Os usuários serão carregados aqui via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <nav aria-label="Navegação de páginas" class="mt-4">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Os botões de paginação serão inseridos aqui via JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir o usuário <strong id="userName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Sim, Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
    let userEmailToDelete = null;
    let currentPage = 1;
    const itemsPerPage = 10;

    // Função para carregar os usuários
    function loadUsers(page = 1) {
        currentPage = page;
        fetch(`/api/user/getList`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ page: page, limit: itemsPerPage })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("A resposta não é um JSON válido!");
            }
            return response.json();
        })
        .then(data => {
            const dataResponse = data.data;
            if (data.success === false) {
                throw new Error(data.message || 'Erro ao carregar usuários!');
            }
            if (!Array.isArray(dataResponse.users)) {
                throw new Error('Formato de dados inválido: lista de usuários não encontrada');
            }
            renderUsers(dataResponse.users);
            renderPagination(dataResponse.total, dataResponse.current_page, dataResponse.last_page);
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert(error.message || 'Erro ao carregar usuários. Por favor, tente novamente mais tarde.', false);
            // Limpa a tabela em caso de erro
            document.getElementById('usersList').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Não foi possível carregar os usuários
                    </td>
                </tr>
            `;
            // Limpa a paginação
            document.getElementById('pagination').innerHTML = '';
        });
    }

    // Função para renderizar os usuários na tabela
    function renderUsers(users) {
        const tbody = document.getElementById('usersList').getElementsByTagName('tbody')[0];
        tbody.innerHTML = '';

        if (!Array.isArray(users) || users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted" style="white-space: nowrap;">
                        <i class="fas fa-info-circle me-2"></i>
                        Nenhum usuário encontrado
                    </td>
                </tr>
            `;
            return;
        }

        users.forEach(user => {
            tbody.innerHTML += `
                <tr style="white-space: nowrap;">
                    <td class="text-center" style="padding: 0.25rem 0.5rem;">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="activateUser('${user.email}')">
                                    <i class="fas fa-edit text-primary"></i> Ativar/Desativar
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="confirmDelete('${user.email}', '${user.name}')">
                                    <i class="fas fa-trash text-danger"></i> Excluir
                                </a></li>
                            </ul>
                        </div>
                    </td>
                    <td style="padding: 0.25rem 0.5rem;">${user.name}</td>
                    <td style="padding: 0.25rem 0.5rem;">${user.email}</td>
                    <td style="padding: 0.25rem 0.5rem;">${user.phone}</td>
                    <td class="text-center" style="padding: 0.25rem 0.5rem;">${user.active ? 'Sim' : 'Não'}</td>
                    <td class="text-center" style="padding: 0.25rem 0.5rem;">${user.role}</td>
                    <td class="text-center" style="padding: 0.25rem 0.5rem;">${new Date(user.created_at).toLocaleString('pt-BR')}</td>
                </tr>
            `;
        });
    }

    // Função para renderizar a paginação
    function renderPagination(total, currentPage, lastPage) {
        const pagination = document.getElementById('pagination');
        if (!pagination) {
            console.error('Elemento de paginação não encontrado!');
            return;
        }
        pagination.innerHTML = '';

        // Primeira página
        pagination.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(1)" aria-label="Primeira">
                    <i class="fas fa-angle-double-left"></i>
                </a>
            </li>
        `;

        // Página anterior
        pagination.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${currentPage - 1})" aria-label="Anterior">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>
        `;

        // Página atual
        pagination.innerHTML += `
            <li class="page-item active">
                <span class="page-link">Página ${currentPage} de ${lastPage}</span>
            </li>
        `;

        // Próxima página
        pagination.innerHTML += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${currentPage + 1})" aria-label="Próxima">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>
        `;

        // Última página
        pagination.innerHTML += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${lastPage})" aria-label="Última">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            </li>
        `;

        // Informações de paginação (agora no final)
        pagination.innerHTML += `
            <li class="page-item disabled">
                <span class="page-link">
                    <small>Total: ${total} registros</small>
                </span>
            </li>
        `;
    }

    function activateUser(email) {
        if (email) {
            fetch('/api/user/activate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, data.success);
                    loadUsers(currentPage);
                } else {
                    showAlert(data.message || 'Erro ao tentar ativar/desativar usuário!');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('Erro ao tentar ativar/desativar usuário!', false);
            });
        }
    }

    function confirmDelete(email, name) {
        userEmailToDelete = email;
        document.getElementById('userName').textContent = name;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (userEmailToDelete) {
            fetch('/api/user/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: userEmailToDelete })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, data.success);
                    loadUsers(currentPage);
                } else {
                    showAlert(data.message || 'Erro ao tentar excluir usuário!');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showAlert('Erro ao tentar excluir usuário!', false);
            });

            // Fecha o modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            deleteModal.hide();
        }
    });

    // Carrega os usuários quando a página é carregada
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers(1);
    });
</script>