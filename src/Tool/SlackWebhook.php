<?php

/**
 * Action
 *
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore\Tool;

use Pimcore\Config;

class SlackWebhook
{

    /**
     * Posts a webhook to slack
     * @param        $text
     * @param array|null   $fields
     * @param string|null   $fallback
     * @param string $username
     * @param string|null   $icon
     * @return string
     */
    public static function post($text, $fields=null, $fallback=null, $username='Slack Webhook', $icon=null)
    {
        if (self::canPostWebhook()) {
            \Logger::error('SlackWebhook class could not find webhook URL, check \'slack_webhook_url\' exists as a website setting');
        }

        $url = Config::getWebsiteConfig()->get('slack_webhook_url');
        $payload = [
            'text' => $text,
            'username' => $username
        ];

        if (is_array($fields) && $fallback) {
            $payload['attachments'] = [
                [
                    'fallback' => $fallback,
                    'fields' => $fields
                ]
            ];
        }

        if ($icon) {
            $payload['icon_emoji'] = $icon;
        }

        return \Pimcore\Tool::getHttpData($url, null, ['payload' => json_encode($payload)]);
    }

    /**
     * Checks if the current pimcore install can post a slack message
     * @return bool
     */
    public static function canPostWebhook()
    {
        return strlen(Config::getWebsiteConfig()->get('slack_webhook_url')) > 0;
    }



}