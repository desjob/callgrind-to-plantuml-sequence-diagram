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
    private $subCallIds;

    public function __construct(int $id, string $toClass, string $method, array $subCallIds = array())
    {
        $this->id = $id;
        $this->toClass = $toClass;
        $this->method = $method;
        $this->subCallIds = $subCallIds;
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

    public function getSubCallIds(): array
    {
        return $this->subCallIds;
    }

    public function addSubCallId(int $callId)
    {
        $this->subCallIds[] = $callId;
    }
}
