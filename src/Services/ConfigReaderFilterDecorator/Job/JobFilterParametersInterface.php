<?php

namespace Trivago\Rumi\Services\ConfigReaderFilterDecorator\Job;

interface JobFilterParametersInterface
{
    public function getJobFilter(): string;
}
