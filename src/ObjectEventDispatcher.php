<?php

/**
 * ObjectEventDispatcher
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2017 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore;

class ObjectEventDispatcher
{

    /**
     * @var ObjectEventDispatcher $objectEventDispatcher
     */
    private static $objectEventDispatcher;

    /**
     * @var mixed $config
     */
    private $config;

    /**
     * @var bool $isInitialised
     */
    private $isInitialised;

    /**
     * @var bool $disabled
     */
    private $disabled;

    private function __construct()
    {

    }

    /**
     * @param $event \Zend_EventManager_Event
     * @return bool
     * @throws \Exception
     */
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
        $classname = get_class($target);

        if (array_key_exists($classname, $config['handlers'])) {

            $handlerclass = $config['handlers'][$classname];

            $eventClass = new $handlerclass($event, $eventFunction);
            if (!$eventClass instanceof ObjectEventDispatcher\AbstractHandler) {
                throw new \Exception('Event Handler class needs to extend \'Gdl\\Pimcore\\ObjectEventDispatcher\\AbstractHandler\'');
            }

            return $eventClass->init($eventFunction);
        }

        return true;
    }

    public static function isDisabled()
    {
        return self::getInstance()->disabled === true;
    }

    public static function getInstance()
    {
        if (self::$objectEventDispatcher === null) {
            self::$objectEventDispatcher = new self;
        }

        return self::$objectEventDispatcher;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Disables translation tasks for the current request
     */
    public static function disable()
    {
        self::getInstance()->disabled = true;
    }

    public static function enable()
    {
        self::getInstance()->disabled = false;
    }

    /**
     * Registers itself with the Pimcore event manager
     */
    public function initialise()
    {
        if ($this->isInitialised) {
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

        $this->isInitialised = true;
    }

    public function addEventHandler($objectClass, $eventHandlerClass)
    {
        $classDefinition = \Pimcore\Model\Object\ClassDefinition::getByName($objectClass);
        if (!$classDefinition) {
            throw new \Exception('Specified Pimcore Class Definition does not exist');
        }

        // check for classmapping to return the correct class
        $pimcoreClass = 'Pimcore\\Model\\Object\\' . ucfirst($objectClass);

        if (!\Pimcore\Tool::classExists($pimcoreClass)) {
            throw new \Exception('Specified Pimcore Class Definition does not exist');
        } else {
            if (!\Pimcore\Tool::classExists($eventHandlerClass)) {
                throw new \Exception('Specified Event Handler class does not exist');
            }
        }

        $instance = \Pimcore::getDiContainer()->make($pimcoreClass);
        $pimcoreClass = get_class($instance);
        unset($instance);

        $this->config['handlers'][$pimcoreClass] = $eventHandlerClass;
    }

}
