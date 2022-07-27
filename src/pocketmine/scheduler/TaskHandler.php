<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\scheduler;

use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;

class TaskHandler{

	/** @var Task */
	protected $task;

	/** @var int */
	protected $taskId;

	/** @var int */
	protected $delay;

	/** @var int */
	protected $period;

	/** @var int */
	protected $nextRun;

	/** @var bool */
	protected $cancelled = false;

	/** @var TimingsHandler */
	private $timings;

	/** @var string */
	private $taskName;
	/** @var string */
	private $ownerName;

	/**
	 * @param Task        $task
	 * @param int         $taskId
	 * @param int         $delay
	 * @param int         $period
	 * @param string|null $ownerName
	 */
	public function __construct(Task $task, int $taskId, int $delay = -1, int $period = -1, ?string $ownerName = null){
		if($task->getHandler() !== null){
			throw new \InvalidArgumentException("Cannot assign multiple handlers to the same task");
		}
		$this->task = $task;
		$this->taskId = $taskId;
		$this->delay = $delay;
		$this->period = $period;
		$this->taskName = $task->getName();
		$this->ownerName = $ownerName ?? "Unknown";
		$this->timings = Timings::getScheduledTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	public function isCancelled(){
		return $this->cancelled;
	}

	public function getNextRun(){
		return $this->nextRun;
	}

	/**
	 * @return void
	 */
	public function setNextRun($ticks){
		$this->nextRun = $ticks;
	}

	public function getTaskId() : int{
		return $this->taskId;
	}

	public function getTask(){
		return $this->task;
	}

	public function getDelay(){
		return $this->delay;
	}

	public function isDelayed(){
		return $this->delay > 0;
	}

	public function isRepeating(){
		return $this->period > 0;
	}

	public function getPeriod(){
		return $this->period;
	}

	/**
	 * @return void
	 */
	public function cancel(){
		try{
			if(!$this->isCancelled()){
				$this->task->onCancel();
			}
		}finally{
			$this->remove();
		}
	}

	/**
	 * @return void
	 */
	public function remove(){
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	/**
	 * @return void
	 */
	public function run(int $currentTick){
		$this->timings->startTiming();
		try{
			$this->task->onRun($currentTick);
		}finally{
			$this->timings->stopTiming();
		}
	}

	public function getTaskName(){
		return $this->taskName;
	}

	public function getOwnerName() : string{
		return $this->ownerName;
	}
}