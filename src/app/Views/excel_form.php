<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка Excel файлов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .upload-area {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #0056b3;
            background-color: #e3f2fd;
        }
        .upload-area.dragover {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .file-info {
            margin-top: 15px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            display: none;
        }
        .progress {
            display: none;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-file-excel text-success"></i> Загрузка Excel файлов</h3>
                    </div>
                    <div class="card-body">
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5>Перетащите файл сюда или нажмите для выбора</h5>
                            <p class="text-muted">Поддерживаются форматы: .xlsx, .xls (максимум 10MB)</p>
                            <input type="file" id="fileInput" name="excel_file" accept=".xlsx,.xls" style="display: none;">
                        </div>
                        
                        <div class="file-info" id="fileInfo">
                            <strong>Выбранный файл:</strong> <span id="fileName"></span><br>
                            <strong>Размер:</strong> <span id="fileSize"></span>
                        </div>
                        
                        <div class="progress" id="progressBar">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="uploadBtn" disabled>
                                <i class="fas fa-upload"></i> Загрузить файл
                            </button>
                            <a href="<?= base_url('excelupload/files') ?>" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Список файлов
                            </a>
                        </div>
                    </div>
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
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressBar = document.getElementById('progressBar');
        const toast = new bootstrap.Toast(document.getElementById('toast'));
        const toastMessage = document.getElementById('toastMessage');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        function handleFile(file) {
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                                 'application/vnd.ms-excel'];
            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
                showToast('Ошибка: Разрешены только файлы Excel (.xlsx, .xls)', 'error');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showToast('Ошибка: Размер файла не должен превышать 10MB', 'error');
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'block';
            uploadBtn.disabled = false;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        uploadBtn.addEventListener('click', () => {
            const file = fileInput.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('excel_file', file);

            progressBar.style.display = 'block';
            uploadBtn.disabled = true;

            fetch('<?= base_url('excelupload/upload') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                progressBar.style.display = 'none';
                uploadBtn.disabled = false;
                
                if (data.success) {
                    showToast(data.message, 'success')
                    fileInput.value = '';
                    fileInfo.style.display = 'none';
                    uploadBtn.disabled = true;
                    setTimeout(() => {
                        window.location.href = '<?= base_url('excelupload/files') ?>';
                    }, 2000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                progressBar.style.display = 'none';
                uploadBtn.disabled = false;
                showToast('Ошибка при загрузке файла', 'error');
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
    </script>
</body>
</html>
