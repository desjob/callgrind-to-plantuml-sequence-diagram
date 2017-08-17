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

    /**
     * @param string $fromClass
     * @param string $toClass
     * @param string $method
     */
    public function __construct(string $fromClass, string $toClass, string $method)
    {
        $this->fromClass = $fromClass;
        $this->toClass = $toClass;
        $this->method = $method;
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
}
