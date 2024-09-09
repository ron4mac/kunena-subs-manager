<?php
namespace RJCreations\Component\Kscribe\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\HTMLHelper;
use Kunena\Forum\Libraries\User\KunenaUser;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategoryHelper;

class KunenaHelper
{
	protected $db;

	public function __construct() {
		$this->db = Factory::getDbo();
	}

	public static function getCategories ()
	{
		return KunenaCategoryHelper::getCategories();
	}

	public static function getCategorySubscribers ($catid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__kunena_user_categories');
		$query->where('category_id = ' . $catid);
		$query->where('subscribed = 1');
		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function getUsers ($group=0)
	{
		if ($group) {
			$users = Access::getUsersByGroup($group);
		} else {
			$uray = HTMLHelper::_('user.userlist');
			$users = array_map(function($u) {return $u->value;}, $uray);
		}
		file_put_contents('KSCRIBEU.txt',print_r($users,true),FILE_APPEND);
		return $users;
	}

	public function subscribe ($group, $cats)
	{
		$users = self::getUsers($group);
		foreach ($users as $user) {
			$this->setUserSubscriptions($user, $cats, 1);
		}
	}

	public function unsubscribe ($group, $cats)
	{
		$users = self::getUsers($group);
		foreach ($users as $user) {
			$this->setUserSubscriptions($user, $cats, 0);
		}
	}

	public function getAutoCats ($group)
	{
		$query = $this->db->getQuery(true);
		$query->select('categoryid, subscribe');
		$query->from('#__kscribe_auto');
		$query->where('groupid = ' . $group);
		$this->db->setQuery($query);
//		Log::add('getAutoCats: ' . (string)$query);
		return $this->db->loadAssocList('categoryid');
	}

	public function getAutos4Groups ($groups)
	{
		$query = $this->db->getQuery(true);
		$query->select('DISTINCT categoryid');
		$query->from('#__kscribe_auto');
		$query->where('groupid IN (' . implode(',',$groups) .')');
		$query->where('subscribe = 1');
		$this->db->setQuery($query);
//		Log::add('getAutos4Groups: ' . (string)$query);
		return $this->db->loadColumn();
	}

	public function userSubscribe($userid, $cats, $rescribe)
	{
		if ($rescribe) {
			$query = $this->db->getQuery(true);
			$query->delete('#__kunena_user_categories');
			$query->where('user_id = ' . $userid);
		// maybe use some method to NOT remove subscriptions that were selected by the user
		//	$query->where('category_id = ' . $cat);
			$this->db->setQuery($query);
//			Log::add('userSubscribe: ' . (string)$query);
			$this->db->execute();
		}
		$this->setUserSubscriptions($userid, $cats, 1);
	}

	public function setAutoSubscribe ($group, $category, $value)
	{
		$query = $this->db->getQuery(true);
		$query->select('subscribe');
		$query->from('#__kscribe_auto');
		$query->where('groupid = ' . $group);
		$query->where('categoryid = ' . $category);
		$this->db->setQuery($query);
//		Log::add('setAutoSubscribeQ: ' . (string)$query);
		$query->clear();
		if ($this->db->loadResult() === null) {
			$query->insert('#__kscribe_auto');
			$query->columns('groupid, categoryid, subscribe');
			$query->values(implode(',',[$group, $category, $value]));
		} else {
			$query->update('#__kscribe_auto');
			$query->set('subscribe = ' . $value);
			$query->where('groupid = ' . $group);
			$query->where('categoryid = ' . $category);
		}
		$this->db->setQuery($query);
//		Log::add('setAutoSubscribeS: ' . (string)$query);
		$this->db->execute();
	}

	private function setUserSubscriptions ($userid, $cats, $yn)
	{
		// get all of user category data
		$ucats = $this->getSubscriptionsOfUser($userid);
		file_put_contents('KSCRIBEU.txt',print_r([$ucats,$cats],true),FILE_APPEND);
		// update ones that are present
		// insert one that arent
		foreach ($cats as $cat) {
			if (isset($ucats[$cat])) {
				$this->setUserSubscription ($userid, $cat, $yn);
			} elseif ($yn) {
				$this->setUserSubscription ($userid, $cat, $yn, true);
			}
		}
	}

	private function setUserSubscription ($user, $cat, $yn, $insert=false)
	{
		$query = $this->db->getQuery(true);
		if ($insert) {
			$query->insert('#__kunena_user_categories');
			$query->columns('user_id, category_id, subscribed');
			$query->values(implode(',',[$user, $cat, $yn]));
		} else {
			$query->update('#__kunena_user_categories');
			$query->set('subscribed = ' . $yn);
			$query->where('user_id = ' . $user);
			$query->where('category_id = ' . $cat);
		}
		$this->db->setQuery($query);
//		Log::add('setUserSubscription: ' . (string)$query);
		$this->db->execute();
	}

	private function getKunenaUser($userId): KunenaUser
	{
		if (!($userId instanceof KunenaUser)) {
			return KunenaUserHelper::get($userId, true);
		}
		return $userId;
	}

	private function getSubscriptionsOfUser(int $userId)
	{
		$query = $this->db->getQuery(true);
		$query->select('category_id, subscribed');
		$query->from('#__kunena_user_categories');
		$query->where('user_id = ' . $userId);
		$this->db->setQuery($query);
//		Log::add('getSubscriptionsOfUser: ' . (string)$query);
		return $this->db->loadAssocList('category_id');
	}

	private function updateSubscriptionofUser(int $type, int $userId, int $categoryId = 0): void
	{
		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__kunena_user_categories'));
		$query->set($this->db->quoteName('subscribed') . ' = ' . $type);
		$query->where($this->db->quoteName('user_id') . ' = ' . $userId);

		if (!empty($categoryId)) {
			$query->where($this->db->quoteName('category_id') . ' = ' . $categoryId);
		}

		$this->db->setQuery($query);
//		Log::add('updateSubscriptionofUser: ' . (string)$query);
		$this->db->execute();

	}

	private function insertSubscriptionofUser(array $insertValues): void
	{
		$query = $this->db->getQuery(true);
		$query->insert($this->db->quoteName('#__kunena_user_categories'));
		$query->columns($this->db->quoteName('user_id') . ', ' . $this->db->quoteName('category_id') . ', ' . $this->db->quoteName('subscribed'));
		$query->values($insertValues);
		$this->db->setQuery($query);
//		Log::add('insertSubscriptionofUser: ' . (string)$query);
		$this->db->execute();
	}

}