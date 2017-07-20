<?php

namespace Api;

class ApplicationConfig
{
	/**
	 * @var array
	 */
	private $config = [];

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @return array
	 */
	public function getArray(): array
	{
		return $this->config;
	}

	/**
	 * @return string
	 */
	public function getRabbitmqHost() : string
	{
		return $this->config['rabbitmq']['host'];
	}

	/**
	 * @return int
	 */
	public function getRabbitmqPort() : int
	{
		return (int) $this->config['rabbitmq']['port'];
	}

	/**
	 * @return string
	 */
	public function getRabbitmqUser() : string
	{
		return $this->config['rabbitmq']['user'];
	}

	/**
	 * @return string
	 */
	public function getRabbitmqPassword() : string
	{
		return $this->config['rabbitmq']['password'];
	}

	/**
	 * @return string
	 */
	public function getRabbitmqChannel() : string
	{
		return $this->config['rabbitmq']['channel'];
	}

	/**
	 * @return string
	 */
	public function getMysqlHost() : string
	{
		return $this->config['mysql']['host'];
	}

	/**
	 * @return int
	 */
	public function getMysqlPort() : int
	{
		return (int) $this->config['mysql']['port'];
	}

	/**
	 * @return string
	 */
	public function getMysqlUser() : string
	{
		return $this->config['mysql']['user'];
	}

	/**
	 * @return string
	 */
	public function getMysqlPassword() : string
	{
		return $this->config['mysql']['password'];
	}

	/**
	 * @return string
	 */
	public function getMysqlDatabase() : string
	{
		return $this->config['mysql']['database'];
	}
}
