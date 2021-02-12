<?php

declare(strict_types=1);

namespace Rhino\InputData;

class ImmutableInputData extends InputData
{
    use MutateData;

    protected function mutateData($data)
    {
        return new static($data);
    }
}
