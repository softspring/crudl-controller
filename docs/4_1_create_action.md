# Create action

Create action is used to create a new entity and provide a form to fill it, and save it.

## Workflow

All this steps are performed by the controller, if they are configured:

- Dispatch initialize event (allows to setResponse and stop the process)
- Dispatch create entity event, if no entity is setted in the event, create a new one calling to the manager
- Check is granted
- Dispatches form prepare event (allows to create form or modify form options)
- Creates the form
- Dispatches form init
- Handles form submit
  - If form is valid:
    - Dispatches form valid event (allows to setResponse and stop the process)
    - Dispatches apply event, if no apply flag is setted in the event, calls to the manager to persist the entity
    - Dispatches success event (allows to setResponse and stop the process)
    - Redirects to success route if configured, or a default route
    - If any of these steps fails, dispatches failure event
  - If form is invalid:
    - Dispatches form invalid event (allows to setResponse and stop the process)
- Creates view data
- Dispatches view event
- Renders view

If any exception is thrown during the process, dispatches exception event.

## Configurations

### entity_attribute

**type**: string **default**: 'entity'

The name of entity field passed to the view, and used for routes

### is_granted

**type**: string **default**: null

Role name to check at the beginning

### form

**type**: string **default**: null

The form class name

### view

**type**: string **default**: null

The view path for rendering list

### success_redirect_to

**type**: string **default**: null

Route name to redirect o success

## Events configuration

All events are optional, you can configure just the ones you need.

### initialize_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\InitializeEvent](src/Event/InitializeEvent.php)

Event dispatched after checking is_granded and before form processing.
  
It allows to setResponse and stop the process, for example, to redirect on custom situation.

**Example**

```yaml
$configs:
    create:
        initialize_event_name: 'product_admin.create.initialize'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.initialize, method: onInitialize }
```

The following example shows how to redirect to other route if some condition is met:

```php
<?php

namespace App\EventListener;

class ProductCreateListener
{
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
    }
}
```

### create_entity_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\CreateEntityEvent](src/Event/CreateEntityEvent.php)

Event dispatched on creating entity.

It's used to generate a new entity if needed.

**Example**

```yaml
$configs:
    create:
     create_entity_event_name: 'product_admin.create.create_entity'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.create_entity, method: onCreateEntity }
```

The following example shows how to create a new entity and initialize some fields:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\CreateEntityEvent;

class ProductCreateListener
{
    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $product = $this->manager->createEntity();
        
        $product->setActive(false)
        
        $event->setEntity($product);
    }
}
```

### form_prepare_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\FormPrepareEvent](src/Event/FormPrepareEvent.php)

Event dispatched before form is created.

It's used to generate form options.

**Example**

```yaml
$configs:
    create:
        form_prepare_event_name: 'product_admin.create.form_prepare'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.form_prepare, method: onFormPrepare }
```

The following example shows how to add validation groups to form:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormPrepareEvent;

class ProductCreateListener
{
    public function onFormPrepare(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'validation_groups' => ['Default', 'create'],
        ]);
        
        $formOptions = $event->getFormOptions();
    }
}
```

### form_init_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\FormEvent](src/Event/FormEvent.php)

Event dispatched after form creation but before process it.

It allows to modify form.

**Example**

```yaml
$configs:
    create:
        form_init_event_name: 'product_admin.create.form_init'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.form_init, method: onFormInit }
```

The following example shows how to add a custom field to form:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormEvent;

class ProductCreateListener
{
    public function onFormInit(FormEvent $event): void
    {
        $request = $event->getRequest();
        
        $form = $event->getForm();
        $form->add('custom_field', TextType::class, [
            'label' => 'Custom field',
        ]);
    }
}
```

### form_valid_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\FormValidEventFormValidEventApplyEvent](src/Event/FormValidEvent.php)

Event dispatched on form submitted and valid, but before performing save.

It allows to setResponse and stop the process or redirect, also modify model before saving it, for example.

**Example**

```yaml
$configs:
    create:
        form_valid_event_name: 'product_admin.create.form_valid'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.form_valid, method: onFormValid }
```

The following example shows how to modify a product field or redirect to other route if some condition is met:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormValidEventApplyEvent;

class ProductCreateListener
{
    public function onFormValid(FormValidEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $form = $event->getForm();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $product->setCustomField('changed value');
        }
    }
}
```

### apply_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\ApplyEvent](src/Event/ApplyEvent.php)

Event dispatched when form is valid and allows to perform entity changes before saving, or change the save process to a custom one.

It allows to setApplied and skip the default saving process.

**Example**

```yaml
$configs:
    create:
        apply_event_name: 'product_admin.create.apply'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.apply, method: onApply }
```

The following example shows how to save the entity to an API instead of database:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\ApplyEvent;

class ProductCreateListener
{
    public function onApply(ApplyEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $form = $event->getForm();
        
        $this->apiClient->createProduct($product);
        
        $event->setApplied(true); // skip default saving process
    }
}
```

### success_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\GetResponseEntityEvent](src/Event/GetResponseEntityEvent.php)

Event dispatched on form submitted and valid, and after saving new entity.

It allows to setResponse and stop the process or redirect, also fire other actions after saving, for example.

**Example**

```yaml
$configs:
    create:
        success_event_name: 'product_admin.create.success'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.success, method: onSuccess }
```

The following example shows how to redirect to other route if some condition is met, or dispatch another event:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;

class ProductCreateListener
{
    public function onSuccess(GetResponseEntityEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other/'.$product->getId())));
        } else {
            $this->dispatcher->dispatch(new ProductCreatedEvent($product));
        }
    }
}
```

### failure_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\FailureEvent](src/Event/FailureEvent.php)

Event dispatched if an exception is thrown during entity saving or success event.

**Example**

```yaml
$configs:
    create:
        failure_event_name: 'product_admin.create.failure'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.failure, method: onFailure }
```

The following example shows how to redirect to other route if some condition is met, or log the failure:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FailureEvent;

class ProductCreateListener
{
    public function onFailure(FailureEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $exception = $event->getException();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->logger->error('Error creating product', [
                'exception' => $exception,
                'product' => $product,
            ]);
        }
    }
}
```

### form_invalid_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\FormInvalidEvent](src/Event/FormInvalidEvent.php)

Event dispatched on form submitted and invalid.

It allows to setResponse and stop the process or redirect, also process form errors, for example.

**Example**

```yaml
$configs:
    create:
        form_invalid_event_name: 'product_admin.create.form_invalid'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.form_invalid, method: onFormInvalid }
```

The following example shows how to redirect to other route if some condition is met, or process form errors:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormInvalidEventFormValidEventFormInvalidEvent;

class ProductCreateListener
{
    public function onFormInvalid(FormInvalidEvent $event): void
    {
        $request = $event->getRequest();
        $form = $event->getForm();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->processFormErrors($form);
        }
    }
}
```

### view_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\ViewEvent](src/Event/ViewEvent.php)

Event dispatched before rendering view. View is rendered when no submit is performed, form is invalid or an exception 
 has been produced during form processing, and none of events allowed to setResponse has set it.

It allows data adding for the view.

**Example**

```yaml
$configs:
    create:
        view_event_name: 'product_admin.create.view'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.view, method: onView }
```

The following example shows how to add data to view:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\ViewEvent;

class ProductCreateListener
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
{# templates/admin/products/create.html.twig #}

{{ some_data }}
```

### exception_event_name


**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\ExceptionEvent](src/Event/ExceptionEvent.php)

Event dispatched if an exception is thrown during any of the process steps.

**Example**

```yaml
$configs:
    create:
        failure_event_name: 'product_admin.create.exception'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductCreateListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.create.exception, method: onException }
```

The following example shows how to redirect to other route if some condition is met, or log the exception:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\ExceptionEvent;

class ProductCreateListener
{
    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $exception = $event->getException();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->logger->error('Error creating product', [
                'exception' => $exception,
                'product' => $product,
            ]);
        }
    }
}
```

## Configuration reference

This is the list action configuration reference:

```yaml
$configs:
    create:
        entity_attribute: 'product'
        is_granted: 'ROLE_ADMIN_PRODUCT_CREATE'
        form: 'App\Form\ProductCreateForm'
        view: 'admin/products/create.html.twig'
        success_redirect_to: 'app_admin_product_list'
        
        # events
        initialize_event_name: 'product_admin.create.initialize'
        create_entity_event_name: 'product_admin.create.create_entity'
        form_prepare_event_name: 'product_admin.create.form_prepare'
        form_init_event_name: 'product_admin.create.form_init'
        form_valid_event_name: 'product_admin.create.form_valid'
        apply_event_name: 'product_admin.create.apply'
        success_event_name: 'product_admin.create.success'
        failure_event_name: 'product_admin.create.failure'
        form_invalid_event_name: 'product_admin.create.form_invalid'
        view_event_name: 'product_admin.create.view'
        exception_event_name: 'product_admin.create.exception'
```

And this is a complete example of event listening:

```php
<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductCreateListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'product_admin.create.initialize' => 'onInitialize',
            'product_admin.create.create_entity' => 'onCreateEntity',
            'product_admin.create.form_prepare' => 'onFormPrepare',
            'product_admin.create.form_init' => 'onFormInit',
            'product_admin.create.form_valid' => 'onFormValid',
            'product_admin.create.apply' => 'onApply',
            'product_admin.create.success' => 'onSuccess',
            'product_admin.create.failure' => 'onFailure',
            'product_admin.create.form_invalid' => 'onFormInvalid',
            'product_admin.create.view' => 'onView',
            'product_admin.create.exception' => 'onException',
        ];
    }
    
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
    }
    
    public function onCreateEntity(CreateEntityEvent $event): void
    {
        $product = $this->manager->createEntity();
        
        $product->setActive(false)
        
        $event->setEntity($product);
    }
    
    public function onFormPrepare(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'validation_groups' => ['Default', 'create'],
        ]);
        
        $formOptions = $event->getFormOptions();
    }
    
    public function onFormInit(FormEvent $event): void
    {
        $request = $event->getRequest();
        
        $form = $event->getForm();
        $form->add('custom_field', TextType::class, [
            'label' => 'Custom field',
        ]);
    }
    
    public function onFormValid(FormValidEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $form = $event->getForm();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $product->setCustomField('changed value');
        }
    }
    
    public function onApply(ApplyEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $form = $event->getForm();
        
        $this->apiClient->createProduct($product);
        
        $event->setApplied(true); // skip default saving process
    }
    
    public function onSuccess(GetResponseEntityEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other/'.$product->getId())));
        } else {
            $this->dispatcher->dispatch(new ProductCreatedEvent($product));
        }
    }
    
    public function onFailure(FailureEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $exception = $event->getException();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->logger->error('Error creating product', [
                'exception' => $exception,
                'product' => $product,
            ]);
        }
    }
    
    public function onFormInvalid(FormInvalidEvent $event): void
    {
        $request = $event->getRequest();
        $form = $event->getForm();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->processFormErrors($form);
        }
    }
    
    public function onView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $data = $event->getData();
        
        $data->set('some_data', 'some_value');
    }
    
    
    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $exception = $event->getException();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->logger->error('Error creating product', [
                'exception' => $exception,
                'product' => $product,
            ]);
        }
    }
} 
```