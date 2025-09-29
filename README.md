# Excel File Manager

Система управления Excel файлами с полным функционалом на CodeIgniter 4.

## Возможности

✅ **Загрузка файлов**
- Поддержка форматов .xlsx и .xls
- Валидация размера файла (до 10MB)
- Drag & Drop интерфейс
- Проверка формата файла

✅ **Управление файлами**
- Список всех загруженных файлов с пагинацией
- Просмотр содержимого файлов
- Скачивание оригинальных файлов
- Удаление файлов с подтверждением

✅ **Редактирование данных**
- Просмотр данных в табличном виде с пагинацией (5 записей)
- Редактирование ячеек в реальном времени
- Добавление новых строк через модальные окна
- Удаление строк с подтверждением
- Все операции через AJAX

✅ **Экспорт и отчеты**
- Экспорт в Excel (.xlsx)
- Экспорт в HTML (PDF требует установки DomPDF)
- Автоматическое форматирование

✅ **Журнал действий**
- Логирование всех операций (загрузка, редактирование, удаление, экспорт)
- Просмотр истории действий
- Информация о пользователе и времени

## Технологии

- **Backend**: CodeIgniter 4
- **Database**: PostgreSQL
- **Frontend**: Bootstrap 5, Font Awesome
- **Excel**: PhpSpreadsheet
- **PDF**: DomPDF (требует установки)
- **AJAX**: Fetch API

## ⚠️ Важное примечание

**Текущее состояние:** Проект полностью функционален, но PDF экспорт временно заменен на HTML экспорт из-за отсутствия библиотеки DomPDF.

**Для полной функциональности** установите PHP и Composer, затем выполните `composer install`. Подробные инструкции в файле [INSTALL.md](INSTALL.md).

## Установка

### 1. Клонирование проекта
```bash
git clone <repository-url>
cd tzz
```

### 2. Установка зависимостей
```bash
cd src
composer install
```

### 3. Настройка базы данных

Создайте базу данных PostgreSQL и обновите конфигурацию в `src/app/Config/Database.php`:

```php
public array $default = [
    'DSN'          => 'pgsql:host=localhost;port=5432;dbname=your_db;user=your_user;password=your_password',
    'hostname'     => 'localhost',
    'username'     => 'your_user',
    'password'     => 'your_password',
    'database'     => 'your_db',
    'DBDriver'     => 'Postgre',
    // ... остальные настройки
];
```

### 4. Запуск миграций
```bash
cd src
php spark migrate
```

### 5. Настройка веб-сервера

Настройте веб-сервер (Apache/Nginx) для работы с папкой `src/public` как корневой директорией.

### 6. Права доступа
```bash
chmod -R 755 src/writable
chmod -R 755 src/writable/uploads
```

## Docker (альтернативный способ)

Проект включает Docker конфигурацию:

```bash
docker-compose up -d
```

## Использование

### Основные маршруты

- `/` - Главная страница
- `/excelupload` - Загрузка файлов
- `/excelupload/files` - Список файлов
- `/excelupload/view/{id}` - Просмотр файла
- `/excelupload/logs` - Журнал действий

### API Endpoints

- `POST /excelupload/upload` - Загрузка файла
- `POST /excelupload/addrow/{id}` - Добавление строки
- `POST /excelupload/editrow/{id}` - Редактирование строки
- `GET /excelupload/deleterow/{id}` - Удаление строки
- `GET /excelupload/delete/{id}` - Удаление файла
- `GET /excelupload/download/{id}` - Скачивание файла
- `GET /excelupload/export-excel/{id}` - Экспорт в Excel
- `GET /excelupload/export-pdf/{id}` - Экспорт в PDF

## Структура базы данных

### Таблица `excel_files`
- `id` - Первичный ключ
- `name` - Имя файла
- `path` - Путь к файлу
- `row_count` - Количество строк
- `created_at` - Дата создания
- `updated_at` - Дата обновления

### Таблица `excel_rows`
- `id` - Первичный ключ
- `file_id` - ID файла (внешний ключ)
- `row_data` - Данные строки (JSON)
- `created_at` - Дата создания
- `updated_at` - Дата обновления

### Таблица `action_logs`
- `id` - Первичный ключ
- `file_id` - ID файла (внешний ключ)
- `action_type` - Тип действия
- `description` - Описание действия
- `user_ip` - IP пользователя
- `user_agent` - User Agent
- `created_at` - Дата создания

## Особенности реализации

### Безопасность
- Валидация типов файлов
- Проверка размера файлов
- Экранирование вывода
- CSRF защита (встроенная в CI4)

### Производительность
- Пагинация для больших файлов
- AJAX для быстрого взаимодействия
- Оптимизированные запросы к БД

### UX/UI
- Современный Bootstrap 5 дизайн
- Модальные окна для подтверждений
- Toast уведомления
- Drag & Drop загрузка
- Адаптивный дизайн

## Требования

- PHP 8.1+
- PostgreSQL 12+
- Composer
- Веб-сервер (Apache/Nginx)

## Лицензия

MIT License
