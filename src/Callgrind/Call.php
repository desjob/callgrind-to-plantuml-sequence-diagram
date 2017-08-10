<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Callgrind;

class Call
{
    /** @var int */
    private $id;

    /** @var string */
    private $toClass;

    /** @var string */
    private $method;

    /** @var array */
    private $calls;

    public function __construct(int $id, string $toClass, string $method, array $calls)
    {
        $this->id = $id;
        $this->toClass = $toClass;
        $this->method = $method;
        $this->calls = $calls;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getToClass(): string
    {
        return $this->toClass;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getCalls(): array
    {
        return $this->calls;
    }
}
