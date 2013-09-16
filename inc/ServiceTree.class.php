<?php
require_once('Connection.class.php');

require_once('__conf.php');
require_once('i18n/i18n.php');
i18n_set_map('en', $LANG, false);

/**
 * Builds the Zabbix service tree.
 */
class ServiceTree
{
	/**
	 * Retrieves the root services list.
	 * @param  PDO   $dbh Database connection handle.
	 * @return array      Services list.
	 */
	public static function GetRootList(PDO $dbh)
	{
		try {
			$stmt = Connection::QueryDatabase($dbh, '
				SELECT DISTINCT(name) as name
				FROM services s
				INNER JOIN services_links sl ON sl.serviceupid = s.serviceid
				WHERE NOT EXISTS (
					SELECT *
					FROM services_links sl2
					WHERE sl2.servicedownid = s.serviceid
				)
			');
		} catch(Exception $e) {
			Connection::HttpError(500, I('Failed to query service list').'<br/>'.$e->getMessage());
		}
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Given a service name, find its ID.
	 * @param  PDO    $dbh  Database connection handle.
	 * @param  string $name Name of the service to be queried.
	 * @return string       ID of service (a big int really).
	 */
	public static function GetIdByName(PDO $dbh, $name)
	{
		try {
			$stmt = Connection::QueryDatabase($dbh,
				'SELECT serviceid FROM services WHERE name = ?', $name);
		} catch(Exception $e) {
			Connection::HttpError(500, sprintf(I('Failed to query service ID for "%s".'), $name).
				'<br/>'.$e->getMessage());
		}
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($row === false)
			Connection::HttpError(400, sprintf(I('Could not find service "%s".'), $name));
		return $row['serviceid'];
	}

	/**
	 * Recursive function to process all service nodes.
	 * @param  PDO    $dbh         Database connection handle.
	 * @param  string $serviceId   ID of service to be processed.
	 * @param  array& $statusCount Array to count services on each status (0-5).
	 * @return array               Associative array specifically formatted to HTML5 needs.
	 */
	public static function GetAllToHtml5(PDO $dbh, $serviceId, &$statusCount)
	{
		// Example of $statusCount array:
		// $statusCount = array(0, 0, 0, 0, 0, 0);
		// Each array position will hold how many services are in that state (0 to 5).

		// Warning: highly optimized query, think twice before modify.
		try {
			$stmt = Connection::QueryDatabase($dbh, "
				SELECT s.serviceid, s.name, s.triggerid, s.status,
					REPLACE(t.description, '{HOSTNAME}', h.host) AS triggerdesc,
					i.imageid, sl.servicedownid
				FROM services s
				LEFT JOIN service_icon si ON si.idservice = s.serviceid
					LEFT JOIN images i ON i.imageid = si.idicon
				LEFT JOIN triggers t ON t.triggerid = s.triggerid
					LEFT JOIN functions f ON f.triggerid = t.triggerid
					LEFT JOIN items ON items.itemid = f.itemid
					LEFT JOIN hosts h ON h.hostid = items.hostid
				LEFT JOIN services_links sl ON sl.serviceupid = s.serviceid
				WHERE s.serviceid = ?
			", $serviceId);
		} catch(Exception $e) {
			Connection::HttpError(500, sprintf(I('Failed to query service for "%s".'), $serviceId).
				'<br/>'.$e->getMessage());
		}

		$ret = array(); // actually an associative array
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if(!count($ret)) { // will happen only once
				$ret = array( // this data structure matches the HTML5 tree
					'text'    => (strlen($row['name']) > 16 ? substr($row['name'], 0, 16).'...' : $row['name']),
					'tooltip' => $row['name']."\n".I('Children').": %d\n".I('Trigger').': '.$row['triggerdesc'],
					'color'   => StatusColor::$VALUES[ (int)$row['status'] ],
					'image'   => ($row['imageid'] === null) ? null : Connection::BaseUrl().'inc/image.php?id='.$row['imageid'],
					'data'    => (object)array( // this "data" section is free-form and will be preserved across tree
						'serviceid'   => $row['serviceid'],
						'fullname'    => $row['name'],
						'triggerid'   => $row['triggerid'],
						'triggername' => $row['triggerdesc']
					),
					'children' => array()
				);
				++$statusCount[ (int)$row['status'] ]; // increment node counter at our state
			}
			$childService = self::GetAllToHtml5($dbh, $row['servicedownid'], $statusCount);
			if(count($childService)) // recursively returned associative array
				$ret['children'][] = (object)$childService; // append child service to children array
		}
		if(count($ret))
			$ret['tooltip'] = sprintf($ret['tooltip'], count($ret['children'])); // count children
		return $ret;
	}

	/**
	 * Returns all the information of a single node.
	 * @param  PDO    $dbh       Database connection handle.
	 * @param  string $serviceId ID of service to be processed.
	 * @return array             Node information.
	 */
	public static function GetInfo(PDO $dbh, $serviceId)
	{
		// Warning: highly optimized query, think twice before modify.
		try {
			$stmt = Connection::QueryDatabase($dbh, "
				SELECT s.*, st.*, sw.*,
					REPLACE(t.description, '{HOSTNAME}', h.host) AS triggerdesc,
					s2.serviceid AS parentserviceid, s2.name AS parentname
				FROM services s
				LEFT JOIN service_threshold st ON st.idservice = s.serviceid
				LEFT JOIN service_weight sw ON sw.idservice = s.serviceid
				LEFT JOIN services_links sl ON sl.servicedownid = s.serviceid
					LEFT JOIN services s2 ON s2.serviceid = sl.serviceupid
				LEFT JOIN triggers t ON t.triggerid = s.triggerid
					LEFT JOIN functions f ON f.triggerid = t.triggerid
					LEFT JOIN items ON items.itemid = f.itemid
					LEFT JOIN hosts h ON h.hostid = items.hostid
				WHERE s.serviceid = ?
			", $serviceId);
		} catch(Exception $e) {
			Connection::HttpError(500, sprintf(I('Failed to query service for "%s".'), $serviceId).
				'<br/>'.$e->getMessage());
		}

		$row = $stmt->fetch(PDO::FETCH_ASSOC); // just 1 service with the ID
		return (object)array(
			'serviceid'   => $row['serviceid'],
			'name'        => $row['name'],
			'status'      => (int)$row['status'],
			'algorithm'   => (int)$row['algorithm'],
			'triggerid'   => $row['triggerid'],
			'triggername' => $row['triggerdesc'],
			'showsla'     => (int)$row['showsla'],
			'goodsla'     => $row['goodsla'],
			'sortorder'   => (int)$row['sortorder'],
			'threshold'   => (object)array(
				'normal'      => $row['threshold_normal'],
				'information' => $row['threshold_information'],
				'alert'       => $row['threshold_alert'],
				'average'     => $row['threshold_average'],
				'major'       => $row['threshold_major'],
				'critical'    => $row['threshold_critical']
			),
			'weight' => (object)array(
				'normal'      => $row['weight_normal'],
				'information' => $row['weight_information'],
				'alert'       => $row['weight_alert'],
				'average'     => $row['weight_average'],
				'major'       => $row['weight_major'],
				'critical'    => $row['weight_critical']
			),
			'parent' => (object)array(
				'serviceid' => $row['parentserviceid'],
				'name'      => $row['parentname']
			)
		);
	}

	/**
	 * Creates entries on the tables service_weight and service_threshold if they don't exist.
	 * @param PDO   $dbh       Database connection handle.
	 * @param sring $serviceId ID of service to be checked.
	 */
	public static function CreateThresholdWeightIfNotExist(PDO $dbh, $serviceId)
	{
		try {
			$stmt_w = Connection::QueryDatabase($dbh,
				'SELECT idservice FROM service_weight WHERE idservice = ?', $serviceId);
			if(!$stmt_w->fetch(PDO::FETCH_NUM))
				Connection::QueryDatabase($dbh, // will insert with default values
					'INSERT INTO service_weight (idservice) VALUES (?)', $serviceId);

			$stmt_t = Connection::QueryDatabase($dbh,
				'SELECT idservice FROM service_threshold WHERE idservice = ?', $serviceId);
			if(!$stmt_t->fetch(PDO::FETCH_NUM))
				Connection::QueryDatabase($dbh, // will insert with default values
					'INSERT INTO service_threshold (idservice) VALUES (?)', $serviceId);
		} catch(Exception $e) {
			Connection::HttpError(500,
				sprintf(I('Failed to verify weight/threshold for service "%s".'), $serviceId).
				'<br/>'.$e->getMessage());
		}
	}
}
