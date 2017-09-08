<?php

namespace CallgrindToPlantUML\Callgrind;

class Parser
{
    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $eventCalls;

    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $summaryCalls;

    /** @var \CallgrindToPlantUML\Callgrind\Call */
    private $mainCall;

    private $functionNames = array();
    private $classNames = array();
    private $eventCallsParsed = false;

    const FUNCTION_CALL_REGEX = '/^fn=\(([0-9]+)\)\s*([a-zA-Z0-9_{}\\\]*)(->|::)?([a-zA-Z-_]*)/';
    const FUNCTION_SUB_CALL_REGEX = '/^cfn=\(([0-9]+)\)/';
    const EMPTY_LINE_REGEX = '/^\s*$/';
    const EVENTS_AND_SUMMARY_SPLIT_REGEX = '/\s+events: Time\s+|\s+summary:\s+[0-9]+\s+/';
    const NEW_LINE_SPLIT_REGEX = "/((\r?\n)|(\r\n?))/";
    const PHP_MAIN = 'php';

    /**
     * @param string $callGrind
     */
    public function __construct()
    {
        $this->eventCalls = [];
        $this->summaryCalls = [];
    }

    /**
     * @return \CallgrindToPlantUML\Callgrind\Call[]
     */
    public function getEventCalls(): array
    {
        return $this->eventCalls;
    }

    /**
     * @return \CallgrindToPlantUML\Callgrind\Call[]
     */
    public function getSummaryCalls(): array
    {
        return $this->summaryCalls;
    }

    /**
     * @param string $fileName
     */
    public function parseFile(string $fileName)
    {
        $isEvent = false;
        $isSummary = false;
        $lookForSubCalls = false;
        $i = 0;
        $previousLine = '';
        $atLeastOneSummaryCallAdded = false;
        if ($handle = fopen($fileName, "r")) {
            while (($line = fgets($handle)) !== FALSE) {
                ++$i;
                $line = trim($line);

                if ($isEvent) {
                    $lookForSubCalls = $this->addEventCall($line, $previousLine, $lookForSubCalls);
                }
                if ($isSummary) {
                    if ($this->addSummaryCall($line)) {
                        $atLeastOneSummaryCallAdded = true;
                    }
                }

                if ($this->isEvent($line) || ($isSummary && empty($line) && $atLeastOneSummaryCallAdded)) {
                    $atLeastOneSummaryCallAdded = false;
                    $isEvent = true;
                    $isSummary = false;
                }
                if ($this->isSummary($line, $i)) {
                    $isEvent = false;
                    $isSummary = true;
                }
                $previousLine = $line;
            }
        }
    }

    /**
     * @param string $line
     *
     * @return bool
     */
    private function isEvent(string $line): bool
    {
        return $line === 'events: Time';
    }

    /**
     * Main summary starting point is {main} but there can be more calls with same id.
     *
     * @param string $line
     *
     * @return bool
     */
    private function isSummary(string $line, int $i): bool
    {
        if (strpos($line, '{main}') !== false) {
            $this->mainCall = $this->getCall($line);

            return true;
        }
        if (null !== $this->mainCall && $line === 'fn=(' . $this->mainCall->getId() . ')') {
            return true;
        }

        return false;
    }

    /**
     * @param string $line
     * @param string $previousLine
     * @param bool $lookForSubCalls
     *
     * @return bool
     */
    public function addEventCall(string $line, string $previousLine, bool $lookForSubCalls): bool
    {
        // New group of calls.
        if (!$lookForSubCalls) {
            if ($call = $this->getCall($line)) {
                $this->eventCalls[] = $call;
                $lookForSubCalls = true;
            }
        } else {
            // Group only ends if current line is empty and previous line is the cost.
            if (empty($line)) {
                return !preg_match('/^[0-9]+\s[0-9]+/', $previousLine, $matches);
            }
            // If not, add as subcall of group call.
            if ($subCallId = $this->getSubCallId($line)) {
                end($this->eventCalls)->addSubCallId($subCallId);
            }
        }

        return $lookForSubCalls;
    }

    /**
     * @param string $line
     *
     * @eturn bool
     */
    public function addSummaryCall(string $line): bool
    {
        if (!empty($line) && $call = $this->getSubCall($line)) {
            $this->summaryCalls[] = $call;

            return true;
        }

        return false;
    }

    /**
     * @param string $line
     *
     * @return \CallgrindToPlantUML\Callgrind\Call|null
     */
    private function getSubCall($line)
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
    private function getSubCallId($line)
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
     * A function call (not in a class) in the global OR custom namespace call will return matches:
     *  index 1 = function id
     *  index 2 = method name
     *  index 4 = [EMPTY STRING]
     *
     * Any follow up class call to the same method will return matches:
     *  index 1 = function id
     *  index 2 = [EMPTY STRING]
     *  index 4 = [EMPTY STRING]
     *
     * @param $line
     *
     * @return null|\CallgrindToPlantUML\Callgrind\Call
     */
    private function getCall($line)
    {
        if (!preg_match(self::FUNCTION_CALL_REGEX, $line, $matches)) {
            return;
        }

        if (isset($matches[2]) && strlen($matches[2]) && isset($matches[4]) && strlen($matches[4])) {
            // initial class call
            $call = new Call($matches[1], $matches[2], $matches[4]);
            $this->cacheCallData($call);
        } elseif (isset($matches[2]) && strlen($matches[2])) {
            // global OR namespaced function call
            // could be that the "global" function resides in a namespace, in that case we want to remove the namespace
            $nameSpacedFunctionName = $matches[2];
            $namespaceParts = explode('\\', $nameSpacedFunctionName);
            $functionName = end($namespaceParts);
            $call = new Call($matches[1], self::PHP_MAIN, $functionName);
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

    /**
     * @param int $functionId
     *
     * @return string
     */
    private function getCachedClassName(int $functionId): string
    {
        if (!isset($this->classNames[$functionId])) {
            throw new \RuntimeException('function id ' . $functionId . ' was referenced, but never defined in class name cache');
        }

        return $this->classNames[$functionId];
    }

    /**
     * @param int $functionId
     *
     * @return string
     */
    private function getCachedFunctionName(int $functionId): string
    {
        if (!isset($this->functionNames[$functionId])) {
            throw new \RuntimeException('function id ' . $functionId . ' was referenced, but never defined in function name cache');
        }

        return $this->functionNames[$functionId];
    }
}
