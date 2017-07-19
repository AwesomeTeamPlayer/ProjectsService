<?php

namespace Domain\ValueObjects;

use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
	/**
	 * @dataProvider toJsonDataProvider
	 */
	public function test_toJson($name, $occuredAt, $data, $expectedJson)
	{
		$event = new Event($name, $occuredAt, $data);
		$this->assertFalse(true);
		$this->assertEquals($expectedJson, $event->toJson());
	}

	public function toJsonDataProvider()
	{
		$dateTime = new \DateTime();
		$dateTime->setTimezone(new \DateTimeZone('+0530'));
		$dateTime->setDate(2017, 7, 16);
		$dateTime->setTime(5, 12, 42);

		return [
			[
				'SuperName',
				$dateTime,
				[],
				'{"name":"SuperName","occuredAt":"2017-07-16T05:12:42+05:30","data":[]}',
			],
			[
				'SuperName',
				$dateTime,
				[
					'a' => 'b'
				],
				'{"name":"SuperName","occuredAt":"2017-07-16T05:12:42+05:30","data":{"a":"b"}}',
			],
		];
	}
}
