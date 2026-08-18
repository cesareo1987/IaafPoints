<?php

use GlaivePro\IaafPoints\IaafCalculator;
use GlaivePro\IaafPoints\PerformanceParser;
use GlaivePro\IaafPoints\ReferenceScoringTable;
use PHPUnit\Framework\TestCase;

final class OfficialScoringTable2025Test extends TestCase
{
    private const MAX_MISMATCHES_IN_MESSAGE = 25;

    public function testReferenceTableLookup(): void
    {
        $table = $this->table();

        $this->assertSame('2025', $table->getEdition());
        $this->assertSame('9.46', $table->getMark('m', '100m', 1400));
        $this->assertSame(1400, $table->findPoints('m', '100m', '9.46'));

        // En 100 m no hay necesariamente una marca centesimal diferente
        // para cada punto. El hueco del documento debe conservarse como null.
        $this->assertNull($table->getMark('m', '100m', 1399));
    }

    /**
     * Este test comprueba exclusivamente PerformanceParser.
     * La conversión esperada se obtiene con un parser mínimo independiente,
     * definido al final de esta clase, para evitar comprobar código consigo mismo.
     */
    public function testPerformanceParserUnderstandsEveryPublishedTimeMark(): void
    {
        $table = $this->table();
        $mismatches = [];
        $checked = 0;

        foreach ($table->entries() as $entry) {
            if ($entry['measurement'] !== 'time') {
                continue;
            }

            $expected = $this->referenceTimeToSeconds($entry['mark']);
            $actual = PerformanceParser::parse($entry['mark']);
            ++$checked;

            if (abs($actual - $expected) > 0.000001) {
                $mismatches[] = sprintf(
                    '%s %-20s | marca=%s | esperado=%s | parser=%s',
                    $entry['gender'],
                    $entry['event_id'],
                    $entry['mark'],
                    (string) $expected,
                    (string) $actual
                );
            }
        }

        $this->assertNoMismatches(
            $mismatches,
            $checked,
            'formatos temporales'
        );
    }

    /**
     * Este test comprueba exclusivamente IaafCalculator contra la tabla
     * transcrita de World Athletics. Para las marcas temporales NO utiliza
     * PerformanceParser, de forma que un error del parser no se confunda con
     * un error en resultShift / conversionFactor / pointShift.
     */
    public function testAllPublished2025MarksMatchIaafCalculator(): void
    {
        $table = $this->table();

        /** @var array<string, IaafCalculator> $calculators */
        $calculators = [];
        $mismatches = [];
        $checked = 0;

        foreach ($table->entries() as $entry) {
            $calculatorKey = implode('|', [
                $entry['gender'],
                $entry['discipline'],
                $entry['trackType'],
            ]);

            if (!isset($calculators[$calculatorKey])) {
                $calculators[$calculatorKey] = new IaafCalculator([
                    'edition' => '2025',
                    'gender' => $entry['gender'],
                    'trackType' => $entry['trackType'],
                    'discipline' => $entry['discipline'],
                ]);
            }

            $result = match ($entry['measurement']) {
                'time' => $this->referenceTimeToSeconds($entry['mark']),
                'distance', 'points' => (float) $entry['mark'],
                default => throw new RuntimeException(
                    "Tipo de medida desconocido: {$entry['measurement']}"
                ),
            };

            $actual = (int) $calculators[$calculatorKey]->evaluate($result);
            $expected = $entry['points'];
            ++$checked;

            if ($actual !== $expected) {
                $mismatches[] = sprintf(
                    '%s %-20s %-5s | tabla=%4d | calculado=%4s | marca=%s | normalizada=%s',
                    $entry['gender'],
                    $entry['event_id'],
                    $entry['trackType'],
                    $expected,
                    var_export($actual, true),
                    $entry['mark'],
                    (string) $result
                );
            }
        }

        $this->assertNoMismatches(
            $mismatches,
            $checked,
            'marcas publicadas'
        );
    }

    private function table(): ReferenceScoringTable
    {
        return new ReferenceScoringTable(
            'resources/iaaf/wa-scoring-2025.json'
        );
    }

    /**
     * Parser deliberadamente pequeño y exclusivo del test de referencia.
     * Entiende los formatos que aparecen en las tablas suministradas:
     * ss.cc, mm:ss, mm:ss.cc, hh:mm:ss y hh:mm:ss.cc.
     * Uso: leer marcas string de la tabla real
     */
    private function referenceTimeToSeconds(string $time): float
    {
        $parts = explode(':', str_replace(',', '.', trim($time)));

        return match (count($parts)) {
            1 => (float) $parts[0],
            2 => ((int) $parts[0] * 60) + (float) $parts[1],
            3 => ((int) $parts[0] * 3600)
                + ((int) $parts[1] * 60)
                + (float) $parts[2],
            default => throw new RuntimeException(
                "Formato temporal inesperado en la tabla: '$time'."
            ),
        };
    }

    private function assertNoMismatches(
        array $mismatches,
        int $checked,
        string $description
    ): void {
        if ($mismatches === []) {
            $this->addToAssertionCount($checked);
            return;
        }

        $shown = array_slice(
            $mismatches,
            0,
            self::MAX_MISMATCHES_IN_MESSAGE
        );

        $message = sprintf(
            "Se han comprobado %d %s y hay %d discrepancias.\n\n%s",
            $checked,
            $description,
            count($mismatches),
            implode("\n", $shown)
        );

        if (count($mismatches) > count($shown)) {
            $message .= sprintf(
                "\n\n... y %d discrepancias más.",
                count($mismatches) - count($shown)
            );
        }

        $this->fail($message);
    }
}
