<?php

use GlaivePro\IaafPoints\IaafCalculator;
use PHPUnit\Framework\TestCase;

final class CalculateResultsFromPointsTest extends TestCase
{
    
    public function testCanCalculateResultFromPoints(): void
    {
        $calculator = new IaafCalculator([
            'edition' => '2025',
            'gender' => 'm',
            'trackType' => 'long',
            'discipline' => '200m',
            'electronicMeasurement' => true,
        ]);

        $mark = $calculator->resultFromPoints(980, 2);

        $this->assertEqualsWithDelta(
            21.61,
            $mark,
            0.000001
        );

        $this->assertSame(
            980,
            (int) $calculator->evaluate($mark)
        );
    }

    public function testReturnsNullWhenPointsHaveNoExactMark(): void
    {
        $calculator = new IaafCalculator([
            'edition' => '2025',
            'gender' => 'm',
            'trackType' => 'long',
            'discipline' => '100m',
            'electronicMeasurement' => true,
        ]);

        $this->assertNull(
            $calculator->resultFromPoints(939, 2)
        );
    }

}
