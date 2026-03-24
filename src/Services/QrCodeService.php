<?php
require_once __DIR__ . '/../Support/qrcode.php';

class QrCodeService
{
    private int $moduleSize = 8;
    private int $quietZone = 4;

    public function setModuleSize(int $moduleSize): void
    {
        $this->moduleSize = max(4, $moduleSize);
    }

    public function getModuleSize(): int
    {
        return $this->moduleSize;
    }

    public function setQuietZone(int $quietZone): void
    {
        $this->quietZone = max(2, $quietZone);
    }

    public function getQuietZone(): int
    {
        return $this->quietZone;
    }

    public function renderBookIdentity(array $book): void
    {
        $this->renderPng($this->buildPayload($book));
    }

    private function buildPayload(array $book): string
    {
        return implode('|', [
            'PERPUSDIGITAL',
            (string) ($book['id'] ?? ''),
            (string) ($book['isbn'] ?? ''),
            (string) ($book['title'] ?? ''),
            (string) ($book['author'] ?? ''),
        ]);
    }

    private function renderPng(string $payload): void
    {
        $qr = new QRcode($payload, 'M');
        $matrix = $qr->getBarcodeArray();
        $modules = $matrix['bcode'] ?? [];
        $size = count($modules);

        if ($size === 0) {
            http_response_code(500);
            exit('QR gagal dibuat.');
        }

        $canvasSize = ($size + ($this->getQuietZone() * 2)) * $this->getModuleSize();
        $image = imagecreatetruecolor($canvasSize, $canvasSize);

        $background = imagecolorallocate($image, 255, 255, 255);
        $foreground = imagecolorallocate($image, 17, 78, 61);

        imagefill($image, 0, 0, $background);

        foreach ($modules as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                if ((int) $value !== 1) {
                    continue;
                }

                $x1 = ($columnIndex + $this->getQuietZone()) * $this->getModuleSize();
                $y1 = ($rowIndex + $this->getQuietZone()) * $this->getModuleSize();
                $x2 = $x1 + $this->getModuleSize() - 1;
                $y2 = $y1 + $this->getModuleSize() - 1;

                imagefilledrectangle($image, $x1, $y1, $x2, $y2, $foreground);
            }
        }

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
}
