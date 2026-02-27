<?php

namespace Ppm\Framework\Utils;

class EntityFinder
{
    /**
     * find types on file
     *
     * @param string $path
     * @return array
     */
    public static function findByTokens(string $path): array
    {
        try {
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
                    case T_ENUM:
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
        } catch (\ParseError $e) {
            throw new \RuntimeException("Unable to parse file {$path} " . $e->getMessage(), 0, $e);
        }
    }
}