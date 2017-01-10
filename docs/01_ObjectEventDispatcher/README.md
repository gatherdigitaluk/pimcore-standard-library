# ObjectEventDispatcher
The ObjectEventDispatcher is a singleton class that helps with Pimcore object event delegation. By attaching this class to a Pimcore request you can assign events to happen to specific objects without having the check the type.

## Usage
To use the ObjectEventDispatcher, we attach a delegate class to an Object type, such as a **Product**.
The delegate class is one which you create to handle events on Pimcore objects. It MUST extend the **ObjectEventDispatcher\AbstractHandler** class, which implements
methods for all Pimcore object events.

The AbstractHandler Class provides access to the current version of the object, and the previous version 
of the object, meaning you can very quickly compare object changes.


### Example
Here's a  full example for events handling on the object class **Product**.

#### 1. Create a handler class
Create the class to handle your events, in this example we will handle the preUpdate event only.
We will check for a price change.

```php
<?php

namespace Shop\Event;

class ProductHandler extends \Gdl\Pimcore\ObjectEventDispatcher\AbstractHandler
{

    public function preUpdate()
    {
        if($this->new->getPrice() !== $this->old->getPrice()) {
            // do something with about a price change!
        }
    }

}

```

#### 2. Attach the EventDispatcher to the Pimcore startup
The most straightforward attachment is within the startup.php file, we grab an instance of
the EventDispatcher, and then add an event handler for a class. Finally when we have finished
adding all the handlers, we call initialise.
 
```php
$eventDispatcher = \Gdl\Pimcore\ObjectEventDispatcher::getInstance();
$eventDispatcher->addEventHandler('Product', '\\Shop\\Event\\ProductHandler');
$eventDispatcher->initialise();
```

*NOTE: The handlers will automatically check for DI.*