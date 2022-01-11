<?php

declare(strict_types = 1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Builder\ParamsBuilder;
use Statistics\Calculator\AbstractCalculator;
use Statistics\Calculator\AveragePostNumberPerUser;
use Statistics\Dto\StatisticsTo;
use Statistics\Enum\StatsEnum;
use DateTime;

/**
 * Class ATestTest
 *
 * @covers AveragePostNumberPerUser
 * @package Tests\unit
 */
class AveragePostNumberPerUserTest extends TestCase
{
    /**
     * @var AbstractCalculator|null
     */
    private $averagePostNumberPerUser = null;

    /**
     * @var FictionalPostHydrator|null
     */
    private $fictionalPostHydrator = null;

    /**
     * @return void
     */
    public function testCalculate(): void
    {
        // Given
        $date = DateTime::createFromFormat('Y-m-d', '2018-08-11');

        // When
        $this->setAveragePostNumberPerUser($date);
        $this->accumulateData();
        $statistics = $this->averagePostNumberPerUser->calculate();

        // Then
        $this->assertInstanceOf(StatisticsTo::class, $statistics);
        $this->assertEquals(StatsEnum::AVERAGE_POST_NUMBER_PER_USER, $statistics->getName());
        $this->assertEquals('posts', $statistics->getUnits());
        $this->assertEquals(1, $statistics->getValue());
    }

    /**
     * Retrieves posts from the mocked response file
     *
     * @return array
     */
    private function getPosts(): array
    {
        $filePath = '/app/tests/data/social-posts-response.json';
        $posts = [];

        if (! file_exists($filePath)) {
            return $posts;
        }

        $fileContent = file_get_contents($filePath, true);
        $mockedResponse = json_decode($fileContent, true);

        if (! isset($mockedResponse['data']['posts'])) {
            return $posts;
        }

        return $mockedResponse['data']['posts'];
    }

    /**
     * Creates the AveragePostNumberPerUser calculator for given month
     *
     * @param DateTime $date
     * @return void
     */
    private function setAveragePostNumberPerUser(DateTime $date): void
    {
        $paramBuilder = new ParamsBuilder();
        $params = $paramBuilder::reportStatsParams($date);
        $paramTo = null;

        array_filter($params, function ($param) use (&$paramTo) {
            if ($param->getStatName() === StatsEnum::AVERAGE_POST_NUMBER_PER_USER) {
                $paramTo = $param;
            }
        });

        $this->averagePostNumberPerUser = new AveragePostNumberPerUser();
        $this->averagePostNumberPerUser->setParameters($paramTo);
    }

    /**
     * @return void
     */
    private function accumulateData(): void
    {
        foreach ($this->getPosts() as $postData) {
            $socialPostTo = $this->fictionalPostHydrator->hydrate($postData);
            $this->averagePostNumberPerUser->accumulateData($socialPostTo);
        }
    }

    /**
     * @access protected
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fictionalPostHydrator = new FictionalPostHydrator();
    }

    /**
     * @access protected
     * @return void
     */
    protected function tearDown(): void
    {
        $this->averagePostNumberPerUser = null;
        $this->fictionalPostHydrator = null;

        parent::tearDown();
    }
}
