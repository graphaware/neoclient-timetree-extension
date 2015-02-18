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

class GetTimeEventsInRangeCommand extends AbstractCommand
{
    /**
     * const http method to be used
     */
    const METHOD = 'GET';

    /**
     * const path portion for the http endpoint
     */
    const PATH = '/graphaware/timetree/range/';

    /**
     * @var int The start time of the range
     */
    private $startTime;

    /**
     * @var int The end time of the range
     */
    private $endTime;

    /**
     * @var string Time resolution, default to day
     */
    private $resolution;

    /**
     * @var string Timezone default to UTC
     */
    private $timezone;

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), null, $this->connection);
    }

    /**
     * Set arguments specific to this command
     *
     * @param null|int    $timestamp The timestamp for the time node
     * @param null|string $resolution The resolution for the time node to be created, refer to TimeTreeExtension constants, default to day
     * @param null|string $timezone The timezone to be used, default to UTC
     */
    public function setArguments($startTime, $endTime = null, $resolution = null, $timezone = null)
    {
        if (null === $endTime) {
            $t = new \DateTime('NOW');
            $endTime = $t->getTimestamp();
        } else {
            $endTime = (int) $endTime;
        }

        if (strlen($endTime) < 13) {
            $endTime = $endTime * 1000;
        }

        $startTime = (int) $startTime;
        if (strlen($startTime) < 13) {
            $startTime = $startTime * 1000;
        }

        $this->startTime = $startTime;
        $this->endTime = $endTime;

        if (null !== $resolution) {
            $this->resolution = (string) $resolution;
        }

        if (null !== $timezone) {
            $this->timezone = (string) $timezone;
        }
    }

    /**
     * Returns the complete path for the timetree http endpoint
     *
     * @return string
     */
    private function getPath()
    {
        return self::PATH . $this->startTime . '/' . $this->endTime . '/events';
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

        return $qs;
    }
}