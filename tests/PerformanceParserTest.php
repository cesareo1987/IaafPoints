<?php

use GlaivePro\IaafPoints\PerformanceParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PerformanceParserTest extends TestCase
{
    /*
     * ------------------------------------------------------------
     * Marcas expresadas directamente en segundos
     * ------------------------------------------------------------
     */

    public function testSecondsWithCentiseconds(): void
    {
        $this->assertEqualsWithDelta(
            10.83,
            PerformanceParser::parse('10.83'),
            0.0001
        );
    }

    public function testSecondsWithCommaDecimalSeparator(): void
    {
        $this->assertEqualsWithDelta(
            10.83,
            PerformanceParser::parse('10,83'),
            0.0001
        );
    }

    public function testSecondsWithoutDecimals(): void
    {
        $this->assertEqualsWithDelta(
            59.0,
            PerformanceParser::parse('59'),
            0.0001
        );
    }

    public function testSecondsGreaterThanSixtyAreAccepted(): void
    {
        $this->assertEqualsWithDelta(
            108.35,
            PerformanceParser::parse('108.35'),
            0.0001
        );
    }

    public function testIntegerInput(): void
    {
        $this->assertEqualsWithDelta(
            120.0,
            PerformanceParser::parse(120),
            0.0001
        );
    }

    public function testFloatInput(): void
    {
        $this->assertEqualsWithDelta(
            108.35,
            PerformanceParser::parse(108.35),
            0.0001
        );
    }

    /*
     * ------------------------------------------------------------
     * Formato minutos:segundos
     * ------------------------------------------------------------
     */

    public function testMinutesAndSeconds(): void
    {
        $this->assertEqualsWithDelta(
            112.0,
            PerformanceParser::parse('1:52'),
            0.0001
        );
    }

    public function testMinutesSecondsAndCentiseconds(): void
    {
        $this->assertEqualsWithDelta(
            108.35,
            PerformanceParser::parse('1:48.35'),
            0.0001
        );
    }

    public function testMinutesSecondsWithTenths(): void
    {
        $this->assertEqualsWithDelta(
            112.5,
            PerformanceParser::parse('1:52.5'),
            0.0001
        );
    }

    public function testMinutesSecondsWithComma(): void
    {
        $this->assertEqualsWithDelta(
            112.50,
            PerformanceParser::parse('1:52,50'),
            0.0001
        );
    }

    public function testLongMinutesFormat(): void
    {
        $this->assertEqualsWithDelta(
            1650.0,
            PerformanceParser::parse('27:30'),
            0.0001
        );
    }

    public function testMinutesCanExceedSixtyWhenNoHoursAreSpecified(): void
    {
        $this->assertEqualsWithDelta(
            3930.0,
            PerformanceParser::parse('65:30'),
            0.0001
        );
    }

    /*
     * ------------------------------------------------------------
     * Formato horas:minutos:segundos
     * ------------------------------------------------------------
     */

    public function testHoursMinutesAndSeconds(): void
    {
        $this->assertEqualsWithDelta(
            3750.0,
            PerformanceParser::parse('1:02:30'),
            0.0001
        );
    }

    public function testHoursMinutesSecondsWithDecimals(): void
    {
        $this->assertEqualsWithDelta(
            7542.50,
            PerformanceParser::parse('2:05:42.50'),
            0.0001
        );
    }

    public function testVeryLongRace(): void
    {
        $this->assertEqualsWithDelta(
            23720.0,
            PerformanceParser::parse('6:35:20'),
            0.0001
        );
    }

    /*
     * ------------------------------------------------------------
     * Espacios
     * ------------------------------------------------------------
     */

    public function testLeadingAndTrailingSpacesAreIgnored(): void
    {
        $this->assertEqualsWithDelta(
            108.35,
            PerformanceParser::parse('  1:48.35  '),
            0.0001
        );
    }

    /*
     * ------------------------------------------------------------
     * Formatos incorrectos
     * ------------------------------------------------------------
     */

    public function testEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('');
    }

    public function testInvalidTextThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('hola');
    }

    public function testTooManyPartsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('1:02:03:04');
    }

    public function testSecondsCannotReachSixtyInMinutesFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('1:60');
    }

    public function testSecondsCannotExceedSixtyInMinutesFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('1:72.25');
    }

    public function testSecondsCannotReachSixtyInHoursFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('1:10:60');
    }

    public function testMinutesCannotReachSixtyInHoursFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('1:60:00');
    }

    public function testNonNumericMinutesThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('abc:30');
    }

    public function testNonNumericHoursThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('a:05:30');
    }

    public function testMalformedDecimalThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('1:52.3.4');
    }

    public function testNegativeNumericInputThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse(-10.5);
    }

    public function testNegativeStringInputThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PerformanceParser::parse('-10.5');
    }
}