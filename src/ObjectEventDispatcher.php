<?php

/**
 * ObjectEventDispatcher
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore;

class ObjectEventDispatcher
{

    public static $objectEventDispatcher;

    /**
     * @var mixed $config
     */
    protected $config;

    public function __construct($config = null)
    {
        $this->config = $config;

        if (!is_array($this->config)) {
            $this->config['handlers'] = [];
        }

    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Registers itself with the Pimcore event manager
     */
    public function initialise()
    {
        if (isset(ObjectEventDispatcher::$eventDispatcher)) {
            throw new \Exception('ObjectEventDispatcher already setup');
        }

        $eventManager = \Pimcore::getEventManager();

        //Object
        $eventManager->attach("object.preAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.postAdd", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.preUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.postUpdate", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.preDelete", [__CLASS__, 'dispatchEvent']);
        $eventManager->attach("object.postDelete", [__CLASS__, 'dispatchEvent']);

        ObjectEventDispatcher::$objectEventDispatcher = $this;
    }

    public function addEventHandler($objectClass, $eventHandlerClass)
    {
        $classDefinition = \Pimcore\Model\Object\ClassDefinition::getByName($objectClass);
        if (!$classDefinition) {
            throw new \Exception('Specified Pimcore Class Definition does not exist');
        }

        $pimcoreClass = '\\Pimcore\\Model\\Object\\' . $objectClass;

        if (!\Pimcore\Tool::classExists($pimcoreClass)) {
            throw new \Exception('Specified Pimcore Class Definition does not exist');
        } else {
            if (!\Pimcore\Tool::classExists($eventHandlerClass)) {
                throw new \Exception('Specified Event Handler class does not exist');
            }
        }

        $this->config['handlers'][$pimcoreClass] = $eventHandlerClass;
    }


    public static function dispatchEvent($event)
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

        $config = ObjectEventDispatcher::$objectEventDispatcher->getConfig();
        $classname = '\\' . get_class($target);

        if (array_key_exists($classname, $config['handlers'])) {

            $handlerclass = $config['handlers'][$classname];

            $eventClass = new $handlerclass($event, $eventFunction);
            if (!$eventClass instanceof ObjectEventDispatcher\AbstractHandler) {
                throw new \Exception('Event Handler class needs to extend \'Gdl\\Pimcore\\ObjectEventDispatcher\\AbstractHandler\'');
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