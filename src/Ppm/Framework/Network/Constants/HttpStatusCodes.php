<?php

namespace Ppm\Framework\Network\Constants;

class HttpStatusCodes
{
    const OK = [HttpResponseCodes::OK, HttpResponseStatuses::OK];
    const SERVER_ERROR = [HttpResponseCodes::SERVER_ERROR, HttpResponseStatuses::SERVER_ERROR];
    const BAD_REQUEST = [HttpResponseCodes::BAD_REQUEST, HttpResponseStatuses::BAD_REQUEST];
    const UNAUTHORIZED = [HttpResponseCodes::UNAUTHORIZED, HttpResponseStatuses::UNAUTHORIZED];
    const FORBIDDEN = [HttpResponseCodes::FORBIDDEN, HttpResponseStatuses::FORBIDDEN];
    const NOT_FOUND = [HttpResponseCodes::NOT_FOUND, HttpResponseStatuses::NOT_FOUND];
    const MOVED_PERMANENTLY = [HttpResponseCodes::MOVED_PERMANENTLY, HttpResponseStatuses::MOVED_PERMANENTLY];
    const NO_CONTENT = [HttpResponseCodes::NO_CONTENT, HttpResponseStatuses::NO_CONTENT];
}