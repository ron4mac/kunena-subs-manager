<?php
namespace RJCreations\Component\Kscribe\Site\Dispatcher;

defined('_JEXEC') or die;

use RuntimeException;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

class Dispatcher extends ComponentDispatcher
{
	public function dispatch ()
	{
		throw new RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
	}

}
