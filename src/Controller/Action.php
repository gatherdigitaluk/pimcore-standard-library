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

namespace Gdl\Pimcore\Controller;

use Pimcore\Controller\Action as PimcoreAction;
use Pimcore\Model\Object;

class Action extends PimcoreAction
{

    public function init()
    {
        parent::init();

        if (\Zend_Registry::isRegistered("Zend_Locale")) {
            $locale = \Zend_Registry::get("Zend_Locale");
        } else {
            $locale = new \Zend_Locale("en");
            \Zend_Registry::set("Zend_Locale", $locale);
        }

        $this->view->language = (string) $locale;
        $this->language = (string) $locale;
    }


    /**
     * Function to help secure a controllers scope by returning parameters only relevant to
     * a specific given Pimcore Class Definition.
     * @throws \Exception
     * @param string $classname
     * @return array
     */
    protected function getRelevantParams($classname)
    {
        $class = Object\ClassDefinition::getByName($classname);
        if (!$class) {
            throw new \Exception("Classname {$classname} is not a recognised Pimcore Class");
        }

        $defs = array_keys($class->getFieldDefinitions());
        $relParams = [];

        foreach($this->_request->getParams() as $key=>$value) {
            if (in_array($key, $defs)) {
                $relParams[$key] = $value;
            }
        } unset($key, $value);

        return $relParams;
    }

}