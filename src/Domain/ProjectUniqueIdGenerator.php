<?php

namespace Domain;

class ProjectUniqueIdGenerator
{
	public function generateUniqueId(): string
	{
		return substr(md5(microtime() . rand(0, 9999999999)), 0, 10);
	}
}
