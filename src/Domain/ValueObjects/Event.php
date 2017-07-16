<?php

namespace Domain\ValueObjects;

class Event
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var \DateTime
	 */
	private $occuredAt;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @param string $name
	 * @param \DateTime $occuredAt
	 * @param array $data
	 */
	public function __construct(
		string $name,
		\DateTime $occuredAt,
		array $data = []
	)
	{
		$this->name = $name;
		$this->occuredAt = $occuredAt;
		$this->data = $data;
	}

	public function toJson()
	{
		return json_encode(
			[
				'name' => $this->name,
				'occuredAt' => $this->occuredAt->format('Y-m-d') . 'T' . $this->occuredAt->format('H:i:sP'),
				'data' => $this->data,
			]
		);
	}

}
