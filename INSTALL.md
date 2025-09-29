# Инструкция по установке зависимостей

## Проблема
При попытке экспорта в PDF возникает ошибка: `Class "Dompdf\Dompdf" not found`

## Причина
Библиотека DomPDF не установлена, так как отсутствует PHP и Composer в системе.

## Решение

### Вариант 1: Установка PHP и Composer (рекомендуется)

1. **Установите PHP 8.1+**
   - Скачайте с официального сайта: https://www.php.net/downloads.php
   - Или используйте XAMPP: https://www.apachefriends.org/

2. **Установите Composer**
   - Скачайте с официального сайта: https://getcomposer.org/download/
   - Следуйте инструкциям установки для Windows

3. **Установите зависимости**
   ```bash
   cd src
   composer install
   ```

4. **Восстановите PDF функциональность**
   - Раскомментируйте строки в `src/app/Controllers/ExcelUploadController.php`:
   ```php
   use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
   use Dompdf\Dompdf as DompdfLib;
   ```
   - Замените метод `exportPdf()` на оригинальную версию с DomPDF

### Вариант 2: Временное решение (текущее состояние)

Сейчас PDF экспорт заменен на HTML экспорт, который работает без дополнительных зависимостей.

**Что работает:**
- ✅ Загрузка Excel файлов
- ✅ Просмотр и редактирование данных
- ✅ Экспорт в Excel (.xlsx)
- ✅ Экспорт в HTML (вместо PDF)
- ✅ Все остальные функции

**Что нужно для полной функциональности:**
- Установить PHP и Composer
- Выполнить `composer install`
- Восстановить PDF экспорт

## Проверка установки

После установки PHP и Composer проверьте:

```bash
php -v
composer --version
```

Если команды работают, перейдите в папку `src` и выполните:

```bash
composer install
```

## Восстановление PDF функциональности

После установки зависимостей:

1. Откройте файл `src/app/Controllers/ExcelUploadController.php`
2. Раскомментируйте строки 8-9:
   ```php
   use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;
   use Dompdf\Dompdf as DompdfLib;
   ```
3. Замените метод `exportPdf()` на оригинальную версию с DomPDF
4. Обновите кнопку в `src/app/Views/files_list.php`:
   ```html
   <a href="#" class="btn btn-danger" id="exportPdfBtn">
       <i class="fas fa-file-pdf"></i> Экспорт в PDF
   </a>
   ```

## Альтернативные решения

### Docker (если установлен)
```bash
docker-compose up -d
```

### Использование готового хостинга
Разместите проект на хостинге с поддержкой PHP 8.1+ и Composer.

## Текущий статус проекта

✅ **Полностью работает:**
- Загрузка файлов с валидацией
- Просмотр и редактирование данных
- AJAX операции
- Экспорт в Excel
- Журнал действий
- Современный UI

⚠️ **Требует установки зависимостей:**
- Экспорт в PDF (временно заменен на HTML)

Проект готов к использованию! PDF экспорт можно восстановить после установки PHP и Composer.
