<?php

namespace Ppm\Framework\Network\Client;

use Generator;
use Ppm\Framework\Stream\Parallel\StreamReceiverBase;
use Ppm\Framework\Stream\ResourceStream;

class HttpResponseReceiver extends StreamReceiverBase
{
    private ResponseDataParser $parser;
    private Generator $parserGenerator;

    public function __construct(ResourceStream $stream, callable $onProgress = null)
    {
        parent::__construct($stream);
        $this->parser = new ResponseDataParser($stream, $onProgress);
        $this->parserGenerator = $this->parser->getGenerator();
    }

    public function onReadyContent(): bool
    {
        if ($this->parser->isCompleted())
            return false;

        while ($this->parserGenerator->current())
            $this->parserGenerator->next();

        return true;
    }

    public function getResponse(): Response
    {
        return $this->parser->getResponse();
    }
}