# Create action

Create action is used to create a new entity and provide a form to fill it, and save it.

## Configurations

### form

**type**: string **required**

The form class name

### view

**type**: string **required**

The view path for rendering list

### entity_attribute

**type**: string **default**: 'entity'

The name of entity field passed to the view, and used for routes

### is_granted

**type**: string **default**: null

Role name to check at the begining

### success_redirect_to

**type**: string **default**: null

Route name to redirect o success

## Events configuration

All events are optional, you can configure just the ones you need.

### initialize_event_name

**type**: string **default**: null **event**: Softspring\Component\Events\GetResponseRequestEvent

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

The folloging example shows how to redirect to other route if some condition is met:

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

### form_prepare_event_name

**type**: string **default**: null **event**: Softspring\Component\CrudlController\Event\FormPrepareEvent

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

**type**: string **default**: null **event**: Softspring\Component\Events\FormEvent

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

use Softspring\Component\Events\FormEvent;

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

**type**: string **default**: null **event**: Softspring\Component\CrudlController\Event\GetResponseFormEvent

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

use Softspring\Component\CrudlController\Event\GetResponseFormEvent;

class ProductCreateListener
{
    public function onFormValid(GetResponseFormEvent $event): void
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

### success_event_name

**type**: string **default**: null **event**: Softspring\Component\CrudlController\Event\GetResponseEntityEvent

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

### exception_event_name

**type**: string **default**: null **event**: Softspring\Component\CrudlController\Event\GetResponseEntityExceptionEvent

Event dispatched if an exception is thrown during entity saving or success event.

**Example**

```yaml
$configs:
    create:
        exception_event_name: 'product_admin.create.exception'
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

use Softspring\Component\CrudlController\Event\GetResponseEntityExceptionEvent;

class ProductCreateListener
{
    public function onException(GetResponseEntityExceptionEvent $event): void
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

**type**: string **default**: null **event**: Softspring\Component\CrudlController\Event\GetResponseFormEvent

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

use Softspring\Component\CrudlController\Event\GetResponseFormEvent;

class ProductCreateListener
{
    public function onFormInvalid(GetResponseFormEvent $event): void
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

**type**: string **default**: null **event**: Softspring\Component\Events\ViewEvent

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

use Softspring\Component\Events\ViewEvent;

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


## Configuration reference

This is the list action configuration reference:

```yaml
$configs:
    create:
        # required fields
        param_converter_key: 'id'
        form: 'App\Form\ProductCreateForm'
        view: 'admin/products/create.html.twig'
        
        # optional fields
        entity_attribute: 'product'
        is_granted: 'ROLE_ADMIN_PRODUCT_CREATE'
        success_event_name: 'product_admin.create.success'
        
        # events
        initialize_event_name: 'product_admin.create.initialize'
        form_prepare_event_name: 'product_admin.create.form_prepare'
        form_init_event_name: 'product_admin.create.form_init'
        form_valid_event_name: 'product_admin.create.form_valid'
        success_redirect_to: 'app_admin_product_list'
        exception_event_name: 'product_admin.create.exception'
        form_invalid_event_name: 'product_admin.create.form_invalid'
        view_event_name: 'product_admin.create.view'
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
            'product_admin.create.form_prepare' => 'onFormPrepare',
            'product_admin.create.form_init' => 'onFormInit',
            'product_admin.create.form_valid' => 'onFormValid',
            'product_admin.create.success' => 'onSuccess',
            'product_admin.create.exception' => 'onException',
            'product_admin.create.form_invalid' => 'onFormInvalid',
            'product_admin.create.view' => 'onView',
        ];
    }
    
    public function onInitialize(GetResponseRequestEvent $event): void
    {
        if (...any check you need...) {
            $event->setResponse(new RedirectResponse('/other')));
        }
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
    
    public function onFormValid(GetResponseFormEvent $event): void
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
    
    public function onException(GetResponseEntityExceptionEvent $event): void
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
    
    public function onFormInvalid(GetResponseFormEvent $event): void
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
} 
```