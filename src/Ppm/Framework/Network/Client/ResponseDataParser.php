<?php

namespace Ppm\Framework\Network\Client;

use Generator;
use Ppm\Framework\Stream\IStream;

class ResponseDataParser
{
    const STATE_HEADER = 1;
    const STAGE_BODY = 2;
    const STAGE_CHUNKED = 3;
    const STATE_COMPLETED = 4;
    private string $lastLine = '';
    private int $state;
    private IStream $stream;
    private Response $response;
    private $onProgress;

    public function __construct(IStream $stream, callable $onProgress = null)
    {
        $this->state = static::STATE_HEADER;
        $this->response = new Response();
        $this->stream = $stream;
        $this->onProgress = $onProgress;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getGenerator(): Generator
    {
        $unread = 0;
        while (true) {
            if ($this->state == static::STATE_COMPLETED)
                return false;

            if (!$this->state == static::STAGE_CHUNKED) {
                $line = $this->readLine();
                if (!is_null($line)) {
                    switch ($this->state) {
                        case static::STATE_HEADER:
                            $this->parseHeaderLine(trim($line));
                            break;
                        case static::STAGE_BODY:
                            if (!empty($line)) {
                                $this->response->addContent($line);
                                if (!is_null($this->onProgress))
                                    call_user_func($this->onProgress, (int)$this->response->getheader('Content-Length'), $this->response->getLength());
                            } else
                                $this->state = static::STATE_COMPLETED;
                    }
                } else {
                    yield false;
                }
            } else {
                $unread = $this->parseChunk($unread);
                if (!is_null($this->onProgress))
                    call_user_func($this->onProgress, (int)$this->response->getheader('Content-Length'), $this->response->getLength());
                yield $unread > 0;
            }
        }
    }

    public function isCompleted(): bool
    {
        return $this->state == static::STATE_COMPLETED;
    }

    private function parseHeaderLine(string $line): void
    {
        if (empty($line)) {
            if ($this->response->getHeader('Transfer-Encoding') === 'chunked')
                $this->state = static::STAGE_BODY;
            else
                $this->state = static::STAGE_CHUNKED;
            return;
        }

        if ($this->response->getCode() == -1) {
            $statusCode = explode(' ', $line, 3);
            $this->response->setStatusCode($statusCode[0], $statusCode[1]);
        } else {
            [$name, $value, $options] = static::parseHeader($line);
            $this->response
                ->setHeader($name, $value)
                ->setHeaderOption($name, $options);
        }
    }

    private function parseChunk(int $unread = 0): int
    {
        if ($unread == 0) {
            $line = trim($this->readLine());
            if (empty($line))
                return 0;
            $size = (int)$line;
            if ($size == 0) {
                $this->state = static::STATE_COMPLETED;
                return 0;
            }
            $data = $this->stream->read($size);
            $readLength = strlen($data);
            if ($readLength < $size)
                $unread = $size - $readLength;
            $this->response->addContent(trim($data, "\r"));
            return $unread;
        } else {
            $data = $this->stream->read($unread);
            $readLength = strlen($data);
            if ($readLength < $unread)
                $unread -= $readLength;
            $this->response->addContent(trim($data, "\r"));
            return $unread;
        }
    }

    private function readLine(): ?string
    {
        $line = $this->stream->readLine();
        if (str_ends_with($line, "\n")) {
            $line = $this->lastLine . $line;
            $this->lastLine = '';
            return $line;
        } else {
            $this->lastLine = $line;
            return null;
        }
    }

    private static function parseHeader(string $header): array
    {
        [$header, $value] = explode(':', $header, 2);
        $options = [];
        if (str_contains($value, ";") || str_contains($value, "=")) {
            $optionsData = explode(';', $value);
            foreach ($optionsData as $option) {
                if (str_contains($option, "=")) {
                    [$key, $value] = explode('=', $option);
                    $options[trim($key)] = trim($value);
                } else {
                    $options[trim($option)] = true;
                }
            }
        } else {
            return [$header, $value];
        }
        return [$header, $value, $options];
    }

//    public function parse(IStream $stream, callable $progressFunction = null): \Generator
//    {
//        $requestInfo = trim($this->readLine($stream));
//        $headers = [];
//        $headerOptions = [];
//        $statusArray = explode(' ', $requestInfo, 3);
//
//        while (!empty($headerRaw = trim($stream->readLine()))) {
//            [$header, $value, $options] = static::parseHeader($headerRaw);
//            if (!empty($headers[$header])) {
//                if (is_array($headers[$header]))
//                    $headers[$header][] = $value;
//                else
//                    $headers[$header] = [$headers[$header], $value];
//            } else
//                $headers[$header] = $value;
//
//            if (!empty($options))
//                if (!empty($headerOptions[$header])) {
//                    if (is_array($headerOptions[$header]))
//                        $headerOptions[$header][] = $value;
//                    else
//                        $headerOptions[$header] = [$headerOptions[$header], $options];
//                } else
//                    $headerOptions[$header] = $options;
//        }
//
//        $body = $this->parseBody($headers, $headerOptions, $stream, $progressFunction);
//        return new Response((int)$statusArray[1], $statusArray[2] ?? '', $headers, $headerOptions, $body);
//    }
//
//    private function parseBody(array $headers, array $headerOptions, ResourceStream $stream, callable $progressFunction = null): string
//    {
//        if ($headerOptions['Transfer-Encoding'] !== 'chunked') {
//            $content = "";
//            $length = (int)trim($stream->readLine());
//            while ($length > 0) {
//                $content .= trim($stream->read($length + 2));
//                $length = (int)trim($stream->readLine());
//            }
//            return $content;
//        } else {
//            $length = $headers['Content-Length'] ?? 0;
//            $content = "";
//            while ($stream->readLine() != '') {
//                $content .= $line = $stream->readLine();
//                if ($progressFunction !== null)
//                    $progressFunction($length, strlen($line));
//            }
//            return $content;
//        }
//    }
}