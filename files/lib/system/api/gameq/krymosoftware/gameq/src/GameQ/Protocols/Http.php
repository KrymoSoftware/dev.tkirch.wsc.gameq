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

/**
 * Class Http
 *
 * Generic HTTP protocol class.  Useful for making http based requests
 *
 * @package GameQ\Protocols
 * @author  Austin Bischoff <austin@codebeard.com>
 */
abstract class Http extends Protocol
{
    /**
     * The query protocol used to make the call
     */
    protected string $protocol = 'http';

    /**
     * String name of this protocol class
     */
    protected string $name = 'http';

    /**
     * Longer string name of this protocol class
     */
    protected string $name_long = "Generic HTTP protocol";

    /**
     * Http protocol is TCP
     */
    protected string $transport = self::TRANSPORT_TCP;

    /**
     * The client join link
     */
    protected ?string $join_link = null;
}