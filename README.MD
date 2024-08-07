# Phar project manager

Решение для сборки проектов и управления пакетами на языке php

> Каждый проект должен содержать файл конфигурации *.proj.json

Структура *.proj.json:d

| Свойство            | Тип                        | Обязательное          | Описание                                                                                                         |
|---------------------|----------------------------|-----------------------|------------------------------------------------------------------------------------------------------------------|
| `name`              | string                     | Нет                   | Наименование проекта, по умолчанию - наименование директории проекта                                             |
| `version`           | string                     | Для основного проекта | Версия проекта                                                                                                   |
| `author`            | string                     | Нет                   | Имя и почта автора, будут записаны в манифест проекта или в метаданные пакета                                    |
| `description`       | string                     | Нет                   | Описание, будет записано в манифест проекта или в метаданные пакета                                              |
| `runner`            | string                     | Нет                   | Имя файла запуска приложения, по умолчанию соответствует свойству `name`                                         |
| `include`           | string                     | Нет                   | Маска, по которой файлы сканируются на типы                                                                      |
| `exclude`           | string                     | Нет                   | Исключает файлы из сканирования типов                                                                            |
| `projectReferences` | string[]                   | Нет                   | Пути к файлам проектов относительно директории текущего проекта                                                  |
| `packageReferences` | dictionary<string, string> | Нет                   | Удаленные зависимости                                                                                            |
| `includes`          | [filter[]](#тип-filter)    | Нет                   | Скрипты, которые будут помещены в phar текущего проекта и подключены перед точкой входа, но после загрузки типов |
| `files`             | [filter[]](#тип-filter)    | Нет                   | Файлы, которые будут перенесены в директорию сборки                                                              |
| `resources`         | [filter[]](#тип-filter)    | Нет                   | Файлы, которые будут помещены в директорию ресурсов phar текущего проекта                                        |
| `stub`              | string                     | Нет                   | Если указан, будет замещать генерируемый stub                                                                    |

### Тип filter

| Свойство  | Тип    | Обязательное | Описание                  |
|-----------|--------|--------------|---------------------------|
| `include` | string | Да           | Маска пути                |
| `exclude` | string | Нет          | Маска для исключения пути |

Структура *.sln.json

| Свойство   | Тип      | Обязательное | Описание                                                                               | 
|------------|----------|--------------|----------------------------------------------------------------------------------------|
| `version`  | string   | Да           | Версия решения, распространяется на все собираемые проекты у которых не указана версия |
| `projects` | string[] | Да           | Пути к файлам проектов решения                                                         |

### Пример *.proj.json

```json
{
  "name": "ProjectName",
  "version": "1.0.0",
  "author": "author mail@mail.com",
  "description": "description of this project",
  "runner": "name",
  "entrypoint": "EntrypointClass[::method(default main)]",
  "include": "*.php",
  "stub": "directory/stub.php",
  "exclude": "dirname/*.a.php;dirname/*.b.php",
  "projectReferences": [
    "relative/path/to/depend/project.json"
  ],
  "packageReferences": {
    "PackageName": "1.23.*"
  },
  "includes": [
    {
      "include": "path/*",
      "exclude": "path/*.css;path/*.csv"
    }
  ],
  "files": [
    {
      "include": "path/*",
      "exclude": "path/*.css;path/*.csv"
    }
  ],
  "resources": [
    {
      "include": "path/*",
      "exclude": "path/*.css;path/*.csv"
    }
  ],
  "actions": [
    {
      "on": "BeforeBuild|AfterBuild",
      "type": "copy",
      "from": "$(buildDirectory)/path/from/*",
      "to": "$(outDirectory)/path/to"
    },
    {
      "type": "shell",
      "command": "php $(buildDirectory)run.php"
    },
    {
      "type": "delete",
      "file": "$(outDirectory)/path/to/file.ext",
      "directory": "$(outDirectory)/path/to/directory"
    }
  ]
}
```