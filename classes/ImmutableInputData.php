<?php

namespace Rhino\InputData;

class ImmutableInputData extends InputData
{
    use MutateData;

    public function mutateData($data)
    {
        return new static($data);
    }
}
