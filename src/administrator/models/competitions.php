<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class SwaModelCompetitions extends JModelList
{

	/**
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see        JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'event_id',
				'a.event_id',
				'competition_type_id',
				'a.competition_type_id',

			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');
		$this->setState(
			'filter.search',
			$app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search')
		);
		$this->setState('params', JComponentHelper::getParams('com_swa'));
		parent::populateState('a.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'DISTINCT a.*'
			)
		);
		$query->from('`#__swa_competition` AS a');

		// Join over for event_id
		$query->select('event_id.name AS event');
		$query->join('LEFT', '#__swa_event AS event_id ON event_id.id = a.event_id');
		// Join over for competition_type_id
		$query->select('competition_type_id.name AS competition_type');
		$query->join(
			'LEFT',
			'#__swa_competition_type AS competition_type_id ON competition_type_id.id = a.competition_type_id'
		);

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where(
					'( event_id.name LIKE ' .
					$search .
					'  OR  competition_type_id.name LIKE ' .
					$search .
					' )'
				);
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

}
