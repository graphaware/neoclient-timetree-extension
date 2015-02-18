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

namespace GraphAware\NeoClientExtension\TimeTree\Command;

use Neoxygen\NeoClient\Command\AbstractCommand;

class AddNodeToTimeCommand extends AbstractCommand
{
    /**
     * const http method to be used
     */
    const METHOD = 'POST';

    /**
     * const path portion for the http endpoint
     */
    const PATH = '/graphaware/timetree/single/event';

    /**
     * @var int The time node to return
     */
    private $time;

    /**
     * @var string Time resolution, default to day
     */
    private $resolution;

    /**
     * @var string Timezone default to UTC
     */
    private $timezone;

    /**
     * @var int the event node Id to attach to a time node
     */
    private $nodeId;

    /**
     * @var string The relationship type directed from the time node to the event node
     */
    private $relationshipType;

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->process(self::METHOD, self::PATH, $this->prepareBody(), $this->connection);
    }

    /**
     * Set arguments specific to this command
     *
     * @param null|int    $timestamp The timestamp for the time node
     * @param null|string $resolution The resolution for the time node to be created, refer to TimeTreeExtension constants, default to day
     * @param null|string $timezone The timezone to be used, default to UTC
     */
    public function setArguments($nodeId, $timestamp = null, $relationshipType, $resolution = null, $timezone = null)
    {
        $this->nodeId = (int) $nodeId;
        $this->relationshipType = (string) $relationshipType;

        if (null === $timestamp) {
            $t = new \DateTime('NOW');
            $time = $t->getTimestamp();
        } else {
            $time = (int) $timestamp;
        }

        if (strlen($time) < 13) {
            $time = $time * 1000;
        }

        $this->time = $time;

        if (null !== $resolution) {
            $this->resolution = (string) $resolution;
        }

        if (null !== $timezone) {
            $this->timezone = (string) $timezone;
        }
    }

    private function prepareBody()
    {
        $body = [
            'nodeId' => $this->nodeId,
            'relationshipType' => $this->relationshipType,
            'time' => $this->time
        ];

        if (null !== $this->resolution) {
            $body['resolution'] = $this->resolution;
        }

        if (null !== $this->timezone) {
            $body['timezone'] = $this->timezone;
        }

        return json_encode($body);
    }
}