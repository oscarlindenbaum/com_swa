<?php
defined( '_JEXEC' ) or die;

// Include dependancies
jimport( 'joomla.application.component.controller' );

// Execute the task.
$controller = JControllerLegacy::getInstance( 'Swa' );
$controller->execute( JFactory::getApplication()->input->get( 'task' ) );
$controller->redirect();
