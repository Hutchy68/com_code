<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_code
 *
 * @since  4.0
 */
class CodeRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_code component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   4.0
	 */
	public function build(&$query)
	{
		// Declare static variables.
		static $items;
		static $cache = array();

		// Initialize variables.
		$segments = array();

		// Get the relevant menu items if not loaded.
		if (empty($items))
		{
			// Get all relevant menu items.
			$menu = JFactory::getApplication()->getMenu();
			$items = $menu->getItems('component', 'com_code');

			// Build an array of found menu item ids.
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				// Check to see if we have found the code status summary menu item.
				if (empty($cache['summary']) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'summary'))
				{
					$cache['summary'] = $items[$i]->id;
				}
			}
		}

		// Only one project for now.
		$segments[] = 'cms';
		unset($query['project_id']);

		if (!empty($query['view']))
		{
			switch ($query['view'])
			{
				case 'issue':
					if (!empty($cache['summary']))
					{
						unset($query['view']);
						$query['Itemid'] = $cache['summary'];

						$segments[] = 'trackers';
						$segments[] = @$query['tracker_alias'];
						$segments[] = @$query['issue_id'];
						unset($query['tracker_alias']);
						unset($query['tracker_id']);
						unset($query['issue_id']);
					}

					break;

				case 'tracker':
					if (!empty($cache['summary']))
					{
						unset($query['view']);
						$query['Itemid'] = $cache['summary'];

						$segments[] = 'trackers';
						$segments[] = @$query['tracker_alias'];
						unset($query['tracker_alias']);
						unset($query['tracker_id']);
					}

					break;

				case 'trackers':
				default:
					if (!empty($cache['summary']))
					{
						unset($query['view']);
						$query['Itemid'] = $cache['summary'];

						$segments[] = 'trackers';
					}

					break;
			}
		}
		elseif (!empty($query['task']))
		{
			if (!empty($cache['summary']))
			{
				unset($query['view']);
				$query['Itemid'] = $cache['summary'];
			}
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array &$segments The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   4.0
	 */
	public function parse(&$segments)
	{
		// Initialize variables.
		$vars = array();

		// If no segments exist then there is no defined project and we do not support that at this time.
		if (empty($segments))
		{
			JError::raiseError(404, 'Resource not found.');
		}

		// Get the project from the first segment.
		$projectAlias = array_shift($segments);

		// The only supported project for now is the Joomla! CMS.
		if ($projectAlias != 'cms')
		{
			JError::raiseError(404, 'Resource not found.');
		}

		$vars['project_id'] = 1;

		// If no further segments exist then we assume the project summary page was requested.
		if (empty($segments))
		{
			$vars['view'] = 'summary';

			return $vars;
		}

		// Get the view/task definition from the next segment.
		switch (array_shift($segments))
		{
			// View trackers and issues.
			case 'trackers':
				// If there is no given tracker name we default to viewing all trackers and return.
				if (empty($segments))
				{
					$vars['view'] = 'trackers';

					return $vars;
				}

				// Get the tracker alias from the next segment.
				$trackerAlias = str_replace(':', '-', array_shift($segments));

				// Search the database for the appropriate tracker.
				$db = JFactory::getDbo();
				$db->setQuery(
					$db->getQuery(true)
						->select('tracker_id')
						->from('#__code_trackers')
						->where('alias = ' . $db->quote($trackerAlias))
				, 0, 1);
				$trackerId = (int) $db->loadResult();

				// If the tracker isn't found throw a 404.
				if (!$trackerId)
				{
					JError::raiseError(404, 'Resource Not Found');
				}

				// We found a valid tracker with that alias so set the id.
				$vars['tracker_id'] = $trackerId;

				// If we have an issue id in the next segment lets set that in the request.
				if (!empty($segments) && is_numeric($segments[0]))
				{
					$vars['view'] = 'issue';
					$vars['issue_id'] = (int) array_shift($segments);
				}
				// No issue id so we are looking at the tracker itself.
				else
				{
					$vars['view'] = 'tracker';
				}

				break;
		}

		return $vars;
	}
}
