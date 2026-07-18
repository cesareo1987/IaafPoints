<?php

use GlaivePro\IaafPoints\IaafCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test all the WA 2025 disciplines at a single score.
 *
 * The 2025 book is a single flat list — no venue split. Just like in the book,
 * the events that score differently on a short (200m) track are separate
 * entries with the `_short` suffix.
 *
 * Where the 2025 coefficients are unchanged from 2022, the performances and
 * scores are taken from the 2022 book tests (former outdoor for the plain
 * keys, former indoor for the `_short` twins). Events already re-coefficiented
 * for 2025 are marked below and need values pinned from the 2025 book.
 */
class Wa2025Test extends TestCase
{
	protected IaafCalculator $calculator;

	public function setUp(): void
	{
		$this->calculator = new IaafCalculator(['edition' => '2025']);
	}

	/**
	 * @dataProvider menData
	 */
	#[DataProvider('menData')]
	public function testMenPoints(string $discipline, int|float $performance): void
	{
		$this->calculator->setOptions(['gender' => 'm', 'discipline' => $discipline]);

		$correctScore = 842;

		// No "842" in some tables
		if (in_array($discipline, ['55m', '60mh', '5km', '3kmW', 'pole_vault', 'long_jump']))
			$correctScore = 843;

		// No "842" or "843" in some tables
		if (in_array($discipline, ['50mh', '55mh', 'high_jump']))
			$correctScore = 844;

		if (in_array($discipline, ['50m', '60m']))
			$correctScore = 845;

		$this->assertEquals(
			$correctScore,
			$this->calculator->evaluate($performance)
		);
	}

	public static function menData(): array
	{
		return static::provide([
			// Men's Sprints — Part I
			'50m' => 6.23,
			'55m' => 6.73,
			'60m' => 7.19,
			'100m' => 11.15,
			'200m' => 22.62,
			'200m_short' => 23.07,

			// Men's Sprints — Part II
			'300m' => 35.74,
			'300m_short' => 36.38,
			'400m' => 50.28,
			'400m_short' => 51.30,
			'500m' => 66.06,
			'500m_short' => 67.39,

			// Men's Hurdles
			'50mh' => 7.38,
			'55mh' => 8.05,
			'60mh' => 8.66,
			'110mh' => 15.31,
			'300mh' => 39.77,
			'400mh' => 56.23,

			// Men's Relays
			'4x100m' => 43.39,
			'4x200m' => 90.81,
			'4x200m_short' => 92.05,
			'4x400m' => 204.87,
			'4x400m_short' => 208.77,
			'mixed_4x400m' => 224.44,
			'mixed_4x400m_short' => 228.01,

			// Men's Middle Distances — Part I
			'600m' => 82.77,
			'600m_short' => 84.53,
			'800m' => 116.78,
			'800m_short' => 118.68,
			'1000m' => 150.91,
			'1000m_short' => 154.02,

			// Men's Middle Distances — Part II
			'1500m' => 241.09,
			'1500m_short' => 244.41,
			'1mile' => 260.11,
			'1mile_short' => 263.94,
			'2000m' => 331.51,
			'2000m_short' => 334.98,
			'2000mSt' => 373.10,
			'3000mSt' => 578.31,

			// Men's Long Distances
			'3000m' => 518.57,
			'3000m_short' => 521.91,
			'2miles' => 558.71,
			'2miles_short' => 565.26,
			'5000m' => 889.45,
			'5000m_short' => 901.16,
			'10000m' => 1882.37,

			// Men's Road Running — Part I
			// 'mile' => 260.11, // Ignoring since the points coincide with the one in Middle Distances — Part II
			'5km' => 889,
			'10km' => 1882,
			'15km' => 2894,
			'10miles' => 3117,
			'20km' => 3917,

			// Men's Road Running — Part II
			'half_marathon' => 4158,
			'25km' => 5007,
			'30km' => 6117,
			'marathon' => 8827,
			'100km' => 26758,

			// Men's Race Walking on Road — Part I
			'3kmW' => 805,
			'5kmW' => 1357,
			'10kmW' => 2828,
			'15kmW' => 4318,
			'20kmW' => 5851,

			// Men's Race Walking on Road — Part II
			'half_marathonW' => 6240,
			'30kmW' => 9399,
			'35kmW' => 11089,
			'marathonW' => 14032,
			'50kmW' => 17289,

			// Men's Race Walking on Track — Part I
			'3000mW' => 805.72,
			'5000mW' => 1357.24,
			'10000mW' => 2828.70,
			'15000mW' => 4318.11,

			// Men's Race Walking on Track — Part II
			'20000mW' => 5851.47,
			'30000mW' => 9399.75,
			'35000mW' => 11089.59,
			'50000mW' => 17289.65,

			// Men's Jumps,	Throws and Combined Events
			'high_jump' => 1.92,
			'pole_vault' => 4.44,
			'long_jump' => 6.63,
			'triple_jump' => 13.93,
			'shot_put' => 15.31,
			'discus_throw' => 48.06,
			'hammer_throw' => 56.66,
			'javelin_throw' => 61.69,
			'heptathlon' => 4570,
			'decathlon' => 6139,
		]);
	}

	/**
	 * @dataProvider womenData
	 */
	#[DataProvider('womenData')]
	public function testWomenPoints(string $discipline, int|float $performance): void
	{
		$this->calculator->setOptions(['gender' => 'f', 'discipline' => $discipline]);

		$correctScore = 842;

		// No "842" in some tables
		if (in_array($discipline, ['55m', '60m', '100m', '3kmW', 'long_jump']))
			$correctScore = 843;

		// No "842" or "843" in some tables
		if (in_array($discipline, ['pole_vault']))
			$correctScore = 844;

		// :))
		if (in_array($discipline, ['high_jump']))
			$correctScore = 849;

		$this->assertEquals(
			$correctScore,
			$this->calculator->evaluate($performance)
		);
	}

	public static function womenData(): array
	{
		return static::provide([
			// Women's Sprints — Part I
			'50m' => 7.05,
			'55m' => 7.63,
			'60m' => 8.18,
			'100m' => 12.78,
			'200m' => 26.12,
			'200m_short' => 26.78,

			// Women's Sprints — Part II
			'300m' => 42.31,
			'300m_short' => 43.26,
			'400m' => 59.86,
			'400m_short' => 60.89,
			'500m' => 78.50,
			'500m_short' => 80.41,

			// Women's Hurdles
			'50mh' => 8.09,
			'55mh' => 8.81,
			'60mh' => 9.51,
			'100mh' => 15.45,
			'300mh' => 46.24,
			'400mh' => 66.46,

			// Women's Relays
			'4x100m' => 51.50,
			'4x200m' => 109.08,
			'4x200m_short' => 111.03,
			'4x400m' => 247.82,
			'4x400m_short' => 250.92,
			'mixed_4x400m' => 224.44,
			'mixed_4x400m_short' => 228.01,

			// Women's Middle Distances — Part I
			'600m' => 99.20,
			'600m_short' => 101.35,
			'800m' => 139.37,
			'800m_short' => 142.67,
			'1000m' => 181.53,
			'1000m_short' => 184.69,

			// Women's Middle Distances — Part II
			'1500m' => 289.32,
			'1500m_short' => 291.63,
			'1mile' => 311.16,
			'1mile_short' => 315.38,
			'2000m' => 397.23,
			'2000m_short' => 401.60,
			'2000mSt' => 450.77,
			'3000mSt' => 712.23,

			// Women's Long Distances
			'3000m' => 624.12,
			'3000m_short' => 629.82,
			'2miles' => 671.51,
			'2miles_short' => 677.93,
			'5000m' => 1079.17,
			'5000m_short' => 1089.74,
			'10000m' => 2282.29,

			// Women's Road Running — Part I
			// 'mile' => 311.16, // Ignoring since the points coincide with the one in Middle Distances — Part II
			'5km' => 1079,
			'10km' => 2282,
			'15km' => 3527,
			'10miles' => 3806,
			'20km' => 4816,

			// Women's Road Running — Part II
			'half_marathon' => 5102,
			'25km' => 6146,
			'30km' => 7505,
			'marathon' => 10901,
			'100km' => 30161,

			// Women's Race Walking on Road — Part I
			'3kmW' => 886,
			'5kmW' => 1529,
			'10kmW' => 3144,
			'15kmW' => 4798,
			'20kmW' => 6489,

			// Women's Race Walking on Road — Part II
			'half_marathonW' => 6896,
			'30kmW' => 10305,
			'35kmW' => 12371,
			'marathonW' => 15425,
			'50kmW' => 18790,

			// Women's Race Walking on Track — Part I
			'3000mW' => 886.88,
			'5000mW' => 1529.42,
			'10000mW' => 3144.92,
			'15000mW' => 4798.04,

			// Women's Race Walking on Track — Part II
			'20000mW' => 6489.80,
			'30000mW' => 10305.31,
			'35000mW' => 12371.29,
			'50000mW' => 18790.22,

			// Women's Jumps, Throws and Combined Events
			'high_jump' => 1.62,
			'pole_vault' => 3.62,
			'long_jump' => 5.28,
			'triple_jump' => 11.28,
			'shot_put' => 14.13,
			'discus_throw' => 47.49,
			'hammer_throw' => 54.39,
			'javelin_throw' => 47.21,
			'pentathlon' => 3510,
			'heptathlon' => 4798,
		]);
	}

	public function testShortTrackTypeSelectsTheShortTwin(): void
	{
		$this->calculator->setOptions(['gender' => 'm', 'discipline' => '200m', 'trackType' => 'short']);
		$resolved = $this->calculator->evaluate(23.07);

		$this->calculator->setOptions(['discipline' => '200m_short', 'trackType' => 'long']);
		$explicit = $this->calculator->evaluate(23.07);

		$this->assertEquals(842, $resolved);
		$this->assertEquals($resolved, $explicit);
	}

	public function testShortTrackTypeFallsBackWhereTrackLengthDoesNotMatter(): void
	{
		$this->calculator->setOptions(['gender' => 'm', 'discipline' => '60m', 'trackType' => 'long']);
		$long = $this->calculator->evaluate(7.19);

		$this->calculator->setOptions(['trackType' => 'short']);
		$short = $this->calculator->evaluate(7.19);

		$this->assertEquals(845, $long);
		$this->assertEquals($long, $short);
	}

	public function testVenueTypeHasNoEffect(): void
	{
		$this->calculator->setOptions(['gender' => 'm', 'discipline' => '200m', 'venueType' => 'outdoor']);
		$outdoor = $this->calculator->evaluate(22.62);

		$this->calculator->setOptions(['venueType' => 'indoor']);
		$indoor = $this->calculator->evaluate(22.62);

		$this->assertEquals($outdoor, $indoor);
	}

	public function testHandTimeCorrectionAppliesOnShortTrack(): void
	{
		$this->calculator->setOptions(['gender' => 'm', 'discipline' => '200m', 'trackType' => 'short', 'electronicMeasurement' => true]);
		$electronic = $this->calculator->evaluate(23.07);

		$this->calculator->setOptions(['electronicMeasurement' => false]);
		$handTimed = $this->calculator->evaluate(23.07 - 0.24);

		$this->assertEquals($electronic, $handTimed);
	}

	public function testTrackTypeHasNoEffectOnOlderEditions(): void
	{
		$this->calculator->setOptions(['edition' => '2022', 'venueType' => 'indoor', 'gender' => 'm', 'discipline' => '200m', 'trackType' => 'long']);
		$long = $this->calculator->evaluate(23.07);

		$this->calculator->setOptions(['trackType' => 'short']);
		$short = $this->calculator->evaluate(23.07);

		$this->assertEquals(842, $long);
		$this->assertEquals($long, $short);
	}

	public function testShortVariantsAreListedAsDisciplines(): void
	{
		$this->calculator->setOptions(['gender' => 'f']);

		$keys = $this->calculator->getSupportedDisciplineKeys();

		$this->assertContains('200m', $keys);
		$this->assertContains('200m_short', $keys);
		$this->assertNotContains('60m_short', $keys);
		$this->assertNotContains('high_jump_short', $keys);
	}

	protected static function provide(array $data): array
	{
		array_walk(
			$data,
			fn(int|float &$value, string $key) => $value = [$key, $value],
		);

		return $data;
	}
}
