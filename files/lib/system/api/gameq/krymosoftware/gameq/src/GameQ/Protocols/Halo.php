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

/**
 * Halo: Combat Evolved Protocol Class
 *
 * @author Wilson Jesus <>
 */
class Halo extends Gamespy2
{

    /**
     * String name of this protocol class
     */
    protected string $name = 'halo';

    /**
     * Longer string name of this protocol class
     */
    protected string $name_long = "Halo: Combat Evolved";
}