Проект для валидации и нормализации данных на PHP с помощью гибких **типов (Types)** и **Sanitizer**.  
Поддерживаются базовые типы (`StringType`, `IntegerType`, `FloatType`, `PhoneRuType`, `ArrayType`, `StructType`) и вложенные структуры.

## Установка

```bash
composer install
```

## Запуск тестов

```bash
composer test
```

## Статический анализ

```bash
composer phpstan
```

## Стиль кода

Проверка без изменения файлов:

```bash
composer cs-check
```

Автоисправление стиля:

```bash
composer cs
```
