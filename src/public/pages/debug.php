<?php 
    if ( !DEBUG || in_array($uriContent, ['desbloquear', 'page_blocked']) ) return; 
?>

<script>
    function toggle(id) {
        document.getElementById(id).classList.toggle('hidden');
    }
</script>

<?php
    $arrDebug = [
        "SESSION" => $_SESSION,
        "POST" => $_POST,
        // "GET" => $_GET,
        // "FILES" => $_FILES,
        // "COOKIE" => $_COOKIE,
        "REQUEST" => $_REQUEST,
        "ENV" => $_ENV,
        "SERVER" => $_SERVER,
        "GLOBALS" => $GLOBALS,
    ];

    // Função para formatar arrays/objetos
    function formatDebugValue($value, $level = 0) {
        if (is_array($value) || is_object($value)) {
            $output = '<div class="ms-' . ($level * 2) . ' small">';
            foreach ((array)$value as $k => $v) {
                $output .= '<div class="mb-1">';
                $output .= '<strong class="text-warning">' . htmlspecialchars($k) . ':</strong> ';
                if (is_array($v) || is_object($v)) {
                    $output .= formatDebugValue($v, $level + 1);
                } else {
                    $output .= '<span class="text-light">' . htmlspecialchars($v) . '</span>';
                }
                $output .= '</div>';
            }
            $output .= '</div>';
            return $output;
        }
        return htmlspecialchars($value);
    }
?>

<div class="container-fluid bg-dark text-light p-3 mt-3 small">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <i class="fas fa-bug me-2"></i>Debug
        </h6>
        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#debugContent">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>

    <div class="collapse" id="debugContent">
        <div class="row g-3">
            <?php foreach ($arrDebug as $key => $value) : ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card bg-secondary text-light h-100">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="mb-0 small">
                                <i class="fas fa-database me-2"></i><?= $key ?>
                            </h6>
                            <span class="badge bg-primary small">
                                <?= count($value) ?> items
                            </span>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-dark table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="small">Key</th>
                                            <th class="small">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($value as $k => $v) : ?>
                                            <tr>
                                                <td class="text-warning small"><?= $k ?></td>
                                                <td class="small">
                                                    <?php if (is_array($v) || is_object($v)) : ?>
                                                        <?php if (!empty($v)) : ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-info py-0 px-2" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#debugModal<?= md5($key . $k) ?>">
                                                                <i class="fas fa-layer-group me-1"></i>
                                                                <?= is_array($v) ? 'Array' : 'Object' ?>
                                                                (<?= count((array)$v) ?>)
                                                            </button>

                                                            <!-- Modal para o array/objeto -->
                                                            <div class="modal fade" id="debugModal<?= md5($key . $k) ?>" tabindex="-1">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content bg-dark text-light">
                                                                        <div class="modal-header py-2">
                                                                            <h6 class="modal-title mb-0">
                                                                                <i class="fas fa-database me-2"></i>
                                                                                <?= $key ?> > <?= $k ?>
                                                                            </h6>
                                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <div class="modal-body small">
                                                                            <?= formatDebugValue($v) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php else : ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-layer-group me-1"></i>
                                                                <?= is_array($v) ? 'Array' : 'Object' ?> vazio
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else : ?>
                                                        <?= htmlspecialchars($v) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
