<?php

/**
 * Location
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore\Tool;

use Pimcore\Model\Object\Data\Geopoint;

class Location
{

    /**
     * Uses the geocode.gatherdigital.co.uk endpoint to
     * return a location
     * @param string $postcode
     * @return Geopoint|bool
     */
    public static function getGeopointFromPostcode($postcode)
    {
        $result = self::geocodePostcode($postcode);
        if (!$result || empty($result->latitude)) {
            return false;
        }

        return new Geopoint($result->longitude, $result->latitude);
    }

    /**
     * Return a geocoded point from Gathers geocode lookup
     * @param string $postcode
     * @return bool|mixed
     * @throws Location\Exception
     */
    public static function geocodePostcode($postcode)
    {
        $response = \Pimcore\Tool::getHttpData("http://geocode.gatherdigital.co.uk/lookup.php", [
            'q' => $postcode
        ]);

        if ($response) {
            throw new Location\Exception('Could not retrieve a location, check the API status');
        }

        return json_decode($response);
    }

    /**
     * Formats a British postcode (and validates)
     * @param string $postcode
     * @return bool|string
     */
    public static function formatBritishPostcode($postcode)
    {
        //--------------------------------------------------
        // Clean up the user input

        $postcode = strtoupper($postcode);
        $postcode = preg_replace('/[^A-Z0-9]/', '', $postcode);
        $postcode = preg_replace('/([A-Z0-9]{3})$/', ' \1', $postcode);
        $postcode = trim($postcode);

        //--------------------------------------------------
        // Check that the submitted value is a valid
        // British postcode: AN NAA | ANN NAA | AAN NAA |
        // AANN NAA | ANA NAA | AANA NAA

        if (preg_match('/^[a-z](\d[a-z\d]?|[a-z]\d[a-z\d]?) \d[a-z]{2}$/i', $postcode)) {
            return $postcode;
        }

        return false;
    }

    /**
     * Returns the postcode sector from a valid postcode
     * @param string $postcode
     * @return string|bool
     */
    public static function getPostcodeSector($postcode)
    {
        $postcode = self::formatBritishPostcode($postcode);

        return ($postcode) ? explode(' ', $postcode)[0] : false;
    }


}