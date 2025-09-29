<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
use Dompdf\Dompdf as DompdfLib;

class ExcelUploadController extends Controller
{
    protected $fileModel;
    protected $rowModel;
    protected $logModel;

    public function __construct()
    {
        $this->fileModel = new \App\Models\ExcelFileModel();
        $this->rowModel = new \App\Models\ExcelRowModel();
        $this->logModel = new \App\Models\ActionLogModel();
    }

    public function index(): string
    {
        return view('excel_form');
    }

    public function upload()
    {
        helper('text');

        $file = $this->request->getFile('excel_file');

        if (!$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Файл невалидный']);
        }

        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getClientExtension();
        
        if (!in_array($extension, $allowedExtensions)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Разрешены только файлы Excel (.xlsx, .xls)']);
        }

        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB
            return $this->response->setJSON(['success' => false, 'message' => 'Размер файла не должен превышать 10MB']);
        }

        try {
            $newName = random_string('alnum', 10) . '.' . $extension;
            $file->move(WRITEPATH . 'uploads', $newName);
            $fullPath = WRITEPATH . 'uploads/' . $newName;

            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            $data = array_filter($data, function($row) {
                return !empty(array_filter($row));
            });

            $fileId = $this->fileModel->insert([
                'name' => $file->getClientName(),
                'path' => $newName,
                'row_count' => count($data)
            ]);

            foreach ($data as $row) {
                $this->rowModel->insert([
                    'file_id' => $fileId,
                    'row_data' => json_encode($row),
                ]);
            }

            $this->logModel->logAction($fileId, 'upload', 'Файл загружен: ' . $file->getClientName());

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Файл успешно загружен']);
            }

            return redirect()->to('/excelupload/files');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Ошибка при обработке файла: ' . $e->getMessage()]);
            }
            return 'Ошибка при обработке файла: ' . $e->getMessage();
        }
    }

    public function files()
    {
        $data['files'] = $this->fileModel->orderBy('created_at', 'DESC')->paginate(10);
        $data['pager'] = $this->fileModel->pager;

        return view('files_list', $data);
    }

    public function view($fileId)
    {
        $file = $this->fileModel->find($fileId);
        if (!$file) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Файл не найден');
        }

        $data['file'] = $file;
        $data['rows'] = $this->rowModel->where('file_id', $fileId)->paginate(5);
        $data['pager'] = $this->rowModel->pager;

        return view('file_view', $data);
    }

    public function addRow($fileId)
    {
        if ($this->request->getMethod() === 'post') {
            $rowData = $this->request->getPost('new_row');
            
            if (!empty($rowData)) {
                $this->rowModel->insert([
                    'file_id' => $fileId,
                    'row_data' => json_encode($rowData),
                ]);

                $this->updateRowCount($fileId);

                $this->logModel->logAction($fileId, 'add_row', 'Добавлена новая строка');

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Строка добавлена']);
                }
            }
        }

        return redirect()->to('/excelupload/view/' . $fileId);
    }

    public function editRow($rowId)
    {
        if ($this->request->getMethod() === 'post') {
            $rowData = $this->request->getPost('row_data');
            $row = $this->rowModel->find($rowId);
            
            if ($row && !empty($rowData)) {
                $this->rowModel->update($rowId, [
                    'row_data' => json_encode($rowData),
                ]);

                $this->logModel->logAction($row['file_id'], 'edit_row', 'Строка отредактирована');

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Строка обновлена']);
                }
            }
        }

        return redirect()->back();
    }

    public function deleteRow($rowId)
    {
        $row = $this->rowModel->find($rowId);
        if ($row) {
            $fileId = $row['file_id'];
            $this->rowModel->delete($rowId);

            $this->updateRowCount($fileId);

            $this->logModel->logAction($fileId, 'delete_row', 'Строка удалена');

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Строка удалена']);
            }
        }

        return redirect()->back();
    }

    public function delete($fileId)
    {
        $file = $this->fileModel->find($fileId);
        if ($file) {
            $filePath = WRITEPATH . 'uploads/' . $file['path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->rowModel->where('file_id', $fileId)->delete();

            $this->fileModel->delete($fileId);

            $this->logModel->logAction($fileId, 'delete_file', 'Файл удален: ' . $file['name']);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Файл удален']);
            }
        }

        return redirect()->to('/excelupload/files');
    }

    public function download($fileId)
    {
        $file = $this->fileModel->find($fileId);
        if (!$file) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Файл не найден');
        }

        $filePath = WRITEPATH . 'uploads/' . $file['path'];
        if (!file_exists($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Файл не найден на диске');
        }

        $this->logModel->logAction($fileId, 'download', 'Файл скачан');

        return $this->response->download($filePath, null);
    }

    public function exportExcel($fileId)
    {
        $file = $this->fileModel->find($fileId);
        if (!$file) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Файл не найден');
        }

        $rows = $this->rowModel->where('file_id', $fileId)->findAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Export');

        $rowIndex = 1;
        foreach ($rows as $row) {
            $data = json_decode($row['row_data'], true);
            $colIndex = 'A';
            foreach ($data as $cell) {
                $sheet->setCellValue($colIndex . $rowIndex, $cell);
                $colIndex++;
            }
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        
        $this->logModel->logAction($fileId, 'export_excel', 'Экспорт в Excel');

        $filename = 'export_' . $file['name'] . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf($fileId)
    {
        $file = $this->fileModel->find($fileId);
        if (!$file) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Файл не найден');
        }

        $rows = $this->rowModel->where('file_id', $fileId)->findAll();
        
        $html = '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Экспорт данных</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .export-info { margin-bottom: 20px; color: #666; font-size: 10px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <h2>' . htmlspecialchars($file['name']) . '</h2>
    <div class="export-info">
        <p><strong>Дата экспорта:</strong> ' . date('d.m.Y H:i:s') . '</p>
        <p><strong>Количество строк:</strong> ' . count($rows) . '</p>
    </div>
    <table>
        <thead>
            <tr>';
        if (!empty($rows)) {
            $firstRow = json_decode($rows[0]['row_data'], true);
            foreach ($firstRow as $index => $cell) {
                $html .= '<th>Колонка ' . ($index + 1) . '</th>';
            }
        }
        
        $html .= '</tr>
        </thead>
        <tbody>';
        
        $rowCount = 0;
        foreach ($rows as $row) {
            $data = json_decode($row['row_data'], true);
            $html .= '<tr>';
            foreach ($data as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';

            $rowCount++;
            if ($rowCount % 30 == 0 && $rowCount < count($rows)) {
                $html .= '</tbody></table><div class="page-break"></div><table><tbody>';
            }
        }
        
        $html .= '</tbody>
    </table>
</body>
</html>';

        $dompdf = new DompdfLib();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $this->logModel->logAction($fileId, 'export_pdf', 'Экспорт в PDF');

        $filename = 'export_' . $file['name'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $dompdf->output();
        exit;
    }

    public function logs($fileId = null)
    {
        $logModel = new \App\Models\ActionLogModel();
        
        if ($fileId) {
            $data['logs'] = $logModel->where('file_id', $fileId)->orderBy('created_at', 'DESC')->paginate(20);
        } else {
            $data['logs'] = $logModel->orderBy('created_at', 'DESC')->paginate(20);
        }
        
        $data['pager'] = $logModel->pager;
        
        return view('logs', $data);
    }

    private function updateRowCount($fileId)
    {
        $count = $this->rowModel->where('file_id', $fileId)->countAllResults();
        $this->fileModel->update($fileId, ['row_count' => $count]);
    }
}