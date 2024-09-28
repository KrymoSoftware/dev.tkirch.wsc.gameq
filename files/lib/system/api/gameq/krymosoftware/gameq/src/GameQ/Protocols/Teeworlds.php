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

use GameQ\Protocol;
use GameQ\Buffer;
use GameQ\Result;
use GameQ\Exception\ProtocolException;

/**
 * Teeworlds Protocol class
 *
 * Only supports versions > 0.5
 *
 * @author Austin Bischoff <austin@codebeard.com>
 * @author Marcel Bößendörfer <m.boessendoerfer@marbis.net>
 */
class Teeworlds extends Protocol
{

    /**
     * Array of packets we want to look up.
     * Each key should correspond to a defined method in this or a parent class
     */
    protected array $packets = [
        self::PACKET_ALL => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\x67\x69\x65\x33\x05",
        // 0.5 Packet (not compatible, maybe some wants to implement "Teeworldsold")
        //self::PACKET_STATUS => "\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFFgief",
    ];

    /**
     * Use the response flag to figure out what method to run
     *
     */
    protected array $responses = [
        "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xffinf35" => "processAll",
    ];

    /**
     * The query protocol used to make the call
     */
    protected string $protocol = 'teeworlds';

    /**
     * String name of this protocol class
     */
    protected string $name = 'teeworlds';

    /**
     * Longer string name of this protocol class
     */
    protected string $name_long = "Teeworlds Server";

    /**
     * The client join link
     */
    protected ?string $join_link = "steam://connect/%s:%d/";

    /**
     * Normalize settings for this protocol
     */
    protected array $normalize = [
        // General
        'general' => [
            // target       => source
            'dedicated'  => 'dedicated',
            'hostname'   => 'hostname',
            'mapname'    => 'map',
            'maxplayers' => 'num_players_total',
        ],
        // Individual
        'player'  => [
            'name'  => 'name',
            'score' => 'score',
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
        // Holds the results
        $results = [];

        // Iterate over the packets
        foreach ($this->packets_response as $response) {
            // Make a buffer
            $buffer = new Buffer($response);

            // Grab the header
            $header = $buffer->readString();

            // Figure out which packet response this is
            if (!array_key_exists($header, $this->responses)) {
                throw new ProtocolException(__METHOD__ . " response type '" . bin2hex($header) . "' is not valid");
            }

            // Now we need to call the proper method
            $results = array_merge(
                $results,
                call_user_func_array([$this, $this->responses[$header]], [$buffer])
            );
        }

        unset($buffer);

        return $results;
    }

    /**
     * Handle processing all of the data returned
     *
     * @return array
     */
    protected function processAll(Buffer $buffer)
    {
        // Set the result to a new result instance
        $result = new Result();

        // Always dedicated
        $result->add('dedicated', 1);

        $result->add('version', $buffer->readString());
        $result->add('hostname', $buffer->readString());
        $result->add('map', $buffer->readString());
        $result->add('game_descr', $buffer->readString());
        $result->add('flags', $buffer->readString()); // not sure about that
        $result->add('num_players', $buffer->readString());
        $result->add('maxplayers', $buffer->readString());
        $result->add('num_players_total', $buffer->readString());
        $result->add('maxplayers_total', $buffer->readString());

        // Players
        while ($buffer->getLength()) {
            $result->addPlayer('name', $buffer->readString());
            $result->addPlayer('clan', $buffer->readString());
            $result->addPlayer('flag', $buffer->readString());
            $result->addPlayer('score', $buffer->readString());
            $result->addPlayer('team', $buffer->readString());
        }

        return $result->fetch();
    }
}
