<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр файла: <?= esc($file['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .editable-cell {
            border: none;
            background: transparent;
            width: 100%;
            padding: 0.375rem;
        }
        .editable-cell:focus {
            background: #fff;
            border: 1px solid #007bff;
            border-radius: 3px;
        }
        .row-actions {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-file-excel text-success"></i> <?= esc($file['name']) ?></h2>
                        <small class="text-muted">
                            Загружен: <?= date('d.m.Y H:i', strtotime($file['created_at'])) ?> | 
                            Строк: <?= $file['row_count'] ?>
                        </small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRowModal">
                            <i class="fas fa-plus"></i> Добавить строку
                        </button>
                        <a href="<?= base_url('excelupload/files') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад к списку
                        </a>
                    </div>
                </div>

                <?php if (empty($rows)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h4>Данные не найдены</h4>
                        <p>В файле нет данных для отображения.</p>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRowModal">
                            <i class="fas fa-plus"></i> Добавить первую строку
                        </button>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-table"></i> Содержимое файла</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-dark sticky-top">
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <?php 
                                            $firstRow = json_decode($rows[0]['row_data'], true);
                                            for ($i = 0; $i < count($firstRow); $i++): 
                                            ?>
                                                <th>Колонка <?= $i + 1 ?></th>
                                            <?php endfor; ?>
                                            <th style="width: 120px;">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $index => $row): ?>
                                            <tr id="row-<?= $row['id'] ?>">
                                                <td><?= $index + 1 ?></td>
                                                <?php 
                                                $cells = json_decode($row['row_data'], true);
                                                foreach ($cells as $cellIndex => $cell): 
                                                ?>
                                                    <td>
                                                        <input type="text" 
                                                               class="editable-cell" 
                                                               value="<?= esc($cell) ?>" 
                                                               data-row-id="<?= $row['id'] ?>"
                                                               data-cell-index="<?= $cellIndex ?>"
                                                               onblur="saveCell(this)">
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="row-actions">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteRow(<?= $row['id'] ?>)"
                                                            title="Удалить строку">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center mt-4">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно добавления строки -->
    <div class="modal fade" id="addRowModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus text-success"></i> Добавить новую строку</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addRowForm">
                    <div class="modal-body">
                        <div id="newRowInputs">
                            <!-- Динамически добавляемые поля -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addNewColumn()">
                            <i class="fas fa-plus"></i> Добавить колонку
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Сохранить строку
                        </button>
                    </div>
                </form>
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
        const toast = new bootstrap.Toast(document.getElementById('toast'));
        const toastMessage = document.getElementById('toastMessage');
        const addRowModal = new bootstrap.Modal(document.getElementById('addRowModal'));
        const fileId = <?= $file['id'] ?>;

        function initNewRowInputs() {
            const container = document.getElementById('newRowInputs');
            container.innerHTML = '';

            for (let i = 0; i < 3; i++) {
                addNewColumn();
            }
        }

        function addNewColumn() {
            const container = document.getElementById('newRowInputs');
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group mb-2';
            inputGroup.innerHTML = `
                <span class="input-group-text">Колонка ${container.children.length + 1}</span>
                <input type="text" class="form-control" name="new_row[]" placeholder="Введите значение">
                <button type="button" class="btn btn-outline-danger" onclick="removeColumn(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(inputGroup);
        }

        function removeColumn(button) {
            const container = document.getElementById('newRowInputs');
            if (container.children.length > 1) {
                button.closest('.input-group').remove();
            }
        }

        function saveCell(input) {
            const rowId = input.dataset.rowId;
            const cellIndex = input.dataset.cellIndex;
            const value = input.value;
            const row = document.getElementById('row-' + rowId);
            const cells = row.querySelectorAll('.editable-cell');
            const rowData = Array.from(cells).map(cell => cell.value);

            fetch('<?= base_url('excelupload/editrow/') ?>' + rowId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'row_data=' + encodeURIComponent(JSON.stringify(rowData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Ячейка сохранена', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Ошибка при сохранении', 'error');
                console.error('Error:', error);
            });
        }

        function deleteRow(rowId) {
            if (confirm('Вы уверены, что хотите удалить эту строку?')) {
                fetch('<?= base_url('excelupload/deleterow/') ?>' + rowId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('row-' + rowId).remove();
                            showToast('Строка удалена', 'success');
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Ошибка при удалении', 'error');
                        console.error('Error:', error);
                    });
            }
        }

        document.getElementById('addRowForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const rowData = formData.getAll('new_row[]').filter(value => value.trim() !== '');

            if (rowData.length === 0) {
                showToast('Заполните хотя бы одно поле', 'error');
                return;
            }

            fetch('<?= base_url('excelupload/addrow/') ?>' + fileId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'new_row=' + encodeURIComponent(JSON.stringify(rowData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addRowModal.hide();
                    showToast('Строка добавлена', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Ошибка при добавлении строки', 'error');
                console.error('Error:', error);
            });
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

        document.addEventListener('DOMContentLoaded', function() {
            initNewRowInputs();
        });
    </script>
</body>
</html>
