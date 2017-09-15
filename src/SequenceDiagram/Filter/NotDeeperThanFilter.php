<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\SequenceDiagram\Call;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

class NotDeeperThanFilter implements FilterInterface
{
    /** @var string */
    private $toClass;

    /** @var string|null */
    private $method;

    /** @var bool */
    private $deeperThanFilter;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Call */
    private $filteringFromCall;

    /**
     * @param string $toClass
     * @param string|null $method
     */
    public function __construct(string $toClass, string $method = null)
    {
        $this->toClass = str_replace('.', '\\', $toClass);
        if (null !== $method) {
            $this->method = str_replace('()', '', $method);
        }
        $this->deeperThanFilter = false;
    }

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
//    public function apply(Sequence $sequence): Sequence
//    {
//        $filteredSequence = new Sequence();
//        $keepAdding = true;
//        $filteringFromCall = null;
//        while ($sequence->hasItems()) {
//            $call = $sequence->pop();
//            if ($keepAdding) {
//                //not in filtering mode, keep adding everything
//                $filteredSequence->add($call);
//
//                //check if we should start filtering
//                if ($call->getToClass() === $this->toClass) {
//                    //match found, start filtering out until we hit the return call for the current call
//                    $keepAdding = false;
//                    $filteringFromCall = $call;
//                }
//            } else {
//                //check if we are at the return call already
//                if ($call->isReturnCall() &&
//                    $call->getFromClass() === $filteringFromCall->getToClass() &&
//                    $call->getToClass() === $filteringFromCall->getFromClass()
//                ) {
//                    //return call found, turn off the filter
//                    $filteringFromCall = null;
//                    $keepAdding = true;
//
//                    //add the return call
//                    $filteredSequence->add($call);
//                }
//            }
//        }
//
//        return $filteredSequence;
//    }

    /**
     * If we are deeper than the filter class::method, return false, if not, return true.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    public function isCallValid(Call $call, int $i): bool
    {
        if (!$this->deeperThanFilter) {

            $this->writeLog($i, 'NOT deeperThanFilter');
            $this->writeLog($i, $call->getFromClass() . ' ---> ' . $call->getToClass() . ' :: ' . $call->getMethod());
            if ($call->getMethod() !== '{main}') {$this->writeLog($i, 'OK 1');}
            if ($call->getToClass() === $this->toClass) {$this->writeLog($i, 'OK 2');}
            if ($this->method === null || $call->getMethod() === $this->method) {$this->writeLog($i, 'OK 3');}

            if (/*$call->getMethod() !== '{main}' &&*/
                $this->classStartsWith($this->toClass, $call->getFromClass()) &&
                // ($call->getFromClass() === $this->toClass /*|| $call->getToClass() === $this->toClass*/) &&
                ($this->method === null || $call->getMethod() === $this->method)
            ) {
                $this->writeLog($i, 'deeperThanFilter = true');
                $this->deeperThanFilter = true;
                $this->filteringFromCall = $call;
            }
        } else {
            $this->writeLog($i, 'deeperThanFilter');
            if ($call->isReturnCall()) {$this->writeLog($i, 'OK 1');}
            if ($call->getFromClass() === $this->filteringFromCall->getToClass()) {$this->writeLog($i, 'OK 2');}
            if ($call->getToClass() === $this->filteringFromCall->getFromClass()) {$this->writeLog($i, 'OK 3');}
            $this->writeLog($i, ($call->isReturnCall() ? 'return call ' : 'not return call') . ' && ' . $call->getFromClass() . ' vs ' . $this->filteringFromCall->getToClass() . ' && ' . $call->getToClass() . ' && ' . $this->filteringFromCall->getFromClass());

            if ($call->isReturnCall() &&
                $call->getFromClass() === $this->filteringFromCall->getToClass() &&
                $call->getToClass() === $this->filteringFromCall->getFromClass()
            ) {
                $this->writeLog($i, 'deeperThanFilter = false');
                $this->deeperThanFilter = false;
                $this->filteringFromCall = null;

                return $this->deeperThanFilter;
            }
        }

        $this->writeLog($i, 'return: ' . (!$this->deeperThanFilter ? 'yes' : 'no'));
        return !$this->deeperThanFilter;
    }

    /**
     * Allow for partial matches, for example, if I want to say that I do not want to go deeper than everything that
     * is Doctrine instead of having to manually identify all classes.
     *
     * @param string $needle
     * @param string $haystack
     *
     * @return bool
     */
    private function classStartsWith(string $needle, string $haystack): bool
    {
        // If partial filter, check if classes start by same class name.
        if (substr($needle, -1) === '%') {
            $needle = substr($needle, 0, -1);

            return substr($haystack, 0, strlen($needle)) === $needle;
        }

        return $haystack === $needle;
    }

    private function writeLog(int $i, string $text)
    {
//        if ($i >= 1875 && $i <= 1880) {
//            error_log($text);
//        }
    }
}
