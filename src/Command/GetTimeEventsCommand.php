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

class GetTimeEventsCommand extends AbstractCommand
{
    /**
     * const http method to be used
     */
    const METHOD = 'GET';

    /**
     * const path portion for the http endpoint
     */
    const PATH = '/graphaware/timetree/';

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

    private $rootNodeId;

    private $relationhipType;

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), null, $this->connection, $this->getQueryStrings());
    }

    /**
     * Set arguments specific to this command
     *
     * @param null|int    $timestamp The timestamp for the time node
     * @param null|string $resolution The resolution for the time node to be created, refer to TimeTreeExtension constants, default to day
     * @param null|string $timezone The timezone to be used, default to UTC
     * @param null|int $rootNodeId The id of the TimeTreeRootNode
     */
    public function setArguments($timestamp = null, $resolution = null, $timezone = null, $relType = null, $rootNodeId = null)
    {
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

        if (null !== $rootNodeId) {
            $this->rootNodeId = (int) $rootNodeId;
        }

        if (null !== $relType) {
            $this->relationhipType = (string) $relType;
        }
    }

    /**
     * Returns the complete path for the timetree http endpoint
     *
     * @return string
     */
    private function getPath()
    {
        $p = self::PATH;
        if (null !== $this->rootNodeId) {
            $p .= $this->rootNodeId . '/';
        }
        $p .= 'single/' . $this->time . '/events';

        return $p;
    }

    private function getQueryStrings()
    {
        if (null === $this->timezone && null === $this->resolution) {
            return null;
        }

        $qs = [];

        if (null !== $this->resolution) {
            $qs['resolution'] = $this->resolution;
        }

        if (null !== $this->timezone) {
            $qs['timezone'] = $this->timezone;
        }

        if (null !== $this->relationhipType) {
            $qs['relationshipTypes'] = $this->relationhipType;
        }

        return $qs;
    }
}
