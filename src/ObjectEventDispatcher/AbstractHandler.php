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

namespace Gdl\Pimcore\ObjectEventDispatcher;

use Pimcore\Tool\Admin;

class AbstractHandler
{

    /**
     * The previous version of the object
     * @var $old \Pimcore\Model\Object\Concrete
     */
    public $old;

    /**
     * The object data just saved
     * @var $new \Pimcore\Model\Object\Concrete
     */
    public $new;

    /**
     * @var $user \Pimcore\Model\User
     */
    public $user;

    public $function;

    /**
     * A generic implementation of an object event handler
     *
     * @param $event \Zend_EventManager_Event
     * @param $function
     */
    public function __construct($event, $function)
    {
        $this->event = $event;
        $this->new = $event->getTarget();
        $this->old = $this->initOldVersion();
        $this->user = Admin::getCurrentUser();
        $this->function = $function;
    }

    /**
     * Returns the previous version of an object if it can be loaded succcessfully
     *
     * @return \Pimcore\Model\Object\Concrete|null
     */
    protected function initOldVersion()
    {
        try {
            $versions = $this->new->getVersions();
        } catch(\Exception $e) {
            // in case no versions are available or a version fetch fails
            // set versions as empty array
            $versions = [];
        }

        $previousVersion = null;

        //get the previous versions no matter what
        if (count($versions)) {
            $previousVersion = $versions[0];
        }

        if (!$previousVersion) {
            return null; //no old version
        }

        /**
         * @var \Pimcore\Model\Version $previousVersion
         */
        try {
            return $previousVersion->loadData();
        } catch (\Exception $e) {
            // also catch any issues with loading the actual version
            return null;
        }
    }

    /**
     *
     */
    public function init($eventName)
    {
        return $this->$eventName();
    }

    public function preAdd()
    {
        return true;
    }

    public function postAdd()
    {
        return true;
    }

    public function preUpdate()
    {
        return true;
    }

    public function postUpdate()
    {
        return true;
    }

    public function preDelete()
    {
        return true;
    }

    public function postDelete()
    {
        return true;
    }

}
