<?php

namespace Softspring\Component\CrudlController\Exception;

class EmptyConfigException extends \InvalidArgumentException
{
    public function __construct(string $action, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(ucfirst($action).' action configuration is empty', $code, $previous);
    }
}
