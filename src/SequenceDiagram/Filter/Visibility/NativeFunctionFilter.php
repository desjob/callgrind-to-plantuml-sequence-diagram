<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter\Visibility;

use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\SequenceDiagram\Call;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

class NativeFunctionFilter implements FilterInterface
{
    /** @var \CallgrindToPlantUML\SequenceDiagram\Call */
    private $previousCall = null;

    /** @var bool */
    private $hideNextCall = false;

    /**
     * Hide return of native calls with no subcalls.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     */
    public function setCallVisibility(Call $call)
    {
        // If there is no previous call don't need to do anything.
        if (empty($this->previousCall)) {
            $this->previousCall = $call;

            return;
        }

        // If call is the native return call of the previous call, hide call and return call.
        if ($this->previousCall->getToClass() === Parser::PHP_MAIN && $this->isTheFilteringReturnCall($call)) {
            $this->previousCall->setVisible(false);
            $call->setVisible(false);
        }

        $this->previousCall = $call;
    }

    /**
     * Checks if this call is the return call of the previous one.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    private function isTheFilteringReturnCall(Call $call): bool
    {
        return $call->isReturnCall() &&
            $call->getFromClass() === $this->previousCall->getToClass() &&
            $call->getToClass() === $this->previousCall->getFromClass() &&
            $call->getMethod() === $this->previousCall->getMethod();
    }
}
