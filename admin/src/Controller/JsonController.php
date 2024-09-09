<?php
namespace RJCreations\Component\Kscribe\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\BaseController;
use RJCreations\Component\Kscribe\Administrator\Helper\KunenaHelper;

define('RJC_DBUG', (true || JDEBUG) && file_exists(JPATH_ROOT.'/rjcdev.php'));

class JsonController extends BaseController
{

	public function doAjax ()
	{
		$kcats = KunenaHelper::getCategories();
		$kusers = KunenaHelper::getUsers(2);
		echo json_encode(['cats'=>$kcats,'users'=>$kusers]);
	}

	public function setAuto()
	{
		$this->checkToken();
		$grp = $this->input->post->get('ugid', 0, 'int');
		$cat = $this->input->post->get('catid', 0, 'int');
		$val = $this->input->post->get('val', 0, 'int');

		$kh = new KunenaHelper();
		$kh->setAutoSubscribe($grp, $cat, $val);

		$p1 = $val ? 'check auto-subbed' : 'times notenabled';
		$p2 = $cat.','.($val?0:1);
		$html = '<span class="icon-'.$p1.' sel-auto" onclick="Kscribe.setAuto('.$p2.',this)"> </span>';
		echo json_encode(['html'=>$html]);
	}

}
