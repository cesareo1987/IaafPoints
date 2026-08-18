<?php

namespace GlaivePro\IaafPoints;

use Generator;
use InvalidArgumentException;
use JsonException;
use RuntimeException;


/**
 * Clase para comparar puntos directamente de la tabla de World Athletics
 * Ubicacion tabla JSON: resources/iaaf/wa-scoring-2025.json
 * Tabla Json creada con ChatGPT usando como fuente 2025.xlsx
 * Errores entre tabla real y funcion extrapolada (no es mayor a 1 punto): wa-scoring-2025-mismatches.csv
 */
final class ReferenceScoringTable
{
    private array $data;

    /** @var array<string, array> */
    private array $events = [];

    public function __construct(string $jsonFile)
    {
        if (!is_file($jsonFile) || !is_readable($jsonFile)) {
            throw new InvalidArgumentException(
                "No se puede leer la tabla de referencia: '$jsonFile'."
            );
        }

        try {
            $data = json_decode(
                file_get_contents($jsonFile),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                "El fichero de referencia no contiene JSON válido.",
                0,
                $e
            );
        }

        if (!isset($data['events']) || !is_array($data['events'])) {
            throw new RuntimeException(
                "El fichero de referencia no contiene la clave 'events'."
            );
        }

        $this->data = $data;

        foreach ($data['events'] as $event) {
            $this->events[$this->eventKey(
                $event['gender'],
                $event['event_id']
            )] = $event;
        }
    }

    public function getEdition(): string
    {
        return (string) ($this->data['edition'] ?? '');
    }

    public function getEvent(string $gender, string $eventId): array
    {
        $key = $this->eventKey($gender, $eventId);

        if (!isset($this->events[$key])) {
            throw new InvalidArgumentException(
                "Evento no encontrado en la tabla: $gender / $eventId."
            );
        }

        return $this->events[$key];
    }

    /**
     * Devuelve la marca publicada para un número de puntos concreto.
     *
     * Devuelve null si esa fila está vacía en la tabla oficial. Esto es
     * importante en pruebas donde la precisión de la marca hace que algunos
     * valores de puntos no sean alcanzables (por ejemplo, en 100m).
     */
    public function getMark(
        string $gender,
        string $eventId,
        int $points
    ): ?string {
        if ($points < 1 || $points > 1400) {
            throw new InvalidArgumentException(
                "Los puntos deben estar entre 1 y 1400."
            );
        }

        $event = $this->getEvent($gender, $eventId);

        $mark = $event['marks'][(string) $points] ?? null;

        return $mark === null ? null : (string) $mark;
    }

    /**
     * Busca los puntos de una marca exactamente tal como aparece publicada.
     * Esta es la puntuacion real y difiere en 880 de 217'749 marcas 
     * Esta funcion no se puede usar para calcular puntos de marcas que no aparecen 
     * exactamente en la tabla (no busca la siguiente marca mas cercana)
     */
    public function findPoints(
        string $gender,
        string $eventId,
        string|int|float $mark
    ): ?int {
        $event = $this->getEvent($gender, $eventId);
        $needle = trim((string) $mark);

        foreach ($event['marks'] as $points => $candidate) {
            if ($candidate !== null && trim((string) $candidate) === $needle) {
                return (int) $points;
            }
        }

        return null;
    }

    /**
     * Recorre únicamente las celdas que contienen una marca publicada.
     * Las celdas vacías del Excel se omiten deliberadamente.
     */
    public function entries(): Generator
    {
        foreach ($this->data['events'] as $event) {
            foreach ($event['marks'] as $points => $mark) {
                if ($mark === null) {
                    continue;
                }

                yield [
                    'gender' => $event['gender'],
                    'event_id' => $event['event_id'],
                    'discipline' => $event['discipline'],
                    'trackType' => $event['trackType'],
                    'category' => $event['category'],
                    'sourceLabel' => $event['sourceLabel'],
                    'measurement' => $event['measurement'],
                    'points' => (int) $points,
                    'mark' => (string) $mark,
                ];
            }
        }
    }

    private function eventKey(string $gender, string $eventId): string
    {
        return strtolower($gender) . '|' . $eventId;
    }
}
