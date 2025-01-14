<?php

namespace Ppm\Framework\Network\Socket;

class SSLOptions
{
    public string $local_cert;
    public string $passphrase;
    public bool $allow_self_signed;
    public bool $verify_peer;

    public function __construct(string $local_cert, string $passphrase, bool $allow_self_signed = true, bool $verify_peer = false)
    {
        $this->local_cert = $local_cert;
        $this->passphrase = $passphrase;
        $this->allow_self_signed = $allow_self_signed;
        $this->verify_peer = $verify_peer;
    }

    public function toArray(): array
    {
        return [
            "local_cert" => $this->local_cert,
            "passphrase" => $this->passphrase,
            "allow_self_signed" => $this->allow_self_signed,
            "verify_peer" => $this->verify_peer
        ];
    }
}