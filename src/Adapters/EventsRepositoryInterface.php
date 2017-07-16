<?php

namespace Adapters;

use Domain\ValueObjects\Event;

interface EventsRepositoryInterface
{
	/**
	 * @param Event $event
	 *
	 * @return void
	 */
	public function push(Event $event);
}
