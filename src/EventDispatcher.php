<?php

/**
 * EventDispatcher
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore;

class EventDispatcher
{

    /**
     * @var mixed $config
     */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;

        if (!is_array($this->config)) {
            $this->config['handlers'] = [];
        }

    }

    /**
     * Registers itself with the Pimcore event manager
     */
    public function initialise()
    {
        if (\Zend_Registry::isRegistered('gdl_pimcore_eventdispatcher')) {
            throw new \Exception('Dispatcher is already initialised');
        }

        $eventManager = \Pimcore::getEventManager();
        $eventDispatcherService = '\\Gdl\\Pimcore\\EventDispatcher\\Service';

        //Documents
        $eventManager->attach("document.preAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("document.postAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("document.preUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("document.postUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("document.preDelete", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("document.postDelete", [__CLASS__, 'dispatchEvent']);

        //Object
        $eventManager->attach("object.preAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.postAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.preUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.postUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.preDelete", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.postDelete", [__CLASS__, 'dispatchEvent']);

        //Asset
        $eventManager->attach("asset.preAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("asset.postAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("asset.preUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("asset.postUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("asset.preDelete", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("asset.postDelete", [__CLASS__, 'dispatchEvent']);

    }

    public function addEventHandler($pimcoreClass, $eventHandlerClass)
    {
        if (!\Pimcore\Tool::classExists($pimcoreClass)) {
            throw new \Exception('Specified Pimcore Class does not exist');
        } else if (!\Pimcore\Tool::classExists($eventHandlerClass)){
            throw new \Exception('Specified Event Handler class does not exist');
        }

        $this->config['handlers'][$pimcoreClass] = $eventHandlerClass;
    }


    public function dispatchEvent($event)
    {
        if (self::isDisabled()) {
            return true;
        }

        $target = $event->getTarget();
        if (!$target) {
            return true;
        }

        $eventName = $event->getName();
        $eventFunction = @end(explode('.', $eventName));
        if (!$eventFunction) {
            return true;
        }

        $classname = get_class($target);
        if (array_key_exists($classname, $this->config['handlers'])) {
            $handlerclass = $this->config['handlers'][$classname];

            $eventClass = new $handlerclass();
            if (!$eventClass instanceof EventDispatcher\AbstractHandler) {
                throw new \Exception('Event Handler class needs to extend \'Gdl\\Pimcore\\EventDispatcher\\AbstractHandler\'');
            }
            return $eventClass->init($eventFunction);
        }

    }


    /**
     * Disables translation tasks for the current request
     */
    public static function disable()
    {
        \Zend_Registry::set('gdl_pimcore_event_disable', true);
    }

    public static function enable()
    {
        \Zend_Registry::set('gdl_pimcore_event_disable', false);
    }

    public static function isDisabled()
    {
        return (\Zend_Registry::isRegistered('gdl_pimcore_event_disable') && \Zend_Registry::get('gdl_pimcore_event_disable'));
    }

}