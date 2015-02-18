<?php

/**
* This file is part of the GraphAware NeoClient TimeTree Extension package
*
* (c) GraphAware <christophe@graphaware.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
*/

namespace GraphAware\NeoClientExtension\TimeTree;

use Neoxygen\NeoClient\Extension\AbstractExtension;

class TimeTreeExtension extends AbstractExtension
{
    /**
     *
     */
    const TIMETREE_RESOLUTION_YEAR = 'Year';

    /**
     *
     */
    const TIMETREE_RESOLUTION_MONTH = 'Month';

    /**
     *
     */
    const TIMETREE_RESOLUTION_DAY = 'Day';

    /**
     *
     */
    const TIMETREE_RESOLUTION_HOUR = 'Hour';

    /**
     *
     */
    const TIMETREE_RESOLUTION_MINUTE = 'Minute';

    /**
     *
     */
    const TIMETREE_RESOLUTION_SECOND = 'Second';

    /**
     *
     */
    const TIMETREE_RESOLUTION_MILLISECOND = 'Millisecond';

    /**
     * @return array
     */
    public static function getAvailableCommands()
    {
        return array(
            'graphaware_timetree_get_time_node' => array(
                'class' => 'GraphAware\\NeoClientExtension\\TimeTree\\Command\\GetTimeNodeCommand'
            ),
            'graphaware_timetree_add_node_to_time' => array(
                'class' => 'GraphAware\\NeoClientExtension\\TimeTree\\Command\\AddNodeToTimeCommand'
            ),
            'graphaware_timetree_get_time_events' => array(
                'class' => 'GraphAware\\NeoClientExtension\\TimeTree\\Command\\GetTimeEventsCommand'
            ),
            'graphaware_timetree_get_events_in_range' => array(
                'class' => 'GraphAware\\NeoClientExtension\\TimeTree\\Command\\GetTimeEventsInRangeCommand'
            )
        );
    }

    /**
     * Creates the schema indexes on the TimeTree nodes
     *
     * @param null $conn
     * @return bool
     */
    public function createTimeTreeIndexes($conn = null)
    {
        $labels = ['Year', 'Month', 'Day', 'Hour', 'Minute', 'Second', 'Millisecond'];
        foreach ($labels as $label) {
            $command = $this->invoke('neo.send_cypher_query', $conn);
            $query = 'CREATE INDEX ON :`'.$label.'`(value);';
            $httpResponse = $command->setArguments($query, array(), $this->resultDataContent, self::WRITE_QUERY)
                ->execute();
        }

        return true;
    }

    /**
     * Get a node for a time instant
     *
     * @param null $timestamp
     * @param null $resolution
     * @param null $timezone
     * @param null $conn
     * @return int
     */
    public function getTimeNode($timestamp = null, $resolution = null, $timezone = null, $conn = null)
    {
        $command = $this->invoke('graphaware_timetree_get_time_node');
        $command->setArguments($timestamp, $resolution, $timezone);
        $response = $command->execute();

        $parsed = $this->handleHttpResponse($response);

        return (int) $parsed->getBody();
    }

    /**
     * Add an event node to a time instant
     *
     * @param $nodeId
     * @param null $timestamp
     * @param $relationshipType
     * @param null $resolution
     * @param null $timezone
     * @param null $conn
     * @return bool
     */
    public function addNodeToTime($nodeId, $timestamp = null, $relationshipType, $resolution = null, $timezone = null, $conn = null)
    {
        $command = $this->invoke('graphaware_timetree_add_node_to_time', $conn);
        $command->setArguments($nodeId, $timestamp, $relationshipType, $resolution, $timezone);
        $command->execute();

        return true;
    }

    /**
     * Returns event nodes attached to a time instant
     *
     * @param $timestamp
     * @param null $resolution
     * @param null $timezone
     * @param null $relationshipType
     * @param null $conn
     * @return array|\Neoxygen\NeoClient\Formatter\Response|string
     */
    public function getTimeEvents($timestamp, $resolution = null, $timezone = null, $relationshipType = null, $conn = null)
    {
        $command = $this->invoke('graphaware_timetree_get_time_events', $conn);
        $command->setArguments($timestamp, $resolution, $timezone, $relationshipType);
        $response = $command->execute();
        $parsed = $this->handleHttpResponse($response);

        $ids = [];
        foreach ($parsed->getBody() as $node) {
            $ids[] = (int) $node['nodeId'];
        }
        $query = 'MATCH (n) WHERE id(n) IN {ids} RETURN n';
        $params = ['ids' => $ids];
        $command = $this->invoke('neo.send_cypher_query', $conn);
        $command->setArguments($query, $params, $this->resultDataContent, self::READ_QUERY);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }

    public function getTimeEventsInRange($startTime, $endTime = null, $resolution = null, $timezone = null, $conn = null)
    {
        $command = $this->invoke('graphaware_timetree_get_events_in_range', $conn);
        $command->setArguments($startTime, $endTime, $resolution, $timezone);
        $response = $command->execute();

        $ids = [];
        foreach ($response->getBody() as $node) {
            $ids[] = $node['nodeId'];
        }
        $q = 'MATCH (n) WHERE id(n) IN {ids} RETURN n';
        $params = ['ids' => $ids];
        $command = $this->invoke('neo.send_cypher_query', $conn);
        $command->setArguments($q, $params, $this->resultDataContent, self::READ_QUERY);
        $httpResponse = $command->execute();

        return $this->handleHttpResponse($httpResponse);
    }
}

