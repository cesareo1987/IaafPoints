<?php

/**
 * Fichero para pruebas rápidas
 * Como pasar test PHPUnit especifico: php .\vendor\bin\phpunit .\tests\...Test.php
 */

require __DIR__ . '/vendor/autoload.php';

use GlaivePro\IaafPoints\IaafCalculator;
use GlaivePro\IaafPoints\PerformanceParser;
use GlaivePro\IaafPoints\ReferenceScoringTable;
use GlaivePro\IaafPoints\Fam\EquivalentDistanceCalculator;

$calculator = new IaafCalculator([
    'edition'    => '2025',
    'gender'     => 'm',
    'trackType'  => 'long',
    'discipline' => '3000mW',
    'electronicMeasurement' => true,
]);

// Calculadora especifica marca -> puntos
$marca_string = "12:31.01";
$marca = PerformanceParser::parse($marca_string);
$puntos = $calculator->evaluate($marca);

printf(
    "Puntos para prueba y marca especifica: %s = %6.2f s -> %4d puntos\n",
    $marca_string, 
    $marca,
    $puntos
);


echo "\n";


// Busqueda concreta contra la tabla real en JSON
$table = new ReferenceScoringTable(
     'resources/iaaf/wa-scoring-2025.json'
);

$gender = 'm';
$event = 'long_jump';
$points = 1399;
$marca = $table->getMark(
    $gender,
    $event,
    $points
);
printf(
    "Busqueda en tabla real: %s y %d puntos -> %.2f como marca\n",
    $event,
    $puntos,
    $marca
);

$marca2 = 8.65;
$puntos = $table->findPoints($gender, $event, $marca2);
printf(
    "Busqueda en tabla real: %s y %.2f como marca -> %d puntos\n",
    $event,
    $marca2,
    $puntos
);


echo "\n";


// Calculo de marca inversa mediante formula:
$points = 980;

$mark = $calculator->resultFromPoints($points, 2);

echo "Puntos: {$points}\n";
echo "Marca: " . number_format($mark, 2, '.', '') . "\n";
echo "Comprobación: " . $calculator->evaluate($mark) . " puntos\n";

echo "\n";

// Calculo de eventos que no existen en las tablas IAAF
$calculator = new EquivalentDistanceCalculator([
    'edition' => '2025',
    'gender' => 'm',
    'trackType' => 'long',
    'discipline' => '2000mW',
    'electronicMeasurement' => true,
]);

$marca = PerformanceParser::parse("10:00.00");
$points = $calculator->evaluate($marca);

echo $points;

$equivalent = $calculator->equivalent($marca);

print_r($equivalent);