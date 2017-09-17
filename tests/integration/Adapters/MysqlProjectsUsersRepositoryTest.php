<?php

namespace Adapters;

use mysqli;
use PHPUnit\Framework\TestCase;

class MysqlProjectsUsersRepositoryTest extends TestCase
{
	/**
	 * @var mysqli
	 */
	private $mysqli;

	public function setUp()
	{
		$this->mysqli = new mysqli('127.0.0.1', 'root', 'root', 'testdb', 13306);
		$this->mysqli->query('CREATE TABLE projects_users (project_id INT NOT NULL,user_id INT NOT NULL);');
		$this->mysqli->query('CREATE UNIQUE INDEX projects_users_unique_index ON projects_users (project_id, user_id);');
	}

	public function tearDown()
	{
		$this->mysqli->query('DROP TABLE projects_users;');
		$this->mysqli->close();
	}

	public function test_addUser_and_removeUser()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli, false);
		$repository->addUser(123, 456);
		$repository->removeUser(123, 456);

		$this->assertTrue(true);
	}

	/**
	 * @expectedException \Adapters\Exceptions\DuplicateProjectUserPairException
	 */
	public function test_double_addUser()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$repository->addUser(123, 456);
		$this->setExpectedExceptionFromAnnotation();
		$repository->addUser(123, 456);
	}

	/**
	 * @expectedException \Adapters\Exceptions\ProjectUserPairDoesNotExistException
	 */
	public function test_removeUser_when_pair_does_not_exist()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$this->setExpectedExceptionFromAnnotation();
		$repository->removeUser(123, 456);
	}

	public function test_getProjectsByUserId_when_database_is_empty()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$result = $repository->getProjectsByUserId(456);
		$this->assertEmpty($result);
	}

	public function test_getProjectsByUserId()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$repository->addUser(1, 111);
		$repository->addUser(1, 999);
		$repository->addUser(2, 999);
		$repository->addUser(3, 111);
		$result = $repository->getProjectsByUserId(1);
		$this->assertEquals([111, 999], $result);
	}

	public function test_checkUserAccess_when_user_and_project_do_not_exist()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$result = $repository->checkUserAccess(123, 456);
		$this->assertFalse($result);
	}

	public function test_checkUserAccess_when_user_has_access()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$repository->addUser(1, 456);
		$result = $repository->checkUserAccess(1, 456);
		$this->assertTrue($result);
	}

	public function test_checkUserAccess_when_user_has_not_access()
	{
		$repository = new MysqlProjectsUsersRepository($this->mysqli);
		$repository->addUser(1, 456);
		$result = $repository->checkUserAccess(1, 999);
		$this->assertFalse($result);
	}
}
