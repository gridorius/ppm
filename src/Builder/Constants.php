<?php

namespace Builder;

class Constants
{
    const ASSEMBLY_PHAR_NAME = 'Assembly.phar';
    const REPLACE_PROJECT_NAME = 'PROJECT_NAME';

    const PROJECT_NAME_REGEX_PATTERN = "/PROJECT_NAME/";
    const REPLACE_ENTRYPOINT_CLASS = 'ENTRYPOINT_CLASS';
    const REPLACE_ENTRYPOINT_METHOD = 'ENTRYPOINT_METHOD';

    const RUNNER_TEMPLATE_PATH = 'templates/runner.php';
    const STUB_TEMPLATE_PATH = 'templates/stub.php';

    const DEFAULT_ENTRYPOINT_METHOD = 'main';

    const MANIFEST_FILE_NAME_PHP = 'manifest.php';
    const MANIFEST_FILE_NAME_JSON = 'manifest.json';
}