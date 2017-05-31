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

namespace OCA\Firebasepushnotifications\Entities;

use OCP\Files\Cache\ICacheEntry;
use OCP\Files\Node;
use OCP\Share\IShare;

/**
 * Dummy class used to store fast share data and return them on hooks without loading the whole structure
 * It's a little cheaty, but we must work with what we have got
 * The whole class will be loaded by a specific data array type, so this is tailored on it to populate IShare like attributes
 * No database records will be influenced by edits on this class, despise what single methods say in their comments
 * 
 */
class DummyShareData implements IShare {

	/**
	 *
	 * @var string $id
	 */
	protected $id;

	/**
	 *
	 * @var string $providerId
	 */
	protected $providerId;

	/**
	 *
	 * @var string $name
	 */
	protected $name;

	/**
	 *
	 * @var Node $node
	 */
	protected $node;

	/**
	 *
	 * @var int $nodeId
	 */
	protected $nodeId;

	/**
	 *
	 * @var string $nodeType
	 */
	protected $nodeType;

	/**
	 *
	 * @var int $shareType
	 */
	protected $shareType;

	/**
	 *
	 * @var string $sharedWith
	 */
	protected $sharedWith;

	/**
	 *
	 * @var int $permissions
	 */
	protected $permissions;

	/**
	 *
	 * @var \DateTime $expirationDate
	 */
	protected $expirationDate;

	/**
	 *
	 * @var string $sharedBy
	 */
	protected $sharedBy;

	/**
	 *
	 * @var string $shareOwner
	 */
	protected $shareOwner;

	/**
	 *
	 * @var string $password
	 */
	protected $password;

	/**
	 *
	 * @var string $token
	 */
	protected $token;

	/**
	 *
	 * @var string $target
	 */
	protected $target;

	/**
	 *
	 * @var \DateTime $shareTime
	 */
	protected $shareTime;

	/**
	 *
	 * @var bool $mailSend
	 */
	protected $mailSend;

	/**
	 *
	 * @var ICacheEntry $entry
	 */
	protected $entry;
	
	/**
	 * Known data here:
	 * [id] => supposedly the id of the share
	 * [itemType] => file or folder
	 * [itemSource] => should be the id of the file
	 * [shareType] => see SHARE_TYPE_* constants in OC\Share\Constants
	 * [shareWith] => share target user id
	 * [uidOwner] => file owner id
	 * [fileSource] => again the file id for some reasons (unused)
	 * [fileTarget] => the name of the file (unused)
	 * 
	 * @param array $rawDataTailoredArray
	 */
	public function __construct($rawDataTailoredArray) {
		$this->setId($rawDataTailoredArray['id'])
				->setNodeType($rawDataTailoredArray['itemType'])
				->setNodeId($rawDataTailoredArray['itemSource'])
				->setShareType($rawDataTailoredArray['shareType'])
				->setSharedWith($rawDataTailoredArray['shareWith'])
				->setShareOwner($rawDataTailoredArray['uidOwner'])
				->setTarget($rawDataTailoredArray['fileTarget']);
	}

	/**
	 * Set the internal id of the share
	 * It is only allowed to set the internal id of a share once.
	 * Attempts to override the internal id will result in an IllegalIDChangeException
	 *
	 * @param string $id
	 * @return \OCP\Share\IShare
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * Get the internal id of the share.
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get the full share id. This is the <providerid>:<internalid>.
	 * The full id is unique in the system.
	 *
	 * @return string
	 */
	public function getFullId() {
		return $this->providerId . ':' . $this->getId();
	}

	/**
	 * Set the provider id of the share
	 * It is only allowed to set the provider id of a share once.
	 * Attempts to override the provider id will result in an IllegalIDChangeException
	 *
	 * @param string $id
	 * @return \OCP\Share\IShare
	 */
	public function setProviderId($id) {
		$this->providerId = $id;
		return $this;
	}

	/**
	 * 
	 * @param string $name
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the node of the file/folder that is shared
	 *
	 * @param Node $node
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setNode(Node $node) {
		$this->node = $node;
		return $this;
	}

	/**
	 * Get the node of the file/folder that is shared
	 *
	 * @return Node is a File or aFolder
	 */
	public function getNode() {
		return $this->node;
	}

	/**
	 * Set file id for lazy evaluation of the node
	 * @param int $fileId
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setNodeId($fileId) {
		$this->nodeId = $fileId;
		return $this;
	}

	/**
	 * Get the fileid of the node of this share
	 * @return int
	 */
	public function getNodeId() {
		return $this->nodeId;
	}

	/**
	 * Set the type of node (file/folder)
	 *
	 * @param string $type
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setNodeType($type) {
		$this->nodeType = $type;
		return $this;
	}

	/**
	 * Get the type of node (file/folder)
	 *
	 * @return string
	 */
	public function getNodeType() {
		return $this->nodeType;
	}

	/**
	 * Set the shareType
	 *
	 * @param int $shareType
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setShareType($shareType) {
		$this->shareType = $shareType;
		return $this;
	}

	/**
	 * Get the shareType
	 *
	 * @return int
	 */
	public function getShareType() {
		return $this->shareType;
	}

	/**
	 * Set the receiver of this share.
	 *
	 * @param string $sharedWith
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setSharedWith($sharedWith) {
		$this->sharedWith = $sharedWith;
		return $this;
	}

	/**
	 * Get the receiver of this share.
	 *
	 * @return string
	 */
	public function getSharedWith() {
		return $this->sharedWith;
	}

	/**
	 * Set the permissions.
	 * See \OCP\Constants::PERMISSION_*
	 *
	 * @param int $permissions
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setPermissions($permissions) {
		$this->permissions = $permissions;
		return $this;
	}

	/**
	 * Get the share permissions
	 * See \OCP\Constants::PERMISSION_*
	 *
	 * @return int
	 */
	public function getPermissions() {
		return $this->permissions;
	}

	/**
	 * Set the expiration date
	 *
	 * @param \DateTime $expireDate
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setExpirationDate($expireDate) {
		$this->expirationDate = $expireDate;
		return $this;
	}

	/**
	 * Get the expiration date
	 *
	 * @return \DateTime
	 */
	public function getExpirationDate() {
		return $this->expirationDate;
	}

	/**
	 * Set the sharer of the path.
	 *
	 * @param string $sharedBy
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setSharedBy($sharedBy) {
		$this->sharedBy = $sharedBy;
		return $this;
	}

	/**
	 * Get share sharer
	 *
	 * @return string
	 */
	public function getSharedBy() {
		return $this->sharedBy;
	}

	/**
	 * Set the original share owner (who owns the path that is shared)
	 *
	 * @param string $shareOwner
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setShareOwner($shareOwner) {
		$this->shareOwner = $shareOwner;
		return $this;
	}

	/**
	 * Get the original share owner (who owns the path that is shared)
	 *
	 * @return string
	 */
	public function getShareOwner() {
		return $this->shareOwner;
	}

	/**
	 * Set the password for this share.
	 * When the share is passed to the share manager to be created
	 * or updated the password will be hashed.
	 *
	 * @param string $password
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 * Get the password of this share.
	 * If this share is obtained via a shareprovider the password is
	 * hashed.
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Set the public link token.
	 *
	 * @param string $token
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setToken($token) {
		$this->token = $token;
		return $this;
	}

	/**
	 * Get the public link token.
	 *
	 * @return string
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * Set the target path of this share relative to the recipients user folder.
	 *
	 * @param string $target
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setTarget($target) {
		$this->target = $target;
		return $this;
	}

	/**
	 * Get the target path of this share relative to the recipients user folder.
	 *
	 * @return string
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * Set the time this share was created
	 *
	 * @param \DateTime $shareTime
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setShareTime(\DateTime $shareTime) {
		$this->shareTime = $shareTime;
		return $this;
	}

	/**
	 * Get the timestamp this share was created
	 *
	 * @return \DateTime
	 */
	public function getShareTime() {
		return $this->shareTime;
	}

	/**
	 * Set if the recipient is informed by mail about the share.
	 *
	 * @param bool $mailSend
	 * @return \OCP\Share\IShare The modified object
	 */
	public function setMailSend($mailSend) {
		$this->mailSend = $mailSend;
		return $this;
	}

	/**
	 * Get if the recipient informed by mail about the share.
	 *
	 * @return bool
	 */
	public function getMailSend() {
		return $this->mailSend;
	}

	/**
	 * Set the cache entry for the shared node
	 *
	 * @param ICacheEntry $entry
	 * @return $this
	 */
	public function setNodeCacheEntry(ICacheEntry $entry) {
		$this->entry = $entry;
		return $this;
	}

	/**
	 * Get the cache entry for the shared node
	 *
	 * @return null|ICacheEntry
	 */
	public function getNodeCacheEntry() {
		return $this->entry;
	}

}
