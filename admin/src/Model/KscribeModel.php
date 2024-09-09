<?php
namespace RJCreations\Component\Kscribe\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use RJCreations\Component\Kscribe\Administrator\Helper\KunenaHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class KscribeModel extends ListModel
{
	public function getItems ()
	{
		$items = KunenaHelper::getCategories();

		return $items;
	}

	protected function dont_populateState ($ordering = null, $direction = null)
	{
		$this->setState('filter.usergroup', '');
		return;

		$app = Factory::getApplication();

		// Load state from the request.
		$pid = $app->getInput()->getInt('parent_id');
		$this->setState('tag.parent_id', $pid);

		$language = $app->getInput()->getString('tag_list_language_filter');
		$this->setState('tag.language', $language);

		$offset = $app->getInput()->get('limitstart', 0, 'uint');
		$this->setState('list.offset', $offset);
		$app = Factory::getApplication();

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('list.limit', $params->get('maximum', 200));

		$this->setState('filter.published', 1);
		$this->setState('filter.access', true);

		$user = $this->getCurrentUser();

		if ((!$user->authorise('core.edit.state', 'com_tags')) && (!$user->authorise('core.edit', 'com_tags'))) {
			$this->setState('filter.published', 1);
		}

		// Optional filter text
		$itemid	   = $pid . ':' . $app->getInput()->getInt('Itemid', 0);
		$filterSearch = $app->getUserStateFromRequest('com_tags.tags.list.' . $itemid . '.filter_search', 'filter-search', '', 'string');
		$this->setState('list.filter', $filterSearch);
	}

}