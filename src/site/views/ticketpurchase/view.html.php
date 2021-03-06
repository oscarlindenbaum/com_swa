<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class SwaViewTicketpurchase extends JViewLegacy
{
	protected $state;

	protected $member;

	protected $params;

	protected $user;

	protected $items;

	protected $ticket_id;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$this->user   = JFactory::getUser();
		$this->state  = $this->get('State');
		$this->params = $app->getParams('com_swa');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// If not logged in
		if ($this->user->id === 0)
		{
			$url = 'index.php?option=com_users';
			$url .= '&return=' . base64_encode(JURI::getInstance()->toString());
			$app->redirect(JRoute::_($url, false));
		}

		$this->member = $this->get('Member');

		if (!is_object($this->member))
		{
			$app->redirect(JRoute::_('index.php?option=com_swa&view=memberregistration'));
		}

		if (!$this->member->paid)
		{
			$app->redirect(JRoute::_('index.php?option=com_swa&view=memberpayment'));
		}

		$this->items = $this->get('Items');

		$this->ticket_id = $app->getUserState('com_swa.ticketpurchase.ticket_id');

		parent::display($tpl);
	}

}
