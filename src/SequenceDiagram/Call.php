<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram;

class Call
{
    /** @var string */
    private $fromClass;

    /** @var string */
    private $toClass;

    /** @var string */
    private $method;

    /** @var bool */
    private $returnCall;

    /**
     * @param string $fromClass
     * @param string $toClass
     * @param string $method
     */
    public function __construct(string $fromClass, string $toClass, string $method, bool $returnCall = false)
    {
        $this->fromClass = $fromClass;
        $this->toClass = $toClass;
        $this->method = $method;
        $this->returnCall = $returnCall;
    }

    /**
     * @return string
     */
    public function getFromClass(): string
    {
        return $this->fromClass;
    }

    /**
     * @return string
     */
    public function getToClass(): string
    {
        return $this->toClass;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    public function isReturnCall(): bool
    {
        return $this->returnCall;
    }
}
