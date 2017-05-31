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
 */

namespace OCA\Firebasepushnotifications\Service;

use Exception;
use OCA\Firebasepushnotifications\DB\FirebaseTokenHandler;


//use OCA\Firebasepushnotifications\Db\FirebaseSetting; //todo change when Paolo will choose the name
//use OCA\Firebasepushnotifications\Db\FirebaseSettingMapper; //todo change when Paolo will choose the name

/**
 * Description of SettingService
 * @unused currently not in use.
 * @author nunzio
 */
class FirebaseSettingService {
    
    private $mapper;

	/**
	 * FirebaseSettingService constructor.
	 *
	 * @param FirebaseTokenHandler $mapper
	 */
	public function __construct(FirebaseTokenHandler $mapper) {
        $this->mapper = $mapper;
    }
    
    public function findAll() {
        //todo implement the paolo mapper function
        return null;
    }

    private function handleException ($e) {
        throw $e;
    }

    public function find() {
        try {
            //todo implement the paolo mapper function
            return null;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function create($serverKey) {
        /*$note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setUserId($userId);
        return $this->mapper->insert($note);*/


        return 0;
    }

    public function update($serverKey) {
        try {
            /*$note = $this->mapper->find($id, $userId);
            $note->setTitle($title);
            $note->setContent($content);
            return $this->mapper->update($note);*/
            return 0;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function delete() {
        try {
            /*$note = $this->mapper->find($id, $userId);
            $this->mapper->delete($note);
            return $note;*/
            return null;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }
    
}
