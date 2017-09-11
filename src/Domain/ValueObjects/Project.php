<?php

namespace Domain\ValueObjects;

use Carbon\Carbon;

class Project
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $isArchived;

	/**
	 * @var Carbon
	 */
	private $createdAt;

	public function __construct(string $id, string $name, int $type, bool $isArchived, Carbon $createdAt)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->isArchived = $isArchived;
		$this->createdAt = $createdAt;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): int
	{
		return $this->type;
	}

	public function isArchived(): bool
	{
		return $this->isArchived;
	}

	public function getCreatedAt(): Carbon
	{
		return $this->createdAt;
	}

}
