<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class SwaModelTeamresults extends JModelList
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
				'event_date',
				'event.event_date',
				'university',
				'team_number',
				'a.team_number',
				'result',
				'a.result',
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
		parent::populateState('event_date desc, result', 'asc');
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
		$query->from('`#__swa_team_result` AS a');

		// Join over the university field 'university_id'
		$query->select('university_id.name AS university');
		$query->join(
			'LEFT',
			'#__swa_university AS university_id ON university_id.id = a.university_id'
		);
		// Join over 'competition'
		$query->join(
			'LEFT',
			'#__swa_competition AS competition ON competition.id = a.competition_id'
		);
		// Join over 'event'
		$query->select('event.name AS event_name');
		$query->select('event.date AS event_date');
		$query->join('LEFT', '#__swa_event AS event ON event.id = competition.event_id');
		// Join over 'competition_type_id'
		$query->select('competition_type_id.name AS competition_type');
		$query->join(
			'LEFT',
			'#__swa_competition_type AS competition_type_id ON competition_type_id.id = competition.competition_type_id'
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
					'( university_id.name LIKE ' .
					$search .
					'  OR  a.team_number LIKE ' .
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
