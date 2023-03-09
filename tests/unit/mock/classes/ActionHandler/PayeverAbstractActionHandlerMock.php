<?php

/**
 * @codeCoverageIgnore
 */
class PayeverAbstractActionHandlerMock extends PayeverAbstractActionHandler
{
    /** @var callable|null */
    public $processCallback;

    /**
     * {@inheritDoc}
     */
    public function getSupportedAction()
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function process($entity)
    {
        if (is_callable($this->processCallback)) {
            call_user_func($this->processCallback);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function incrementActionResult()
    {
    }
}
