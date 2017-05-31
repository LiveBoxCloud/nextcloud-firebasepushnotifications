<?php
/**
 *
 * @copyright Copyright (c) 2017, LiveBox (support@liveboxcloud.com)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author Paolo Manili
 */
namespace OCA\Firebasepushnotifications\DB;


use OCA\Firebasepushnotifications\Entities\FirebaseErrorLog;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;

class FirebaseErrorLogHandler extends Mapper {

	protected $db;
	private $_log;
	public function __construct(IDb $db) {
		parent::__construct($db, 'firebase_error_log', FirebaseErrorLog::class);
		$this->db = $db;
	}

	/**This function saves an Error Log entry
	 * @param $customData
	 */
	public function saveErrorLog($customData){
		$toSave = null;
		if(is_string($customData)){
			$toSave = $customData;
		}else if(is_array($customData)){
			$toSave = json_encode($customData);
		}

		$errorLog = new FirebaseErrorLog();
		$errorLog->setCustomData($toSave);
		$errorLog->setCurrentTimestamp();
		$this->insert($errorLog);
	}

	/**This function fetches ErrorLog entries
	 * @param int $fetchLimit
	 * @return array
	 */
	public function getErrorLogs($fetchLimit = 100){
		$logs = $this->findEntities('SELECT * from '.$this->getTableName().' ORDER BY id ASC LIMIT '.$fetchLimit);
		$this->log()->info('Retrieved :'.count($logs).' log entries');
		return $logs;
	}

	/**This function clears error logs
	 * @return \PDOStatement
	 */
	public function deleteErrorLogs(){
		return $this->execute('DELETE FROM '.$this->getTableName().' WHERE id > 0');
	}

	/**This function deletes ErrorLog entries by Id
	 * @param $id
	 * @return \PDOStatement
	 */
	public function deleteLog($id){
		return $this->execute('DELETE FROM '.$this->getTableName().' WHERE id = ? ', $id);
	}

	/**This function return the system logger
	 * @return \OCP\ILogger
	 */
	private function log(){
		if(!$this->_log){
			$this->_log = \OC::$server->getLogger();
		}return $this->_log;
	}
}