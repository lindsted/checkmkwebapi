<?php

/**
 * Copyright (C) 2018-19 Benjamin Heisig
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Benjamin Heisig <https://benjamin.heisig.name/>
 * @copyright Copyright (C) 2018-19 Benjamin Heisig
 * @license http://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License (AGPL)
 * @link https://github.com/bheisig/checkmkwebapi
 */

namespace bheisig\checkmkwebapi;

/**
 * Handle Python dictionaries
 */
class Python {

    /**
     * Convert Python dictionary to PHP array
     *
     * @param string $value Python foo
     *
     * @return array|null Result as array, otherwise null
     */
    public static function decode($value) {
        // Remove line breaks:
        $value = preg_replace('/\s+/', ' ', $value);
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);

        $value = str_replace(
            ['\'', 'True', 'False', 'None', '": u"', ', u"'],
            ['"', 'true', 'false', 'null', '": "', ', "'],
            $value
        );
        if (!is_string($value)) {
            return null;
        }

        // null
        // true
        // false
        // 123
        // 123.45
        // "abc_123.456-789"
        // JSON array (with brackets "[" and "]")
        // JSON object (with brackets "{" and "}")
        $element = '(null|true|false|[0-9]+|[0-9]+\.[0-9]+|"[a-zA-Z0-9\._\-]*"|\[.*\]|{.+})';

        // Convert nested tupels ((1, 2), (3, 4)) into arrays:
        $value = preg_replace(
            "/\(\($element, $element\), \($element, $element\)\)/i",
            '[[$1, $2], [$3, $4]]',
            $value
        );
        if (!is_string($value)) {
            return null;
        }

        $count = 1;

        while ($count > 0) {
            // Convert tupels like…
            // (1, 2)
            // ('abc', 123)
            // ('foo', ('bar', 'baz'))
            $value = preg_replace(
                "/\($element, $element\)/i",
                '[$1, $2]',
                $value,
                -1,
                $count
            );
            if (!is_string($value)) {
                return null;
            }
        }

        $value = utf8_encode($value);

        $decoded = json_decode(
            $value,
            true
        );

        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

}
