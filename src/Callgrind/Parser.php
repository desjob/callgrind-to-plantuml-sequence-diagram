<?php

namespace CallgrindToPlantUML\Callgrind;

class Parser
{
    /** @var string */
    private $callGrind;

    private $functionNames = array();
    private $classNames = array();
    private $eventCallsParsed = false;

    const FUNCTION_CALL_REGEX = '/^fn=\(([0-9]+)\)\s*([a-zA-Z0-9_{}\\\]*)(->|::)?([a-zA-Z-_]*)/';
    const FUNCTION_SUB_CALL_REGEX = '/^cfn=\(([0-9]+)\)/';
    const EMPTY_LINE_REGEX = '/^\s*$/';
    const EVENTS_AND_SUMMARY_SPLIT_REGEX = '/\s+events: Time\s+|\s+summary:\s+[0-9]+\s+/';
    const NEW_LINE_SPLIT_REGEX = "/((\r?\n)|(\r\n?))/";
    const PHP_MAIN = 'php';

    public function __construct(string $callGrind)
    {
        $this->callGrind = $callGrind;
    }

    /**
     * @return \CallgrindToPlantUML\Callgrind\Call[]
     */
    public function getEventCalls(): array
    {
        $split = preg_split(self::EVENTS_AND_SUMMARY_SPLIT_REGEX, $this->callGrind);
        $eventSegment = $split[1];

        $lookForSubCalls = false;
        $eventCalls = array();

        foreach (preg_split(self::NEW_LINE_SPLIT_REGEX, $eventSegment) as $line) {

            if (!$lookForSubCalls) {
                if ($call = $this->matchCall($line)) {
                    $eventCalls[] = $call;
                    $lookForSubCalls = true;
                }

            } else {
                if ($this->matchedEmptyLine($line)) {
                    $lookForSubCalls = false;
                    continue;
                }
                if ($subCallId = $this->matchSubCallId($line)) {
                    $call->addSubCallId($subCallId);
                }
            }
        }

        $this->eventCallsParsed = true;

        return $eventCalls;
    }

    /**
     * @return \CallgrindToPlantUML\Callgrind\Call[]
     */
    public function getSummaryCalls(): array
    {
        if(!$this->eventCallsParsed) {

            throw new \RuntimeException('cannot parse summary calls before parsing event calls.');
        }

        $split = preg_split(self::EVENTS_AND_SUMMARY_SPLIT_REGEX, $this->callGrind);
        $summarySegment = $split[2];

        $summaryCalls = array();
        foreach (preg_split(self::NEW_LINE_SPLIT_REGEX, $summarySegment) as $line) {

            if($call = $this->matchSubCall($line)) {
                $summaryCalls[] = $call;
            }
        }

        return $summaryCalls;
    }

    /**
     * @param string $line
     *
     * @return \CallgrindToPlantUML\Callgrind\Call|null
     */
    private function matchSubCall($line)
    {
        if (!preg_match(self::FUNCTION_SUB_CALL_REGEX, $line, $matches)) {
            return;
        }

        return new Call($matches[1], $this->getCachedClassName($matches[1]), $this->getCachedFunctionName($matches[1]));
    }

    /**
     * @param string $line
     *
     * @return int|null
     */
    private function matchSubCallId($line)
    {
        if (!preg_match(self::FUNCTION_SUB_CALL_REGEX, $line, $matches)) {
            return;
        }

        return $matches[1];
    }

    /**
     * Any initial class call will return matches:
     *  index 1 = function id
     *  index 2 = class name
     *  index 4 = method name
     *
     * A function call (not in a class) in the global namespace call will return matches:
     *  index 1 = function id
     *  index 2 = method name
     *  index 4 = [EMPTY STRING]
     *
     * Any follow up class call to the same method will return matches:
     *  index 1 = function id
     *  index 2 = [EMPTY STRING]
     *  index 4 = [EMPTY STRING]
     *
     * @todo: function in a namespace but not in a class
     *
     * @param $line
     *
     * @return null|\CallgrindToPlantUML\Callgrind\Call
     */
    private function matchCall($line)
    {
        if (!preg_match(self::FUNCTION_CALL_REGEX, $line, $matches)) {
            return;
        }

        if (isset($matches[2]) && strlen($matches[2]) && isset($matches[4]) && strlen($matches[4])) {
            // initial class call
            $call = new Call($matches[1], $matches[2], $matches[4]);
            $this->cacheCallData($call);
        } elseif (isset($matches[2]) && strlen($matches[2])) {
            // global function call
            $call = new Call($matches[1], self::PHP_MAIN, $matches[2]);
            $this->cacheCallData($call);
        } else {
            try {
                // follow up class call
                $call = new Call(
                    $matches[1],
                    $this->getCachedClassName($matches[1]),
                    $this->getCachedFunctionName($matches[1])
                );
            } catch (\RuntimeException $exception) {

                var_dump($line, $matches);
                die;
            }

        }

        return $call;
    }

    private function matchedEmptyLine($line)
    {
        if (preg_match(self::EMPTY_LINE_REGEX, $line)) {
            return true;
        }

        return false;
    }

    /**
     * Cache call class + method name based on function ID for follow up calls
     *
     * @param \CallgrindToPlantUML\Callgrind\Call $call
     */
    private function cacheCallData(Call $call)
    {
        $this->classNames[$call->getId()] = $call->getToClass();
        $this->functionNames[$call->getId()] = $call->getMethod();
    }

    private function getCachedClassName(int $functionId): string
    {
        if (!isset($this->classNames[$functionId])) {
            throw new \RuntimeException('function id ' . $functionId[1] . ' was referenced, but never defined in class name cache');
        }

        return $this->classNames[$functionId];
    }

    private function getCachedFunctionName(int $functionId): string
    {
        if (!isset($this->functionNames[$functionId])) {
            throw new \RuntimeException('function id ' . $functionId[1] . ' was referenced, but never defined in function name cache');
        }

        return $this->functionNames[$functionId];
    }
}
