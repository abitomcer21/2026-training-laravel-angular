<?php

namespace App\Products\Domain\ValueObject;

class ProductImageSrc
{
    private const MAX_LENGTH = 255;

    private ?string $path;

    private function __construct(?string $path)
    {
        if ($path === null) {
            $this->path = null;
            return;
        }

        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            $this->path = null;
            return;
        }

        if (mb_strlen($trimmedPath) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('La ruta de la imagen no puede exceder %d caracteres.', self::MAX_LENGTH)
            );
        }

        $this->path = $trimmedPath;
    }

    public static function create(?string $path): self
    {
        return new self($path);
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function value(): ?string
    {
        return $this->path;
    }
}