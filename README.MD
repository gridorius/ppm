# Phar project manager

Решение для сборки проектов и управления пакетами на языке php

> Каждый проект должен содержать файл конфигурации *.proj.json

### Структура *.proj.json

Допустимо использование переменных среды пример ${VARIABLE_NAME}

| Свойство            | Тип                         | Обязательное          | Описание                                                                                                                                                                      |
|---------------------|-----------------------------|-----------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `name`              | string                      | Нет                   | Наименование проекта, по умолчанию - наименование директории проекта                                                                                                          |
| `version`           | string                      | Для основного проекта | Версия проекта                                                                                                                                                                |
| `author`            | string                      | Нет                   | Имя и почта автора, будут записаны в манифест проекта или в метаданные пакета                                                                                                 |
| `description`       | string                      | Нет                   | Описание, будет записано в манифест проекта или в метаданные пакета                                                                                                           |
| `runner`            | string                      | Нет                   | Имя файла запуска приложения, по умолчанию соответствует свойству `name`                                                                                                      |
| `include`           | string                      | Нет                   | Маска, по которой файлы сканируются на типы                                                                                                                                   |
| `exclude`           | string                      | Нет                   | Исключает файлы из сканирования типов                                                                                                                                         |
| `projectReferences` | string[]                    | Нет                   | Пути к файлам проектов относительно директории текущего проекта                                                                                                               |
| `packageReferences` | Dictionary<string, string>  | Нет                   | Удаленные зависимости                                                                                                                                                         |
| `includes`          | [Filter[]](#тип-Filter)     | Нет                   | Скрипты, которые будут помещены в phar текущего проекта и подключены перед точкой входа, но после загрузки типов                                                              |
| `files`             | [Filter[]](#тип-Filter)     | Нет                   | Файлы, которые будут перенесены в директорию сборки                                                                                                                           |
| `resources`         | [Filter[]](#тип-Filter)     | Нет                   | Файлы, которые будут помещены в директорию ресурсов phar текущего проекта                                                                                                     |
| `stub`              | string                      | Нет                   | Если указан, будет замещать генерируемый stub                                                                                                                                 |
| `commands`          | Dictionary<string, Command> | Нет                   | Команды проекта, запускается из директории проекта, формат: "команда - <обязательный аргумент> [опциональный аргумент]", тире - позиция в которой могут устанавливаться опции |

### Тип Filter

| Свойство  | Тип    | Обязательное | Описание                  |
|-----------|--------|--------------|---------------------------|
| `include` | string | Да           | Маска пути                |
| `exclude` | string | Нет          | Маска для исключения пути |

### Тип Command
Команды пакетов доступны из директории сборки

ppm run ...

| Свойство         | Тип                         | Обязательное | Описание                                                                                                                           |
|------------------|-----------------------------|--------------|------------------------------------------------------------------------------------------------------------------------------------|
| `handler`        | string                      | Да           | Обработчик команды                                                                                                                 |
| `definedOptions` | Dictionary<string, boolean> | Нет          | Опции команды, true - со значением, false - без значения, поддерживаются форматы: --option value, --option=value, -o val, -o=value |
| `description`    | string                      | Нет          | Описание команды                                                                                                                   |

Структура *.sln.json

| Свойство   | Тип      | Обязательное | Описание                                                                               | 
|------------|----------|--------------|----------------------------------------------------------------------------------------|
| `version`  | string   | Да           | Версия решения, распространяется на все собираемые проекты у которых не указана версия |
| `projects` | string[] | Да           | Пути к файлам проектов решения                                                         |

### Пример *.proj.json

```json
{
  "name": "ProjectName",
  "version": "${MY_PROJECT_VERSION}",
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
  ],
  "commands": {
    "migration add - <required> [optional]": {
      "handler": "Namespace\\Class::staticMethod",
      "description": "command description",
      "definedOptions": {
        "s": false
      }
    }
  }
}
```