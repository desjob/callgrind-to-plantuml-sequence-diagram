<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\SequenceDiagram\Call;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

class StartFromFilter implements FilterInterface
{
    /** @var string */
    private $toClass;

    /** @var string */
    private $method;

    /** @var bool */
    private $startAdding;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Call */
    private $filteringFromCall;

    /**
     * @param string $toClass
     * @param string $method
     */
    public function __construct(string $toClass, string $method)
    {
        $this->toClass = str_replace('.', '\\', $toClass);
        $this->method = str_replace('()', '', $method);
        $this->startAdding = false;
    }

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
//    public function apply(Sequence $sequence): Sequence
//    {
//        $startAdding = false;
//        $startedFromCall = null;
//        $filteredSequence = new Sequence();
//        while ($sequence->hasItems()) {
//            $call = $sequence->pop();
//
//            // check if we need to start adding yet
//            if(!$startAdding && $call->getToClass() === $this->toClass && $call->getMethod() === $this->method) {
//                $startAdding = true;
//                $startedFromCall = $call;
//            }
//
//            if(!$startAdding) {
//                continue;
//            } else {
//                $filteredSequence->add($call);
//
//                //once we reach the returncall of the $startedFromCall, stop adding
//                if($call->isReturnCall() && $call->getToClass() === $startedFromCall->getFromClass() && $call->getFromClass() === $startedFromCall->getToClass()) {
//                    $startAdding = false;
//                    $startedFromCall = null;
//                }
//            }
//        }
//
//        return $filteredSequence;
//    }

    /**
     * If before start from class::method, return false, if not, return true.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    public function isCallValid(Call $call, int $i): bool
    {
        if (!$this->startAdding) {
            $this->writeLog($i, 'not startAdding');
            $this->writeLog($i, $call->getToClass() . ' -> ' . $call->getMethod());
            if ($call->getToClass() === $this->toClass && ($this->method === null || $call->getMethod() === $this->method)) {
                $this->writeLog($i, 'startAdding = true');
                $this->startAdding = true;
                $this->filteringFromCall = $call;
            }
        } else {
            $this->writeLog($i, 'startAdding');
            $this->writeLog($i, ($call->isReturnCall() ? 'return' : 'not return') . ' From: ' . $call->getFromClass() . ' To:' . $call->getToClass());
            if ($call->isReturnCall() &&
                $call->getFromClass() === $this->filteringFromCall->getToClass() &&
                $call->getToClass() === $this->filteringFromCall->getFromClass()
            ) {
                $this->writeLog($i, 'startAdding = false');
                $this->startAdding = false;
                $this->filteringFromCall = null;
            }
        }

        return $this->startAdding;
    }

    private function writeLog(int $i, string $text)
    {
//        if ($i >= 88278 && $i <= 88303) {
//            error_log($text);
//        }
    }
}
