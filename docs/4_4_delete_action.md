# Delete action

Delete action is used to remove an existing entity, after submitting a form.

This form can help to prevent accidental deletion, to ask for confirmation, or configure some actions to be performed.

The configuration is the same as [update action](docs/4_3_update_action.md).

## Workflow

All these steps are performed by the controller, if they are configured (differences with create action are marked in bold):

- Dispatch initialize event (allows to setResponse and stop the process)
- Dispatch load entity event, if no entity is setted in the event, search for entity using param_converter_key with the manager
- Check is granted
- If no entity is found
  - dispatches not found entity event (allows to setResponse and stop the process)
  - if no response is setted, throws a NotFoundHttpException
- else, if entity is found
  - dispatches found entity event (allows to setResponse and stop the process)
- Dispatches form prepare event (allows to create form or modify form options)
- Creates the form
- Dispatches form init
- Handles form submit
  - If form is valid:
    - Dispatches form valid event (allows to setResponse and stop the process)
    - Dispatches apply event, if no apply flag is setted in the event, calls to the manager to delete the entity
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

### param_converter_key

**type**: string **default**: null

The field name used to load the entity from the request (should be used in routes)

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
    delete:
        initialize_event_name: 'product_admin.delete.initialize'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.initialize, method: onInitialize }
```

The following example shows how to redirect to other route if some condition is met:

```php
<?php

namespace App\EventListener;

class ProductDeleteListener
{
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
    }
}
```

### load_entity_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\LoadEntityEvent](src/Event/LoadEntityEvent.php)

Event dispatched on entity loading.

It's used to load the entity if needed.

**Example**

```yaml
$configs:
    delete:
     load_entity_event_name: 'product_admin.delete.load_entity'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.load_entity, method: onLoadEntity }
```

The following example shows how to search entity with a custom repository method:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\LoadEntityEvent;

class ProductDeleteListener
{
    public function onLoadEntity(LoadEntityEvent $event): void
    {
        $event->setEntity($this->manager->getRepository()->findOneByCustomField($event->getRequest()->get('custom_field')));
    }
}
```

### found_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\\Softspring\Component\CrudlController\Event\EntityFoundEvent](src/Event/\Softspring\Component\CrudlController\Event\EntityFoundEvent.php)

Event dispatched if entity is found.

**Example**

```yaml
$configs:
    delete:
     found_event_name: 'product_admin.delete.found'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.found, method: onFound }
```

The following example shows how to log the found entity:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\EntityFoundEvent;

class ProductDeleteListener
{
    public function onFound(EntityFoundEvent $event): void
    {
        $this->logger->debug(sprintf('Entity found: %s', $event->getEntity()->getId()));
    }
}
```

### not_found_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\NotFoundEvent](src/Event/NotFoundEvent.php)

Event dispatched on entity not found.

**Example**

```yaml
$configs:
    delete:
     not_found_event_name: 'product_admin.delete.not_found'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.not_found, method: onNotFound }
```

The following example shows how to set a flash message and redirect to other route:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\NotFoundEvent;

class ProductDeleteListener
{
    public function onNotFound(NotFoundEvent $event): void
    {
        $this->flashBag->add('error', 'Product not found');
        $event->setResponse(new RedirectResponse('/other')));
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
    delete:
        form_prepare_event_name: 'product_admin.delete.form_prepare'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.form_prepare, method: onFormPrepare }
```

The following example shows how to add validation groups to form:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormPrepareEvent;

class ProductDeleteListener
{
    public function onFormPrepare(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'validation_groups' => ['Default', 'delete'],
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
    delete:
        form_init_event_name: 'product_admin.delete.form_init'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.form_init, method: onFormInit }
```

The following example shows how to add a custom field to form:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormEvent;

class ProductDeleteListener
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

Event dispatched on form submitted and valid, but before performing deletion.

It allows to setResponse and stop the process or redirect, also modify model before saving it, for example.

**Example**

```yaml
$configs:
    delete:
        form_valid_event_name: 'product_admin.delete.form_valid'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.form_valid, method: onFormValid }
```

The following example shows how to modify a product field or redirect to other route if some condition is met:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormValidEventApplyEvent;

class ProductDeleteListener
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

Event dispatched when form is valid and allows to perform entity changes before saving, or change the deletion process to a custom one.

It allows to setApplied and skip the default saving process.

**Example**

```yaml
$configs:
    delete:
        apply_event_name: 'product_admin.delete.apply'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.apply, method: onApply }
```

The following example shows how to delete the entity to an API instead of database:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\ApplyEvent;

class ProductDeleteListener
{
    public function onApply(ApplyEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $form = $event->getForm();
        
        $this->apiClient->deleteProduct($product);
        
        $event->setApplied(true); // skip default saving process
    }
}
```

### success_event_name

**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\GetResponseEntityEvent](src/Event/GetResponseEntityEvent.php)

Event dispatched on form submitted and valid, and after saving the entity.

It allows to setResponse and stop the process or redirect, also fire other actions after saving, for example.

**Example**

```yaml
$configs:
    delete:
        success_event_name: 'product_admin.delete.success'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.success, method: onSuccess }
```

The following example shows how to redirect to other route if some condition is met, or dispatch another event:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;

class ProductDeleteListener
{
    public function onSuccess(GetResponseEntityEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other/'.$product->getId())));
        } else {
            $this->dispatcher->dispatch(new ProductDeletedEvent($product));
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
    delete:
        failure_event_name: 'product_admin.delete.failure'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.failure, method: onFailure }
```

The following example shows how to redirect to other route if some condition is met, or log the failure:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FailureEvent;

class ProductDeleteListener
{
    public function onFailure(FailureEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $exception = $event->getException();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->logger->error('Error deleting product', [
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
    delete:
        form_invalid_event_name: 'product_admin.delete.form_invalid'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.form_invalid, method: onFormInvalid }
```

The following example shows how to redirect to other route if some condition is met, or process form errors:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\FormInvalidEventFormValidEventFormInvalidEvent;

class ProductDeleteListener
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
    delete:
        view_event_name: 'product_admin.delete.view'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.view, method: onView }
```

The following example shows how to add data to view:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\ViewEvent;

class ProductDeleteListener
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
{# templates/admin/products/delete.html.twig #}

{{ some_data }}
```

### exception_event_name


**type**: string **default**: null **event**: [Softspring\Component\CrudlController\Event\ExceptionEvent](src/Event/ExceptionEvent.php)

Event dispatched if an exception is thrown during any of the process steps.

**Example**

```yaml
$configs:
    delete:
        failure_event_name: 'product_admin.delete.exception'
```

Once the event name is configured, you can configure your listener for this event:

```yaml
# config/services.yaml
services:
    App\EventListener\ProductDeleteListener:
        tags:
            - { name: kernel.event_listener, event: product_admin.delete.exception, method: onException }
```

The following example shows how to redirect to other route if some condition is met, or log the exception:

```php
<?php

namespace App\EventListener;

use Softspring\Component\CrudlController\Event\ExceptionEvent;

class ProductDeleteListener
{
    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        $exception = $event->getException();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        } else {
            $this->logger->error('Error deleting product', [
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
    delete:
        entity_attribute: 'product'
        is_granted: 'ROLE_ADMIN_PRODUCT_DELETE'
        form: 'App\Form\ProductDeleteForm'
        view: 'admin/products/delete.html.twig'
        success_redirect_to: 'app_admin_product_list'
        
        # events
        initialize_event_name: 'product_admin.delete.initialize'
        load_entity_event_name: 'product_admin.delete.load_entity'
        form_prepare_event_name: 'product_admin.delete.form_prepare'
        form_init_event_name: 'product_admin.delete.form_init'
        form_valid_event_name: 'product_admin.delete.form_valid'
        apply_event_name: 'product_admin.delete.apply'
        success_event_name: 'product_admin.delete.success'
        failure_event_name: 'product_admin.delete.failure'
        form_invalid_event_name: 'product_admin.delete.form_invalid'
        view_event_name: 'product_admin.delete.view'
        exception_event_name: 'product_admin.delete.exception'
```

And this is a complete example of event listening:

```php
<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductDeleteListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'product_admin.delete.initialize' => 'onInitialize',
            'product_admin.delete.load_entity' => 'onLoadEntity',
            'product_admin.delete.found' => 'onFound',
            'product_admin.delete.not_found' => 'onNotFound',
            'product_admin.delete.form_prepare' => 'onFormPrepare',
            'product_admin.delete.form_init' => 'onFormInit',
            'product_admin.delete.form_valid' => 'onFormValid',
            'product_admin.delete.apply' => 'onApply',
            'product_admin.delete.success' => 'onSuccess',
            'product_admin.delete.failure' => 'onFailure',
            'product_admin.delete.form_invalid' => 'onFormInvalid',
            'product_admin.delete.view' => 'onView',
            'product_admin.delete.exception' => 'onException',
        ];
    }
    
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
    }
    
    public function onLoadEntity(LoadEntityEvent $event): void
    {
        $event->setEntity($this->manager->getRepository()->findOneByCustomField($event->getRequest()->get('custom_field')));
    }
    
    public function onFound(EntityFoundEvent $event): void
    {
        $this->logger->debug(sprintf('Entity found: %s', $event->getEntity()->getId()));
    }
    
    public function onNotFound(NotFoundEvent $event): void
    {
        $this->flashBag->add('error', 'Product not found');
        $event->setResponse(new RedirectResponse('/other')));
    }
    
    public function onFormPrepare(FormPrepareEvent $event): void
    {
        $event->setFormOptions([
            'validation_groups' => ['Default', 'delete'],
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
        
        $this->apiClient->deleteProduct($product);
        
        $event->setApplied(true); // skip default saving process
    }
    
    public function onSuccess(GetResponseEntityEvent $event): void
    {
        $request = $event->getRequest();
        $product = $event->getEntity();
        
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other/'.$product->getId())));
        } else {
            $this->dispatcher->dispatch(new ProductDeletedEvent($product));
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
            $this->logger->error('Error deleting product', [
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
            $this->logger->error('Error deleting product', [
                'exception' => $exception,
                'product' => $product,
            ]);
        }
    }
} 
```