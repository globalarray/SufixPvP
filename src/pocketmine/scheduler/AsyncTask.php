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

declare(strict_types=1);

namespace pocketmine\scheduler;

use pocketmine\Collectable;
use pocketmine\Server;
use ArrayObject;
use function unserialize;
use function spl_object_id;

/**
 * Class used to run async tasks in other threads.
 *
 * An AsyncTask does not have its own thread. It is queued into an AsyncPool and executed if there is an async worker
 * with no AsyncTask running. Therefore, an AsyncTask SHOULD NOT execute for more than a few seconds. For tasks that
 * run for a long time or infinitely, start another {@link \pocketmine\Thread} instead.
 *
 * WARNING: Do not call PocketMine-MP API methods, or save objects (and arrays containing objects) from/on other Threads!!
 */
abstract class AsyncTask extends \Threaded implements \Collectable{

	private static mixed $threadLocalStorage = null;

	/** @var AsyncWorker $worker */
	public $worker = null;

	/** @var \Threaded */
	public $progressUpdates;

	private $result = null;
	private $serialized = false;
	private $cancelRun = false;
	/** @var int|null */
	private $taskId = null;

	private $isGarbage = false;

	private $isFinished = false;

	private $crashed = false;

	/**
	 * Constructs a new instance of AsyncTask. Subclasses don't need to call this constructor unless an argument is to be passed. ONLY construct this class from the main thread.
	 * <br>
	 * If an argument is passed into this constructor, it will be stored in a thread-local storage (in ServerScheduler), which MUST be retrieved through {@link #fetchLocal} when {@link #onCompletion} is called.
	 * Otherwise, a NOTICE level message will be raised and the reference will be removed after onCompletion exits.
	 * <br>
	 * If null or no argument is passed, do <em>not</em> call {@link #fetchLocal}, or an exception will be thrown.
	 * <br>
	 * WARNING: Use this method carefully. It might take a long time before an AsyncTask is completed. PocketMine will keep a strong reference to objects passed in this method.
	 * This may result in a light memory leak. Usually this does not cause memory failure, but be aware that the object may be no longer usable when the AsyncTask completes.
	 * (E.g. a {@link \pocketmine\Level} object is no longer usable because it is unloaded while the AsyncTask is executing, or even a plugin might be unloaded)
	 * Since PocketMine keeps a strong reference, the objects are still valid, but the implementation is responsible for checking whether these objects are still usable.
	 *
	 * @param mixed $complexData the data to store, pass null to store nothing. Scalar types can be safely stored in class properties directly instead of using this thread-local storage.
	 */
	public function __construct($complexData = null){
		if($complexData === null){
			return;
		}

		Server::getInstance()->getScheduler()->storeLocalComplex($this, $complexData);
	}


	/**
	 * @return bool
	 */
	public function isGarbage() : bool{
		return $this->isGarbage;
	}

	public function setGarbage(){
		$this->isGarbage = true;
	}

	/**
	 * @return bool
	 */
	public function isFinished() : bool{
		return $this->isFinished;
	}

	public function run() {
		$this->result = null;

		if($this->cancelRun !== true){
			try{
				$this->onRun();
			}catch(\Throwable $e){
				$this->crashed = true;
				$this->worker->handleException($e);
			}
		}

		$this->isFinished = true;
	}

	/**
	 * Saves mixed data in thread-local storage. Data stored using this storage is **only accessible from the thread it
	 * was stored on**. Data stored using this method will **not** be serialized.
	 * This can be used to store references to variables which you need later on on the same thread, but not others.
	 *
	 * For example, plugin references could be stored in the constructor of the async task (which is called on the main
	 * thread) using this, and then fetched in onCompletion() (which is also called on the main thread), without them
	 * becoming serialized.
	 *
	 * Scalar types can be stored directly in class properties instead of using this storage.
	 *
	 * Objects stored in this storage can be retrieved using fetchLocal() on the same thread that this method was called
	 * from.
	 *
	 * @param mixed  $complexData the data to store
	 */
	protected function storeLocal($complexData){
		if($this->worker !== null and $this->worker === \Thread::getCurrentThread()){
			throw new \BadMethodCallException("Objects can only be stored from the parent thread");
		}

		if(self::$threadLocalStorage === null){
			self::$threadLocalStorage = new \SplObjectStorage(); //lazy init
		}

		if(isset(self::$threadLocalStorage[$this])){
			throw new \InvalidStateException("Already storing complex data for this async task");
		}
		self::$threadLocalStorage[$this] = $complexData;
	}

	public function isCrashed() : bool{
		return $this->crashed;
	}

	/**
	 * @return mixed
	 */
	public function getResult(){
		return $this->serialized ? unserialize($this->result) : $this->result;
	}

	public function cancelRun(){
		$this->cancelRun = true;
	}

	public function hasCancelledRun() : bool{
		return $this->cancelRun === true;
	}

	/**
	 * @return bool
	 */
	public function hasResult() : bool{
		return $this->result !== null;
	}

	/**
	 * @param mixed $result
	 * @param bool  $serialize
	 */
	public function setResult($result, bool $serialize = true){
		$this->result = $serialize ? serialize($result) : $result;
		$this->serialized = $serialize;
	}

	public function setTaskId(int $taskId){
		$this->taskId = $taskId;
	}

	/**
	 * @return int|null
	 */
	public function getTaskId(){
		return $this->taskId;
	}

	/**
	 * Gets something into the local thread store.
	 * You have to initialize this in some way from the task on run
	 *
	 * @param string $identifier
	 * @return mixed
	 */
	public function getFromThreadStore(string $identifier){
		global $store;
		return ($this->isGarbage() or !isset($store[$identifier])) ? null : $store[$identifier];
	}

	/**
	 * Saves something into the local thread store.
	 * This might get deleted at any moment.
	 *
	 * @param string $identifier
	 * @param mixed  $value
	 */
	public function saveToThreadStore($identifier, $value) : void{
		if($this->worker === null or $this->isGarbage()){
			throw new \BadMethodCallException("Objects can only be added to AsyncWorker thread-local storage during task execution");
		}
		$this->worker->saveToThreadStore($identifier, $value);
	}

	/**
	 * @see AsyncWorker::removeFromThreadStore()
	 *
	 * @param string $identifier
	 */
	public function removeFromThreadStore(string $identifier) : void{
		if($this->worker === null or $this->isGarbage()){
			throw new \BadMethodCallException("Objects can only be removed from AsyncWorker thread-local storage during task execution");
		}
		$this->worker->removeFromThreadStore($identifier);
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	abstract public function onRun();

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 *
	 * @param Server $server
	 *
	 * @return void
	 */
	public function onCompletion(Server $server){

	}

	/**
	 * Call this method from {@link AsyncTask#onRun} (AsyncTask execution thread) to schedule a call to
	 * {@link AsyncTask#onProgressUpdate} from the main thread with the given progress parameter.
	 *
	 * @param mixed $progress A value that can be safely serialize()'ed.
	 */
	public function publishProgress($progress){
		$this->progressUpdates[] = serialize($progress);
	}

	/**
	 * @internal Only call from AsyncPool.php on the main thread
	 *
	 * @param Server $server
	 */
	public function checkProgressUpdates(Server $server){
		while($this->progressUpdates->count() !== 0){
			$progress = $this->progressUpdates->shift();
			$this->onProgressUpdate($server, unserialize($progress));
		}
	}

	/**
	 * Called from the main thread after {@link AsyncTask#publishProgress} is called.
	 * All {@link AsyncTask#publishProgress} calls should result in {@link AsyncTask#onProgressUpdate} calls before
	 * {@link AsyncTask#onCompletion} is called.
	 *
	 * @param Server $server
	 * @param mixed  $progress The parameter passed to {@link AsyncTask#publishProgress}. It is serialize()'ed
	 *                         and then unserialize()'ed, as if it has been cloned.
	 */
	public function onProgressUpdate(Server $server, $progress){

	}

	/**
	 * Call this method from {@link AsyncTask#onCompletion} to fetch the data stored in the constructor, if any, and
	 * clears it from the storage.
	 *
	 * Do not call this method from {@link AsyncTask#onProgressUpdate}, because this method deletes the data and cannot
	 * be used in the next {@link AsyncTask#onProgressUpdate} call or from {@link AsyncTask#onCompletion}. Use
	 * {@link AsyncTask#peekLocal} instead.
	 *
	 * @return mixed
	 *
	 * @throws \RuntimeException if no data were stored by this AsyncTask instance.
	 */
	protected function fetchLocal() : mixed{
		try{
			return $this->peekLocal();
		}finally{
			if(self::$threadLocalStorage !== null){
				unset(self::$threadLocalStorage[$this]);
			}
		}
	}

	/**
	 * Call this method from {@link AsyncTask#onProgressUpdate} to fetch the data stored in the constructor.
	 *
	 * Use {@link AsyncTask#peekLocal} instead from {@link AsyncTask#onCompletion}, because this method does not delete
	 * the data, and not clearing the data will result in a warning for memory leak after {@link AsyncTask#onCompletion}
	 * finished executing.
	 *
	 * @return mixed
	 *
	 * @throws \RuntimeException if no data were stored by this AsyncTask instance
	 */
	protected function peekLocal(){
		if($this->worker !== null and $this->worker === \Thread::getCurrentThread()){
			throw new \BadMethodCallException("Objects can only be retrieved from the parent thread");
		}

		if(self::$threadLocalStorage === null or !isset(self::$threadLocalStorage[$this])){
			throw new \InvalidStateException("No complex data stored for this async task");
		}

		return self::$threadLocalStorage[$this];
	}

	public function cleanObject() : void{
		if (self::$threadLocalStorage !== null && isset(self::$threadLocalStorage[$this])) {
			unset($threadLocalStorage[$this]);
		}
	}
}

