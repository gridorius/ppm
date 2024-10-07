<?php

namespace Ppm\Framework\Network\Client\Body;

use Ppm\Framework\Network\ContentTypes;

class JsonBody extends RawBody
{
    public function __construct(mixed $data)
    {
        parent::__construct(json_encode($data), ContentTypes::JSON);
    }
}