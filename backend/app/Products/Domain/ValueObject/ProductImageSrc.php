<?php

namespace App\Products\Domain\ValueObject;

class ProductImageSrc
{
    private const MAX_LENGTH = 255;

    private string $path;

    private function __construct(string $path)
    {
        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            throw new \InvalidArgumentException('Image path cannot be empty.');
        }

        if (mb_strlen($trimmedPath) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Image path cannot exceed %d characters.', self::MAX_LENGTH)
            );
        }

        $this->path = $trimmedPath;
    }

    public static function create(string $path): self
    {
        return new self($path);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function value(): string
    {
        return $this->path;
    }
}
