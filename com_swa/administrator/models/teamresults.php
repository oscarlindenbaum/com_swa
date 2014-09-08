<?php

defined( '_JEXEC' ) or die;

jimport( 'joomla.application.component.modellist' );

/**
 * Methods supporting a list of Swa records.
 */
class SwaModelTeamresults extends JModelList {

	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since    1.6
	 */
	public function __construct( $config = array() ) {
		if ( empty( $config['filter_fields'] ) ) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'race_id', 'a.race_id',
				'university_id', 'a.university_id',
				'team_number', 'a.team_number',
				'result', 'a.result',

			);
		}

		parent::__construct( $config );
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState( $ordering = null, $direction = null ) {
		// Initialise variables.
		$app = JFactory::getApplication( 'administrator' );

		// Load the filter state.
		$search = $app->getUserStateFromRequest( $this->context . '.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $search );

		$published = $app->getUserStateFromRequest( $this->context . '.filter.state', 'filter_published', '', 'string' );
		$this->setState( 'filter.state', $published );

		// Load the parameters.
		$params = JComponentHelper::getParams( 'com_swa' );
		$this->setState( 'params', $params );

		// List state information.
		parent::populateState( 'a.id', 'asc' );
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param    string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 * @since    1.6
	 */
	protected function getStoreId( $id = '' ) {
		// Compile the store id.
		$id .= ':' . $this->getState( 'filter.search' );
		$id .= ':' . $this->getState( 'filter.state' );

		return parent::getStoreId( $id );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery() {
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery( true );

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from( '`#__swa_team_result` AS a' );

		// Join over the users for the checked out user
		$query->select( "uc.name AS editor" );
		$query->join( "LEFT", "#__users AS uc ON uc.id=a.checked_out" );
		// Join over the user field 'created_by'
		$query->select( 'created_by.name AS created_by' );
		$query->join( 'LEFT', '#__users AS created_by ON created_by.id = a.created_by' );
		// Join over the university field 'university_id'
		$query->select( 'university_id.name AS university' );
		$query->join( 'LEFT', '#__swa_university AS university_id ON university_id.id = a.university_id' );
		// Join over 'race_id'
		$query->join( 'LEFT', '#__swa_race AS race_id ON race_id.id = a.race_id' );
		// Join over 'event_id'
		$query->select( 'event_id.name AS event' );
		$query->join( 'LEFT', '#__swa_event AS event_id ON event_id.id = race_id.event_id' );
		// Join over 'race_type_id'
		$query->select( 'race_type_id.name AS race_type' );
		$query->join( 'LEFT', '#__swa_race_type AS race_type_id ON race_type_id.id = race_id.race_type_id' );

		// Filter by published state
		$published = $this->getState( 'filter.state' );
		if ( is_numeric( $published ) ) {
			$query->where( 'a.state = ' . (int)$published );
		} else if ( $published === '' ) {
			$query->where( '(a.state IN (0, 1))' );
		}

		// Filter by search in title
		$search = $this->getState( 'filter.search' );
		if ( !empty( $search ) ) {
			if ( stripos( $search, 'id:' ) === 0 ) {
				$query->where( 'a.id = ' . (int)substr( $search, 3 ) );
			} else {
				$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );

			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get( 'list.ordering' );
		$orderDirn = $this->state->get( 'list.direction' );
		if ( $orderCol && $orderDirn ) {
			$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );
		}

		return $query;
	}

	public function getItems() {
		$items = parent::getItems();

		return $items;
	}

}