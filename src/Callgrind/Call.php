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

    /**
     * @param int $id
     * @param string $toClass
     * @param string $method
     * @param array $subCallIds
     */
    public function __construct(int $id, string $toClass, string $method, array $subCallIds = array())
    {
        $this->id = $id;
        $this->toClass = $toClass;
        $this->method = $method;
        $this->subCallIds = $subCallIds;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return array
     */
    public function getSubCallIds(): array
    {
        return $this->subCallIds;
    }

    /**
     * @param int $callId
     */
    public function addSubCallId(int $callId)
    {
        $this->subCallIds[] = $callId;
    }
}
