# Read action

Read action is used to load an existing entity and show it.

## Configurations

### param_converter_key

**type**: string **required**

The id field name used for quering

### view

**type**: string **required**

The view path for rendering list

### entity_attribute

**type**: string **default**: 'entity'

The name of entity field passed to the view, and used for routes

### is_granted

**type**: string **default**: null

Role name to check at the begining

## Events configuration

All events are optional, you can configure just the ones you need.

### not_found_event_name

**type**: string **default**: null **event**: Softspring\Component\Events\GetResponseRequestEvent

Event dispatched before initialize anything, and after checking is_granted, to prevent inform about an entity existance.
  
It allows to setResponse and stop the process, for example, to redirect if not found.

If no event is configured, the controller will throw a NotFoundHttpException.

**Example**

```yaml
$configs:
    read:
        not_found_event_name: 'product_admin.read.not_found'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductReadListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.read.not_found, method: onNotFound }
```

The folloging example shows how to redirect to other route if an entity is not found:

```php
<?php

namespace App\EventListener;

class ProductReadListener
{
    public function onNotFound(GetResponseRequestEvent $event): void
    {
        $event->setResponse(new RedirectResponse('/')));
    }
}
```

### initialize_event_name

**type**: string **default**: null **event**: Softspring\Component\Events\GetResponseRequestEvent

Event dispatched after checking is_granded and before form processing.
  
It allows to setResponse and stop the process, for example, to redirect on custom situation.

**Example**

```yaml
$configs:
    read:
        initialize_event_name: 'product_admin.read.initialize'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductReadListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.read.initialize, method: onInitialize }
```

The folloging example shows how to redirect to other route if some condition is met:

```php
<?php

namespace App\EventListener;

class ProductReadListener
{
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
    }
}
```

### view_event_name

**type**: string **default**: null **event**: Softspring\Component\Events\ViewEvent

Event dispatched before rendering view. View is rendered when no submit is performed, form is invalid or an exception 
 has been produced during form processing, and none of events allowed to setResponse has set it.

It allows data adding for the view.

**Example**

```yaml
$configs:
    read:
        view_event_name: 'product_admin.read.view'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductReadListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.read.view, method: onView }
```

The following example shows how to add data to view:

```php
<?php

namespace App\EventListener;

use Softspring\Component\Events\ViewEvent;

class ProductReadListener
{
    public function onView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getData();
        
        $data->set('some_data', 'some_value');
    }
}
```

```twig
{# templates/admin/products/read.html.twig #}

{{ some_data }}
```


## Configuration reference

This is the list action configuration reference:

```yaml
$configs:
    read:
        # required fields
        param_converter_key: 'id'
        view: 'admin/products/read.html.twig'
        
        # optional fields
        entity_attribute: 'product'
        is_granted: 'ROLE_ADMIN_PRODUCT_READ'
        
        # events
        not_found_event_name: 'product_admin.read.not_found'
        initialize_event_name: 'product_admin.read.initialize'
        view_event_name: 'product_admin.read.view'
```

And this is a complete example of event listening:

```php
<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductReadListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'product_admin.read.not_found' => 'onNotFound',
            'product_admin.read.initialize' => 'onInitialize',
            'product_admin.read.view' => 'onView',
        ];
    }
    
    public function onNotFound(GetResponseRequestEvent $event): void
    {
        $event->setResponse(new RedirectResponse('/')));
    }
    
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
    }
    
    public function onView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getData();
        
        $data->set('some_data', 'some_value');
    }
} 
```