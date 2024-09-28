<?php
/**
 * This file is part of GameQ.
 *
 * GameQ is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GameQ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace GameQ\Protocols;

use GameQ\Exception\ProtocolException;
use GameQ\Protocol;
use GameQ\Buffer;
use GameQ\Result;

/**
 * GameSpy2 Protocol class
 *
 * Given the ability for non utf-8 characters to be used as hostnames, player names, etc... this
 * version returns all strings utf-8 encoded (utf8_encode).  To access the proper version of a
 * string response you must use utf8_decode() on the specific response.
 *
 * @author Austin Bischoff <austin@codebeard.com>
 */
class Gamespy2 extends Protocol
{

    /**
     * Define the state of this class
     */
    protected int $state = self::STATE_BETA;

    /**
     * Array of packets we want to look up.
     * Each key should correspond to a defined method in this or a parent class
     */
    protected array $packets = [
        self::PACKET_DETAILS => "\xFE\xFD\x00\x43\x4F\x52\x59\xFF\x00\x00",
        self::PACKET_PLAYERS => "\xFE\xFD\x00\x43\x4F\x52\x58\x00\xFF\xFF",
    ];

    /**
     * Use the response flag to figure out what method to run
     *
     */
    protected array $responses = [
        "\x00\x43\x4F\x52\x59" => "processDetails",
        "\x00\x43\x4F\x52\x58" => "processPlayers",
    ];

    /**
     * The query protocol used to make the call
     */
    protected string $protocol = 'gamespy2';

    /**
     * String name of this protocol class
     */
    protected string $name = 'gamespy2';

    /**
     * Longer string name of this protocol class
     */
    protected string $name_long = "GameSpy2 Server";

    /**
     * The client join link
     */
    protected ?string $join_link = null;

    /**
     * Normalize settings for this protocol
     */
    protected array $normalize = [
        // General
        'general' => [
            // target       => source
            'dedicated'  => 'dedicated',
            'gametype'   => 'gametype',
            'hostname'   => 'hostname',
            'mapname'    => 'mapname',
            'maxplayers' => 'maxplayers',
            'mod'        => 'mod',
            'numplayers' => 'numplayers',
            'password'   => 'password',
        ],
    ];


    /**
     * Process the response
     *
     * @return mixed
     * @throws ProtocolException
     */
    public function processResponse(): mixed
    {

        // Will hold the packets after sorting
        $packets = [];

        // We need to pre-sort these for split packets so we can do extra work where needed
        foreach ($this->packets_response as $response) {
            $buffer = new Buffer($response);

            // Pull out the header
            $header = $buffer->read(5);

            // Add the packet to the proper section, we will combine later
            $packets[$header][] = $buffer->getBuffer();
        }

        unset($buffer);

        $results = [];

        // Now let's iterate and process
        foreach ($packets as $header => $packetGroup) {
            // Figure out which packet response this is
            if (!array_key_exists($header, $this->responses)) {
                throw new ProtocolException(__METHOD__ . " response type '" . bin2hex($header) . "' is not valid");
            }

            // Now we need to call the proper method
            $results = array_merge(
                $results,
                call_user_func_array([$this, $this->responses[$header]], [new Buffer(implode($packetGroup))])
            );
        }

        unset($packets);

        return $results;
    }

    /*
     * Internal methods
     */

    /**
     * Handles processing the details data into a usable format

     *
     * @return array
     * @throws ProtocolException
     */
    protected function processDetails(Buffer $buffer)
    {

        // Set the result to a new result instance
        $result = new Result();

        // We go until we hit an empty key
        while ($buffer->getLength()) {
            $key = $buffer->readString();
            if ($key === '') {
                break;
            }
            $result->add($key, $this->convertToUtf8($buffer->readString()));
        }

        return $result->fetch();
    }

    /**
     * Handles processing the players data into a usable format

     *
     * @return array
     * @throws ProtocolException
     */
    protected function processPlayers(Buffer $buffer)
    {

        // Set the result to a new result instance
        $result = new Result();

        // Skip the header
        $buffer->skip();

        // Players are first
        $this->parsePlayerTeam('players', $buffer, $result);

        // Teams are next
        $this->parsePlayerTeam('teams', $buffer, $result);

        return $result->fetch();
    }

    /**
     * Parse the player/team info returned from the player call
     *
     * @throws ProtocolException
     */
    protected function parsePlayerTeam(string $dataType, Buffer $buffer, Result $result)
    {

        // Do count
        $result->add('num_' . $dataType, $buffer->readInt8());

        // Variable names
        $varNames = [];

        // Loop until we run out of length
        while ($buffer->getLength()) {
            $varNames[] = str_replace('_', '', $buffer->readString());

            if ($buffer->lookAhead() === "\x00") {
                $buffer->skip();
                break;
            }
        }

        // Check if there are any value entries
        if ($buffer->lookAhead() === "\x00") {
            $buffer->skip();

            return;
        }

        // Get the values
        while ($buffer->getLength() > 4) {
            foreach ($varNames as $varName) {
                $result->addSub($dataType, $this->convertToUtf8($varName), $this->convertToUtf8($buffer->readString()));
            }
            if ($buffer->lookAhead() === "\x00") {
                $buffer->skip();
                break;
            }
        }
    }
}
