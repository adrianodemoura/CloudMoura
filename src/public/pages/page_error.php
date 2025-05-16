<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h2 class="text-danger mb-4">
                        <i class="fas fa-exclamation-triangle"></i> Erro
                    </h2>
                    <p class="mb-4">Página <span class="text-danger fw-bold">"<?= $pageError; ?>"</span> não encontrada</p>
                    <p class="text-muted mb-4">Verifique se o endereço está correto ou tente voltar para a página inicial.</p>
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Voltar para a página inicial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>