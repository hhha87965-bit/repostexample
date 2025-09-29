<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список Excel файлов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .file-card {
            transition: transform 0.2s ease-in-out;
        }
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .file-icon {
            font-size: 2rem;
        }
        .action-btn {
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-file-excel text-success"></i> Загруженные Excel файлы</h2>
                    <div>
                        <a href="<?= base_url('excelupload') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Загрузить файл
                        </a>
                        <a href="<?= base_url('excelupload/logs') ?>" class="btn btn-info">
                            <i class="fas fa-history"></i> Журнал действий
                        </a>
                    </div>
                </div>

                <?php if (empty($files)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h4>Файлы не найдены</h4>
                        <p>Загрузите первый Excel файл, чтобы начать работу.</p>
                        <a href="<?= base_url('excelupload') ?>" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Загрузить файл
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($files as $file): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card file-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <i class="fas fa-file-excel file-icon text-success me-3"></i>
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1" title="<?= esc($file['name']) ?>">
                                                    <?= esc(strlen($file['name']) > 30 ? substr($file['name'], 0, 30) . '...' : $file['name']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> <?= date('d.m.Y H:i', strtotime($file['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="row text-center mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Строк</small>
                                                <div class="fw-bold"><?= $file['row_count'] ?></div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Изменен</small>
                                                <div class="fw-bold"><?= date('d.m.Y', strtotime($file['updated_at'])) ?></div>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('excelupload/view/' . $file['id']) ?>" 
                                                   class="btn btn-outline-primary btn-sm action-btn" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('excelupload/download/' . $file['id']) ?>" 
                                                   class="btn btn-outline-success btn-sm action-btn" title="Скачать">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-info btn-sm action-btn" 
                                                        onclick="showExportModal(<?= $file['id'] ?>, '<?= esc($file['name']) ?>')" title="Экспорт">
                                                    <i class="fas fa-file-export"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm action-btn" 
                                                        onclick="confirmDelete(<?= $file['id'] ?>, '<?= esc($file['name']) ?>')" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center mt-4">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить файл <strong id="deleteFileName"></strong>?</p>
                    <p class="text-danger"><small>Это действие нельзя отменить. Все данные файла будут удалены.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash"></i> Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно экспорта -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-export text-info"></i> Экспорт файла</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Выберите формат для экспорта файла <strong id="exportFileName"></strong>:</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-success" id="exportExcelBtn">
                            <i class="fas fa-file-excel"></i> Экспорт в Excel (.xlsx)
                        </a>
                        <a href="#" class="btn btn-danger" id="exportPdfBtn">
                            <i class="fas fa-file-pdf"></i> Экспорт в PDF
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast для уведомлений -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong class="me-auto">Уведомление</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
        const toast = new bootstrap.Toast(document.getElementById('toast'));
        const toastMessage = document.getElementById('toastMessage');

        let currentFileId = null;

        function confirmDelete(fileId, fileName) {
            currentFileId = fileId;
            document.getElementById('deleteFileName').textContent = fileName;
            deleteModal.show();
        }

        function showExportModal(fileId, fileName) {
            currentFileId = fileId;
            document.getElementById('exportFileName').textContent = fileName;
            
            const excelBtn = document.getElementById('exportExcelBtn');
            const pdfBtn = document.getElementById('exportPdfBtn');
            
            excelBtn.href = '<?= base_url('excelupload/export-excel/') ?>' + fileId;
            pdfBtn.href = '<?= base_url('excelupload/export-pdf/') ?>' + fileId;
            
            exportModal.show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (currentFileId) {
                fetch('<?= base_url('excelupload/delete/') ?>' + currentFileId)
                    .then(response => response.json())
                    .then(data => {
                        deleteModal.hide();
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        deleteModal.hide();
                        showToast('Ошибка при удалении файла', 'error');
                        console.error('Error:', error);
                    });
            }
        });

        function showToast(message, type = 'info') {
            toastMessage.textContent = message;
            const toastElement = document.getElementById('toast');
            
            toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-info');
            
            if (type === 'success') {
                toastElement.classList.add('text-bg-success');
            } else if (type === 'error') {
                toastElement.classList.add('text-bg-danger');
            } else {
                toastElement.classList.add('text-bg-info');
            }
            
            toast.show();
        }
    </script>
</body>
</html>
