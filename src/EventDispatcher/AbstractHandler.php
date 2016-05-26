<?php

/**
 * AbstractHandler
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore\EventDispatcher;

use Pimcore\Tool\Admin;

class AbstractHandler
{


    /**
     * The previous version of the object
     */
    public $old;

    /**
     * The object data just saved
     */
    public $new;

    /**
     * @var $user \Pimcore\Model\User
     */
    public $user;

    public $function;

    /**
     * A generic implementation of an object event handler
     * @param $event
     * @param $function
     */
    public function __construct($event, $function)
    {
        $this->new = $event->getTarget();
        $this->old = $this->initOldVersion();
        $this->user = Admin::getCurrentUser();
    }

    /**
     *
     */
    public function init($eventName)
    {
        return $this->$eventName();
    }

    /**
     * Returns the previous version of an object
     *
     * @return \Pimcore\Model\Object
     */
    protected function initOldVersion()
    {
        $versions = $this->new->getVersions();
        $previousVersion = null;

        //get the previous versions no matter what
        if(count($versions)) {
            $previousVersion = $versions[0];
        }

        if(!$previousVersion) {
            return null; //no old version
        }

        /**
         * @var \Pimcore\Model\Version $previousVersion
         */
        return $previousVersion->loadData();
    }

    /**
     * @param $event
     */
    public function preAdd($event) {
        return true;
    }

    /**
     * @param $event
     */
    public function postAdd($event) {
        return true;
    }

    /**
     * @param $event
     */
    public function preUpdate($event) {
        return true;
    }

    /**
     * @param $event
     */
    public function postUpdate($event) {
        return true;
    }

    /**
     * @param $event
     */
    public function preDelete($event) {
        return true;
    }

    /**
     * @param $event
     */
    public function postDelete($event) {
        return true;
    }

}