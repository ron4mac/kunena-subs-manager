<?php
namespace RJCreations\Component\Kscribe\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use RJCreations\Component\Kscribe\Administrator\Helper\KunenaHelper;

define('RJC_DBUG', (true || JDEBUG) && file_exists(JPATH_ROOT.'/rjcdev.php'));

class DisplayController extends BaseController
{
	protected $default_view = 'kscribe';

	public function subscribe ()
	{
		$this->checkToken();
		$group = (int) $this->input->post->get('filter', [], 'array')['usergroup'];
		$cats = (array) $this->input->post->get('cid', [], 'int');
		$kh = new KunenaHelper();
		$kh->subscribe($group, $cats);

		$this->app->enqueueMessage(sprintf(Text::_('COM_KSCRIBE_SUBSCRIBED'),$group,implode(',',$cats)), 'success');
		$this->setRedirect(Route::_('index.php?option=com_kscribe&view=kscribe', false));

		return true;
	}

	public function unsubscribe ()
	{
		$this->checkToken();
		$group = (int) $this->input->post->get('filter', [], 'array')['usergroup'];
		$cats = (array) $this->input->post->get('cid', [], 'int');
		$kh = new KunenaHelper();
		$kh->unsubscribe($group, $cats);

		$this->app->enqueueMessage(sprintf(Text::_('COM_KSCRIBE_UNSUBSCRIBED'),$group,implode(',',$cats)), 'success');
		$this->setRedirect(Route::_('index.php?option=com_kscribe&view=kscribe', false));

		return true;
	}

}
