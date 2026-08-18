<?php

namespace GlaivePro\IaafPoints;

use InvalidArgumentException;

final class PerformanceParser
{
    /**
     * Convierte una marca temporal a segundos para calcular puntos WA u otros
     *
     * Formatos aceptados:
     *
     * 10.83        -> 10.83
     * 10,83        -> 10.83
     * 59.9         -> 59.9
     * 108.35       -> 108.35
     *
     * 1:52         -> 112
     * 1:52.50      -> 112.50
     * 
     * 1:52,50      -> 112.50
     * 27:30        -> 1650
     * 65:30        -> 3930
     *
     * 1:02:30      -> 3750
     * 2:05:42      -> 7542
     * 2:05:42.50   -> 7542.50
     */
    public static function parse(string|int|float $time): float
    {
        if (is_int($time) || is_float($time)) {
            if ($time < 0 || !is_finite((float) $time)) {
                throw new InvalidArgumentException(
                    "La marca no es válida."
                );
            }

            return (float) $time;
        }

        $original = $time;

        $time = trim($time);

        if ($time === '') {
            throw new InvalidArgumentException(
                "La marca está vacía."
            );
        }

        // Permitimos decimal español.
        $time = str_replace(',', '.', $time);

        $parts = explode(':', $time);

        if (count($parts) > 3) {
            throw new InvalidArgumentException(
                "Formato de tiempo incorrecto: '$original'."
            );
        }

        /*
         * Si solo hay un bloque significa que la marca esta en segundos
         * Tambien acepta marcas normalizadas: 108.35 s - 1:48.35
         */
        if (count($parts) === 1) {
            if (!preg_match('/^\d+(?:\.\d+)?$/', $parts[0])) {
                throw new InvalidArgumentException(
                    "Marca incorrecta: '$original'."
                );
            }

            return (float) $parts[0]; // ss.cc
        }

        /*
         * Con son dos o tres partes, el último elemento son los segundos 
         * y pueden llevar centesimas
         */
        $secondsText = array_pop($parts);

        if (!preg_match('/^\d+(?:\.\d+)?$/', $secondsText)) {
            throw new InvalidArgumentException(
                "Segundos incorrectos en la marca '$original'."
            );
        }

        $seconds = (float) $secondsText;

        if ($seconds >= 60) {
            throw new InvalidArgumentException(
                "Los segundos deben estar entre 0 y menos de 60."
            );
        }

        /*
         * mm:ss(.decimales)
         */
        if (count($parts) === 1) {
            $minutesText = $parts[0];

            if (!ctype_digit($minutesText)) {
                throw new InvalidArgumentException(
                    "Minutos incorrectos en la marca '$original'."
                );
            }

            $minutes = (int) $minutesText;

            return ($minutes * 60) + $seconds; // mm:ss(.decimales)
        }

        /*
         * hh:mm:ss(.decimales)
         */
        [$hoursText, $minutesText] = $parts;

        if (
            !ctype_digit($hoursText) ||
            !ctype_digit($minutesText)
        ) {
            throw new InvalidArgumentException(
                "Horas o minutos incorrectos en la marca '$original'."
            );
        }

        $hours = (int) $hoursText;
        $minutes = (int) $minutesText;

        if ($minutes >= 60) {
            throw new InvalidArgumentException(
                "Los minutos deben estar entre 00 y 59."
            );
        }

        return ($hours * 3600)
            + ($minutes * 60)
            + $seconds; // hh:mm:ss(.decimales)
    }
}