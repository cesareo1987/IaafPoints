<?php

namespace GlaivePro\IaafPoints\Fam;

use GlaivePro\IaafPoints\IaafCalculator;
use InvalidArgumentException;

class EquivalentDistanceCalculator
{
    private array $distances;

    private array $options;

    private string $discipline;

    public function __construct(array $options)
    {
        $this->distances = require __DIR__
            . '/../../resources/fam/equivalent_distances.php';

        if (
            !isset($options['discipline'])
            || !is_string($options['discipline'])
        ) {
            throw new InvalidArgumentException(
                'The discipline option is required.'
            );
        }

        $this->discipline = $options['discipline'];

        if (!isset($this->distances[$this->discipline])) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported equivalent discipline: %s',
                    $this->discipline
                )
            );
        }

        $this->options = $options;

        unset($this->options['discipline']); // IaafCalculator no lo necesita
    }

    /**
     * Returns the equivalent World Athletics discipline and result.
     */
    public function equivalent(float $result): array
    {
        $config = $this->distances[$this->discipline];

        $equivalentResult = $result
            * (
                $config['referenceDistance']
                / $config['distance']
            );

        return [
            'discipline' => $config['referenceDiscipline'],
            'result' => $equivalentResult,
        ];
    }

    /**
     * Calculates points using the equivalent
     * World Athletics discipline.
     */
    public function evaluate(float $result): ?int
    {
        $equivalent = $this->equivalent($result);

        $options = $this->options;

        $options['discipline'] = $equivalent['discipline'];

        $calculator = new IaafCalculator($options);

        $points = $calculator->evaluate(
            $equivalent['result']
        );

        return $points === null
            ? null
            : (int) $points;
    }
}