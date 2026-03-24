<?php

namespace App\Products\Domain\ValueObject;

class ImageSrc
{
    private string $path;

    private function __construct(string $path)
    {
        if (trim($path) === '') {
            throw new \InvalidArgumentException('Image path cannot be empty.');
        }
        $this->path = trim($path);
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
