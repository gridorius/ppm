<?php

namespace Ppm\Framework\Stream\MessageProtocol;

class MessageConverter
{
    const LENGTH_HEADERS_SIZE = 6;
    const LENGTH_MESSAGE_SIZE = 16;

    public static function convert(string $message, array $headers = []): string
    {
        $headersString = static::encodeHeaders($headers);
        $headerLength = str_pad(strlen($headersString), static::LENGTH_HEADERS_SIZE, ' ', STR_PAD_LEFT);
        $messageLength = str_pad(strlen($message), static::LENGTH_MESSAGE_SIZE, ' ', STR_PAD_LEFT);
        return $headerLength . $messageLength . $headersString . $message;
    }

    public static function encodeHeaders(array $headers): string
    {
        return serialize($headers);
    }

    public static function decodeHeaders(string $headers): array
    {
        return unserialize($headers);
    }
}