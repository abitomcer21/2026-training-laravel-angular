<?php

namespace App\Products\Domain\ValueObject;

class ProductImageSrc
{
    private const MAX_LENGTH = 255;

    private ?string $path;

    private function __construct(?string $path)
    {
        // Permitir null
        if ($path === null) {
            $this->path = null;
            return;
        }

        $trimmedPath = trim($path);

        // Permitir string vacío (será null)
        if ($trimmedPath === '') {
            $this->path = null;
            return;
        }

        if (mb_strlen($trimmedPath) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Image path cannot exceed %d characters.', self::MAX_LENGTH)
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
