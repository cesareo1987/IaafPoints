<?php

namespace GlaivePro\IaafPoints;

class IaafCalculator extends Support\Calculator
{
	protected const RESOURCES = 'iaaf';

	/**
	 * @param array $options Allowed keys:
	 *  + edition - string - use getSupportedEditionKeys() method for array of available options
	 *  + gender - 'f' or 'm'
	 *  + electronicMeasurement = true or false
	 *  + venueType - 'indoor' or 'outdoor' --- for 2017 and 2022 editions only
	 *  + trackType - 'long' or 'short' --- for 2025 and later editions only
	 *	+ discipline - string - after setting other options use getSupportedDisciplineKeys() method for array of available options
	 */
	protected $options = [
		'discipline' => null,
		'gender' => 'm',
		'electronicMeasurement' => true,
		'venueType' => 'outdoor',
		'trackType' => 'long',
		'edition' => '2022',
	];

	protected $resultShift;
	protected $conversionFactor;
	protected $pointShift;

	protected function loadData(): void
	{
		$this->resultShift = $this->conversionFactor = $this->pointShift = null;

		$discipline = $this->options['discipline'];

		if (!$discipline)
			return;

		$constants = $this->constants();

		// short track twins like `200m_short` exist only where the track length matters
		if ('short' === $this->options['trackType'] && isset($constants[$discipline.'_short']))
			$discipline .= '_short';

		$constants = $constants[$discipline] ?? null;

		if (!$constants)
			return;

		$this->resultShift = $constants['resultShift'];
		$this->conversionFactor = $constants['conversionFactor'];
		$this->pointShift = $constants['pointShift'];
	}

	/**
	 * Calculate points for given result.
	 *
	 * @param float $result Result in meters, seconds or points (depending on discipline).
	 *
	 * @return int
	 */
	public function evaluate($result)
	{
		if (!$result)
			return null;

		if (null === $this->resultShift || null === $this->conversionFactor || null === $this->pointShift)
			return null;

		if (!$this->options['electronicMeasurement'])  //hand time corrections
		{
			// the correction is the same whether the track is short or long
			$discipline = preg_replace('/_short$/', '', $this->options['discipline']);

			// For sprints & hurdles up to 200m
			if (in_array($discipline, ['50m', '55m', '60m', '100m', '200m', '50mh', '55mh', '60mh', '100mh', '110mh']))
				$result += 0.24;

			/**
			 * For sprints & hurdles up to 400m
			 *
			 * Note that the WA Scoring tables' book instructs to only apply
			 * this correction 300m, 400m, 400mh, but the author of this
			 * package got confirmation from WA via email on 2026-06-01 that
			 * it's a typo and this correction must be applied to 300mh as well.
			 */
			if (in_array($discipline, ['300m', '400m', '300mh', '400mh']))
				$result += 0.14;

			// The correction for 500m has been removed in the 2025 edition.
			if (in_array($this->options['edition'], ['2017', '2022']) && $discipline === '500m')
				$result += 0.14;
		}

		$shiftedResult = $result + $this->resultShift;

		// for some (track) disciplines the resultShift is subtracting "0 points etalon" and shifted result above 0 means no points are awarded
		if ($this->resultShift < 0 && $shiftedResult >= 0)
			return 0;

		$points = ($this->conversionFactor * $shiftedResult * $shiftedResult) + $this->pointShift;

		if ($points <= 0)
			return 0;

		return floor($points);
	}

	/**
	 * @deprecated
	 */
	public function getPoints($result)
	{
		return $this->evaluate($result);
	}

	protected function constants(): array
	{
		$edition = $this->options['edition'];
		$venueType = $this->options['venueType'];
		$gender = $this->options['gender'];

		$constants = $this->constants->edition($edition);

		// since the 2025 edition the tables are flat, short track events have their own keys
		if (!in_array($edition, ['2017', '2022']))
			return $constants[$gender] ?? [];

		return $constants[$venueType][$gender] ?? [];
	}
}
