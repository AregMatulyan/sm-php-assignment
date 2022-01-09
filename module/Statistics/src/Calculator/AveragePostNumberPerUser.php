<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostNumberPerUser extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $users = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();

        $this->users[$key] = ($this->users[$key] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $value = count($this->users) > 0
            ? array_sum($this->users) / count($this->users)
            : 0;

        return (new StatisticsTo())->setValue(round($value,2));

    }
}
