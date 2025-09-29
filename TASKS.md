# Дополнительные задачи для проекта

## 📋 Задачи для реализации

### 8. Скрипт выборки из БД Postgres данных с первого числа месяца по последнее число месяца

**Описание:** Создать функциональность для выборки данных из базы данных PostgreSQL за определенный месяц.

**Требования:**
- Выборка данных с 1 числа месяца по последнее число
- Параметризованные запросы для безопасности
- Возможность выбора месяца и года
- Пагинация результатов
- Экспорт результатов в Excel/PDF

**Пример реализации:**

```php
// В контроллере
public function getMonthlyData($year = null, $month = null)
{
    $year = $year ?? date('Y');
    $month = $month ?? date('m');
    
    $startDate = $year . '-' . $month . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    
    $data = $this->excelFileModel
        ->where('created_at >=', $startDate)
        ->where('created_at <=', $endDate . ' 23:59:59')
        ->findAll();
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $data,
        'period' => [
            'start' => $startDate,
            'end' => $endDate
        ]
    ]);
}
```

**SQL запрос:**
```sql
SELECT * FROM excel_files 
WHERE created_at >= '2025-09-01' 
AND created_at <= '2025-09-30 23:59:59'
ORDER BY created_at DESC;
```

---

### 9. Рефакторинг кода с CodeIgniter 2 на CodeIgniter 4

**Описание:** Переписать код с CI2 на CI4 с использованием современных подходов.

**Исходный код CI2:**
```php
function show_files(){
    $session_data = $this->session->userdata('logged_in');
    $data['inner_view'] = "filters";
    $limit_per_page = 20;
    $start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
    if ($start_index == 0) {$per_page = 0; $start_index = $limit_per_page; }
    else {$per_page= $start_index; $start_index = $start_index+$limit_per_page;  }
    $total_records = $this->ask2sud_model->getTotalASA($worker_id);
    if ($total_records > 0) {
        $data['result'] =  $this -> ask2sud_model -> ShowAskSudAccountLimit($worker_id, $per_page, $start_index);
        $config['base_url'] = base_url() . 'index.php/ask2sud/show_files';
        $config['total_rows'] = $total_records;
        $config['per_page'] = $limit_per_page;
        $config['uri_segment'] = 3;
        $this->pagination->initialize($config);
        $data['links'] = $this->pagination->create_links();
    }

    $this->load->view('templates/header', $data);
    $this->load->view('ask2sud/show_file', $data);
    $this->load->view('templates/footer_no', $data);
}
```

**Рефакторинг на CI4:**

```php
<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Ask2SudController extends Controller
{
    protected $ask2SudModel;
    protected $session;

    public function __construct()
    {
        $this->ask2SudModel = new \App\Models\Ask2SudModel();
        $this->session = \Config\Services::session();
    }

    public function showFiles()
    {
        // Проверка авторизации
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'inner_view' => 'filters',
            'result' => [],
            'links' => ''
        ];

        $limitPerPage = 20;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $limitPerPage;

        // Получаем worker_id из сессии или параметров
        $workerId = $this->session->get('worker_id') ?? $this->request->getGet('worker_id');

        if ($workerId) {
            $totalRecords = $this->ask2SudModel->getTotalASA($workerId);
            
            if ($totalRecords > 0) {
                $data['result'] = $this->ask2SudModel->showAskSudAccountLimit($workerId, $offset, $limitPerPage);
                
                // Настройка пагинации
                $pager = \Config\Services::pager();
                $pager->store('ask2sud', $page, $limitPerPage, $totalRecords);
                $data['links'] = $pager->makeLinks('ask2sud', 'ask2sud', $totalRecords, $limitPerPage, $page);
            }
        }

        return view('ask2sud/show_file', $data);
    }
}
```

**Основные изменения CI2 → CI4:**

1. **Namespace и структура:**
   - Добавлен namespace `App\Controllers`
   - Класс наследует от `CodeIgniter\Controller`
   - Конструктор для инициализации зависимостей

2. **Сессии:**
   - `$this->session->userdata()` → `$this->session->get()`
   - `$this->session->set_userdata()` → `$this->session->set()`

3. **Модели:**
   - `$this->ask2sud_model` → `$this->ask2SudModel` (PascalCase)
   - Создание экземпляра модели в конструкторе

4. **Пагинация:**
   - Старая система пагинации → `\Config\Services::pager()`
   - Более современный и гибкий подход

5. **Представления:**
   - `$this->load->view()` → `return view()`
   - Упрощенная загрузка шаблонов

6. **URL и параметры:**
   - `$this->uri->segment()` → `$this->request->getGet()`
   - Более безопасная работа с параметрами

---

### 10. PostgreSQL: Преобразование текущей даты в число формата YYYYMM

**Описание:** Преобразовать текущую дату в число формата YYYYMM (например, 20.09.2025 → 202509).

**SQL решения:**

```sql
-- Вариант 1: Используя to_char()
SELECT to_char(CURRENT_DATE, 'YYYYMM')::integer as date_number;

-- Вариант 2: Используя EXTRACT
SELECT (EXTRACT(YEAR FROM CURRENT_DATE) * 100 + EXTRACT(MONTH FROM CURRENT_DATE))::integer as date_number;

-- Вариант 3: Используя date_trunc и EXTRACT
SELECT (EXTRACT(YEAR FROM date_trunc('month', CURRENT_DATE)) * 100 + 
        EXTRACT(MONTH FROM date_trunc('month', CURRENT_DATE)))::integer as date_number;

-- Для конкретной даты
SELECT to_char('2025-09-20'::date, 'YYYYMM')::integer as date_number;
-- Результат: 202509
```

**PHP решения:**

```php
// Вариант 1: Простой
$dateNumber = date('Ym'); // 202509

// Вариант 2: Для конкретной даты
$date = '2025-09-20';
$dateNumber = date('Ym', strtotime($date)); // 202509

// Вариант 3: Используя DateTime
$date = new DateTime('2025-09-20');
$dateNumber = $date->format('Ym'); // 202509

// Вариант 4: В CodeIgniter 4
$date = \CodeIgniter\I18n\Time::parse('2025-09-20');
$dateNumber = $date->format('Ym'); // 202509
```

**Использование в запросах:**

```php
// В модели CodeIgniter 4
public function getDataByMonth($dateNumber)
{
    $year = substr($dateNumber, 0, 4);
    $month = substr($dateNumber, 4, 2);
    
    return $this->where('YEAR(created_at)', $year)
                ->where('MONTH(created_at)', $month)
                ->findAll();
}

// Или используя SQL
public function getDataByMonthSQL($dateNumber)
{
    $sql = "SELECT * FROM excel_files 
            WHERE to_char(created_at, 'YYYYMM') = ?";
    
    return $this->db->query($sql, [$dateNumber])->getResult();
}
```

---

## 📝 Примечания

- Все задачи должны быть реализованы с учетом безопасности (параметризованные запросы)
- Код должен следовать стандартам PSR-12
- Обязательно добавить валидацию входных данных
- Покрыть код тестами
- Добавить документацию к методам

## 🔗 Связанные файлы

- `src/app/Controllers/ExcelUploadController.php` - основной контроллер
- `src/app/Models/ExcelFileModel.php` - модель для работы с файлами
- `src/app/Models/ActionLogModel.php` - модель для журнала действий
