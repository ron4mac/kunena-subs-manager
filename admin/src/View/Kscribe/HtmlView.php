<?php
namespace RJCreations\Component\Kscribe\Administrator\View\Kscribe;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use RJCreations\Component\Kscribe\Administrator\Helper\KunenaHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;
	public $autoCats=[];
	private $isEmptyState = false;
	protected $ordering = [];

	public function display($tpl = null)
	{
		$this->items			= KunenaHelper::getCategories();
//		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm		= $this->get('FilterForm');
		$this->activeFilters	= $this->get('ActiveFilters');

		HTMLHelper::stylesheet('administrator/components/com_kscribe/static/kscribe.css', ['version' => 'auto']);
		HTMLHelper::script('administrator/components/com_kscribe/static/kscribe.js', ['version' => 'auto']);
		Text::script('COM_KSCRIBE_NO_GRPSEL');
		Text::script('COM_KSCRIBE_SELECT_GROUP');

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Preprocess the list of items to find ordering divisions etc.
		foreach ($this->items as &$item) {
			$this->ordering[$item->parentid][] = $item->id;
			$item->subscribers = KunenaHelper::getCategorySubscribers($item->id);
		}

		// Get the auto-subscriptions
		if ($ugr = $this->state->get('filter.usergroup')) {
			$kh = new KunenaHelper();
			$this->autoCats = $kh->getAutoCats($ugr);
		}


		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo		= ContentHelper::getActions('com_kscribe');
		$user		= $this->getCurrentUser();
		$toolbar	= Toolbar::getInstance();

		ToolbarHelper::title(Text::_('COM_KSCRIBE_SUBS_MANAGER'), 'signup');

			$toolbar->standardButton('refresh', 'COM_KSCRIBE_ACTION_SUBSCRIBE')
			//	->task('subscribe')
				->icon('icon-eye-open')
				->listCheck(true)
				->onclick('Kscribe.action(event, \'subscribe\')')
				->formValidation(true)
				->buttonClass('btn ks-action');
			$toolbar->standardButton('refresh', 'COM_KSCRIBE_ACTION_UNSUBSCRIBE')
			//	->task('unsubscribe')
				->icon('icon-eye-close')
				->listCheck(true)
				->onclick('Kscribe.action(event, \'unsubscribe\')')
				->buttonClass('btn ks-action');
/*
		if ($canDo->get('core.create')) {
			$toolbar->addNew('tag.add');
		}

		if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $user->authorise('core.admin'))) {
			// @var DropdownButton $dropdown 
			$dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state')) {
				$childBar->apply('subscribe','COM_KSCRIBE_ACTION_SUBSCRIBE')->listCheck(true);
				$childBar->apply('unsubscribe','COM_KSCRIBE_ACTION_UNSUBSCRIBE')->listCheck(true);
			}

		}

		if (!$this->isEmptyState && $canDo->get('core.admin')) {
			$toolbar->standardButton('refresh', 'JTOOLBAR_REBUILD')
				->task('tags.rebuild');
		}

		if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			$toolbar->delete('tags.delete', 'JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}
*/
		if ($canDo->get('core.admin') || $canDo->get('core.options')) {
			$toolbar->preferences('com_kscribe');
		}

//		$toolbar->help('Kscribe');
	}

}
