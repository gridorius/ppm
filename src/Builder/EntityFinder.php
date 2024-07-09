<?php

namespace Builder;

class EntityFinder
{
    public static function findByTokens(string $path): array
    {
        $content = file_get_contents($path);
        $tokens = token_get_all($content, TOKEN_PARSE);
        $entities = [];
        $version = phpversion();

        $namespace = null;
        $state = null;
        $doubleColon = false;
        foreach ($tokens as $token) {
            if (!is_array($token)) {
                $state = 'empty';
                continue;
            }
            switch ($token[0]) {
                case T_DOUBLE_COLON:
                    $doubleColon = true;
                    break;
                case T_NAMESPACE:
                    $namespace = '';
                    $state = 'namespace';
                    break;
                case T_EXTENDS:
                case T_IMPLEMENTS:
                    $state = 'empty';
                    break;
                case T_CLASS:
                case T_TRAIT:
                case T_INTERFACE:
                    if (!$doubleColon)
                        $state = 'entity';
                    break;
                case T_STRING:
                    switch ($state) {
                        case 'namespace':
                            $namespace .= $token[1];
                            break;
                        case 'entity':
                            $entities[] = (empty($namespace) ? '' : $namespace . "\\") . $token[1];
                            break;
                    }
                    break;
                case T_NS_SEPARATOR:
                    if ($state == 'namespace')
                        $namespace .= '\\';
                    break;
                default:
                    $doubleColon = false;
            }

            if ($version >= 8 && in_array($token[0], [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED]) && $state == 'namespace')
                $namespace = $token[1];
        }

        return $entities;
    }
}