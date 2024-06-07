# Phar project manager
Решение для сборки проектов и управления пакетами на языке php

> Каждый проект должен содержать файл конфигурации *.proj.json
 
Структура *.proj.json:d

| Свойство            | Тип                        | Обязательное          | Описание                                                                                                |
|---------------------|----------------------------|-----------------------|---------------------------------------------------------------------------------------------------------|
| `name`              | string                     | Нет                   | Наименование проекта, по умолчанию наименование директории проекта                                      |
| `version`           | string                     | Для основного проекта | Версия проекта                                                                                          |
| `author`            | string                     | Нет                   | Имя и почта автора, будут записаны в манифест проекта или в метаданные пакета                           |
| `description`       | string                     | Нет                   | Описание, будет записано в манифест проекта или в метаданные пакета                                     |
| `runner`            | string                     | Нет                   | Имя файла запуска приложения, по умолчанию соответствует свойству `name`                                |
| `include`           | string                     | Нет                   | Маска по которой файлы сканируются на типы                                                              |
| `exclude`           | string                     | Нет                   | Исключает файлы из сканирования типов                                                                   |
| `projectReferences` | string[]                   | Нет                   | Пути к файлу проекта относительно текущего проекта либо глобальный                                      |
| `packageReferences` | dictionary<string, string> | Нет                   | Удаленные зависимости                                                                                   |
| `includes`          | [filter[]](#тип-filter)    | Нет                   | Скрипты будут помещены в phar текущего проекта и подключены перед точкой входа, но после загрузки типов |
| `files`             | [filter[]](#тип-filter)    | Нет                   | Файлы, которые будут перенесены в директорию сборки                                                     |
| `resources`         | [filter[]](#тип-filter)    | Нет                   | Файлы, которые будут помещены в директорию ресурсов phar текущего проекта                               |
| `stub`              | string                     | Нет                   | Если указан, будет замещать генерируемый stub                                                           |

### Тип filter
| Свойство  | Тип    | Обязательное | Описание                  |
|-----------|--------|--------------|---------------------------|
| `include` | string | Да           | Маска пути                |
| `exclude` | string | Нет          | Маска для исключения пути |

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
  ]
}
```

### Команды:
```
ppm <command> [arguments]

commands:
- build [out directory] [build directory] - Собирает проект
        build directory - по умолчанию текущая директория
        out directory - по умолчанию создает директорию out в текущей
        
- build package [build directory] - Собирает пакет
        build directory - по умолчанию текущая директория
        -p <source> - Загрузить проект в реестр
                      source - реестр
        
- auth <source> <login> - Авторизироваться в удаленном реестре
        source - реестр
        login - Логин
        
- sources [add|delete] <source> - add/delete source
        add - добавить источник source в каталог
        delete - удалить источник source из каталога
        
- restore - Загрузить зависимости проекта в локальный реестр

- packages upload <source> [build directory] - Загружает собраный пакет в реестр
        build directory - по умолчанию текущая директория
        source - реестр
```