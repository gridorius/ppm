<?php

namespace Ppm\Framework\Crypto;

use OpenSSLAsymmetricKey;

class CertificateBuilder
{
    private array $distinguished_names;
    private ?OpenSSLAsymmetricKey $privateKey = null;

    public function __construct(
        string $countryName,
        string $stateOrProvinceName,
        string $localityName,
        string $organizationName,
        string $organizationalUnitName,
        string $commonName,
        string $emailAddress,
    )
    {
        $this->distinguished_names = [
            'countryName' => $countryName,
            'stateOrProvinceName' => $stateOrProvinceName,
            'localityName' => $localityName,
            'organizationName' => $organizationName,
            'organizationalUnitName' => $organizationalUnitName,
            'commonName' => $commonName,
            'emailAddress' => $emailAddress,
        ];
    }

    public function generateKey(?array $options = null): static
    {
        $this->privateKey = openssl_pkey_new($options);
        return $this;
    }

    public function generatePem(int $days = 365, string $passphrase = ''): string
    {
        if (is_null($this->privateKey))
            $this->privateKey = openssl_pkey_new();
        $certificate = openssl_csr_new($this->distinguished_names, $this->privateKey);
        $x509 = openssl_csr_sign($certificate, null, $this->privateKey, $days);
        $pem = [];
        openssl_x509_export($x509, $pem[0]);
        openssl_pkey_export($this->privateKey, $pem[1], $passphrase);
        return implode($pem);
    }
}