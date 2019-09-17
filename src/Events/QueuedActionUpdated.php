<?php

namespace MichielKempen\LaravelActions\Events;

use MichielKempen\LaravelActions\Database\QueuedAction;

class QueuedActionUpdated
{
	/**
	 * @var QueuedAction
	 */
	private $queuedAction;

	/**
	 * QueuedActionUpdated constructor.
	 *
	 * @param QueuedAction $queuedAction
	 */
	public function __construct(QueuedAction $queuedAction)
	{
		$this->queuedAction = $queuedAction;
	}

    /**
     * @return QueuedAction
     */
    public function getQueuedAction(): QueuedAction
    {
        return $this->queuedAction;
    }
}
