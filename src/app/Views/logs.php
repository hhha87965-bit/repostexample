<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал действий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .log-entry {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .log-entry.upload { border-left-color: #28a745; }
        .log-entry.edit_row { border-left-color: #ffc107; }
        .log-entry.delete_row { border-left-color: #dc3545; }
        .log-entry.delete_file { border-left-color: #dc3545; }
        .log-entry.download { border-left-color: #17a2b8; }
        .log-entry.export_excel { border-left-color: #28a745; }
        .log-entry.export_pdf { border-left-color: #dc3545; }
        
        .action-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .action-icon.upload { background-color: #28a745; color: white; }
        .action-icon.edit_row { background-color: #ffc107; color: black; }
        .action-icon.delete_row { background-color: #dc3545; color: white; }
        .action-icon.delete_file { background-color: #dc3545; color: white; }
        .action-icon.download { background-color: #17a2b8; color: white; }
        .action-icon.export_excel { background-color: #28a745; color: white; }
        .action-icon.export_pdf { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-history text-info"></i> Журнал действий</h2>
                    <div>
                        <a href="<?= base_url('excelupload/files') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад к файлам
                        </a>
                        <a href="<?= base_url('excelupload') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Загрузить файл
                        </a>
                    </div>
                </div>

                <?php if (empty($logs)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h4>Журнал пуст</h4>
                        <p>Пока нет записей в журнале действий.</p>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list"></i> История действий</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($logs as $log): ?>
                                <div class="log-entry <?= $log['action_type'] ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="action-icon <?= $log['action_type'] ?>">
                                            <?php
                                            $icons = [
                                                'upload' => 'fas fa-upload',
                                                'edit_row' => 'fas fa-edit',
                                                'delete_row' => 'fas fa-trash',
                                                'delete_file' => 'fas fa-trash-alt',
                                                'download' => 'fas fa-download',
                                                'export_excel' => 'fas fa-file-excel',
                                                'export_pdf' => 'fas fa-file-pdf'
                                            ];
                                            $icon = $icons[$log['action_type']] ?? 'fas fa-info';
                                            ?>
                                            <i class="<?= $icon ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?php
                                                        $actionNames = [
                                                            'upload' => 'Загрузка файла',
                                                            'edit_row' => 'Редактирование строки',
                                                            'delete_row' => 'Удаление строки',
                                                            'delete_file' => 'Удаление файла',
                                                            'download' => 'Скачивание файла',
                                                            'export_excel' => 'Экспорт в Excel',
                                                            'export_pdf' => 'Экспорт в PDF'
                                                        ];
                                                        echo $actionNames[$log['action_type']] ?? $log['action_type'];
                                                        ?>
                                                    </h6>
                                                    <?php if (!empty($log['description'])): ?>
                                                        <p class="mb-1 text-muted"><?= esc($log['description']) ?></p>
                                                    <?php endif; ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i> <?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?>
                                                        <?php if (!empty($log['user_ip'])): ?>
                                                            | <i class="fas fa-globe"></i> <?= esc($log['user_ip']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
