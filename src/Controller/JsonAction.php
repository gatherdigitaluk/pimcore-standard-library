<?php

/**
 * JsonAction
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore\Controller;

class JsonAction extends Action
{

    /**
     * @var string $callbackString
     */
    private $callbackString;

    /**
     * @var int $responseStatusCode
     */
    protected $responseStatusCode;

    /**
     * init
     */
    public function init()
    {
        parent::init();

        $this->callbackString = $this->_request->getParam('callback');

        $this->disableLayout();
        $this->removeViewRenderer(true);

    }

    /**
     * Creates a json response optionally wrapping it inside a callback
     * @param mixed $data
     */
    public function respond($data, $sendNow=false, $alreadyJson=false, $afterCallbackJS=null)
    {
        $response = $this->getResponse();

        if (strlen($this->callbackString)) {
            $response->setHeader('Content-Type', 'text/javascript', true);
            $body = $this->callbackString . '(' . ( ($alreadyJson) ? $data : json_encode($data) ) . ');';

            //include additional body after the callback
            //handy for injecting JS ;-)
            if (!empty($afterCallbackJS)) {
                $body .= $afterCallbackJS;
            }

        } else {
            $response->setHeader('Content-Type','application/x-json', true);

            if ($this->getParam('disableAccessControl', false)) {
                $response->setHeader('Access-Control-Allow-Origin', '*', true);
                $response->setHeader('Access-Control-Allow-Headers', 'origin, x-requested-with, content-type, accept', true);
            }

            $body = ($alreadyJson) ? $data : json_encode($data);
        }

        $response->setBody($body);

        if ($sendNow) {
            $response->sendResponse();
            exit;
        }
    }


}