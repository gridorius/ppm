# Phar project manager

> Решение для сборки проектов и управления пакетами на языке php

### Структура проекта

```
Solution
    Project1
        Project1.proj.json
        ...
    Project2
        Pr2.proj.json
        ...
    solution.json
    ...
```

## solution.json - Набор метаданных для сборки проектов решения

Структура solution.json

| Свойство   | Тип                                       | Обязательное | Описание                                                                | 
|------------|-------------------------------------------|--------------|-------------------------------------------------------------------------|
| `projects` | Dictionary<string, string>                | Да           | Проекты решения <Наименование проекта, Путь относительно корня решения> |
| `scripts`  | Dictionary<string, [Script](#тип-Script)> | Нет          | Скрипты проекта                                                         |

## Тип Script

при запуске скрипта проект автоматически собирается при необходимости

| Свойство  | Тип      | Обязательное | Описание                                              | 
|-----------|----------|--------------|-------------------------------------------------------|
| `project` | string   | Да           | Проект                                                |
| `command` | string[] | Да           | Команда, запускается из директории собранного проекта |

### Пример solution.json

```json
{
  "projects": {
    "server": "Server/App"
  },
  "scripts": {
    "serve": {
      "project": "server",
      "command": [
        "php",
        "server.php"
      ]
    }
  }
}
```

### Структура *.proj.json

Допустимо использование переменных среды пример ${VARIABLE_NAME}

| Свойство      | Тип                                         | Обязательное          | Описание                                                                                                                                                                    |
|---------------|---------------------------------------------|-----------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `name`        | string                                      | Нет                   | Наименование проекта, по умолчанию - наименование директории проекта                                                                                                        |
| `version`     | string                                      | Для основного проекта | Версия проекта                                                                                                                                                              |
| `author`      | string                                      | Нет                   | Имя и почта автора, будут записаны в манифест проекта или в метаданные пакета                                                                                               |
| `description` | string                                      | Нет                   | Описание, будет записано в манифест проекта или в метаданные пакета                                                                                                         |
| `runner`      | string                                      | Нет                   | Имя файла запуска приложения, по умолчанию соответствует свойству `name`                                                                                                    |
| `include`     | string                                      | Нет                   | Маска, по которой файлы сканируются на типы                                                                                                                                 |
| `exclude`     | string                                      | Нет                   | Исключает файлы из сканирования типов                                                                                                                                       |
| `projects`    | string[]                                    | Нет                   | Пути к файлам проектов относительно директории текущего проекта                                                                                                             |
| `packages`    | Dictionary<string, string>                  | Нет                   | Удаленные зависимости                                                                                                                                                       |
| `includes`    | [Filter[]](#тип-Filter)                     | Нет                   | Скрипты, которые будут помещены в phar текущего проекта и подключены перед точкой входа, но после загрузки типов                                                            |
| `files`       | [Filter[]](#тип-Filter)                     | Нет                   | Файлы, которые будут перенесены в директорию сборки                                                                                                                         |
| `resources`   | [Filter[]](#тип-Filter)                     | Нет                   | Файлы, которые будут помещены в директорию ресурсов phar текущего проекта                                                                                                   |
| `stub`        | string                                      | Нет                   | Если указан, будет замещать генерируемый stub                                                                                                                               |
| `commands`    | Dictionary<string, [Command](#тип-Command)> | Нет                   | Команды проекта, доступны из директории собранного проекта c префиксом "app", формат: "команда - <обязательный аргумент> [опциональный аргумент]", тире - позиция для опций |

### Тип Filter

| Свойство  | Тип    | Обязательное | Описание                  |
|-----------|--------|--------------|---------------------------|
| `include` | string | Да           | Маска пути                |
| `exclude` | string | Нет          | Маска для исключения пути |

### Тип Command

Команды сборки

ppm app ...

| Свойство         | Тип                                   | Обязательное | Описание                                                                      |
|------------------|---------------------------------------|--------------|-------------------------------------------------------------------------------|
| `handler`        | string                                | Да           | Обработчик команды                                                            |
| `definedOptions` | [CommandOptions](#тип-CommandOptions) | Нет          | Опции команды, форматы: -abc --option value, --option=value, -o val, -o=value |
| `description`    | string                                | Нет          | Описание команды                                                              |

### Тип CommandOptions

| Свойство   | Тип   | Обязательное | Описание                 |
|------------|-------|--------------|--------------------------|
| `values`   | array | Нет          | Опции требующие значение |
| `counters` | array | Нет          | Флаги                    |

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
  "projects": [
    "relative/path/to/project/directory"
  ],
  "packages": {
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
      "type": "shell",
      "command": "php $(buildDirectory)run.php"
    },
    {
      "type": "delete",
      "template": "$(outDirectory)/path/to/file.ext"
    }
  ],
  "commands": {
    "migration add - <required> [optional]": {
      "handler": "Namespace\\Class::staticMethod",
      "description": "command description",
      "definedOptions": {
        "counters": [
          "a",
          "verbose"
        ],
        "values": [
          "b",
          "path"
        ]
      }
    }
  }
}
```