# Configure your controller

You need to configure your controller as a service.

The controller requires 6 arguments:

- The manager implementing Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface
- listFilterForm or null
- createForm or null
- updateForm or null
- deleteForm or null
- config: an array with controller configuration

```yaml
services:
    product.controller:
        class: Softspring\Component\CrudlController\Controller\CrudlController
        public: true
        tags: [ 'controller.service_arguments' ]
        arguments:
            $manager: '@App\Manager\ProductManagerInterface'
            $config:
                ...
```

## General configuration

```yaml
$config:
    entity_attribute: 'product'
```

This is used for route attribute and view data passing.

If no entity_attribute is set, 'entity' name will be used.

## Create action configuration

This is the list action configuration reference:

```yaml
$config:
    create:
        is_granted: 'ROLE_ADMIN_PRODUCT_CREATE'
        success_redirect_to: 'app_admin_product_list'
        view: 'admin/products/create.html.twig'
        initialize_event_name: 'product_admin.create.initialize'
        form_init_event_name: 'product_admin.create.form_init'
        form_valid_event_name: 'product_admin.create.form_valid'
        success_event_name: 'product_admin.create.success'
        form_invalid_event_name: 'product_admin.create.form_invalid'
        view_event_name: 'product_admin.create.view'
```

Main fields:

- **is_granted**: (optional) role name to check at the begining
- **view**: (required) the view path for rendering list
- **success_redirect_to**: (optional) route name to redirect o success

Events configuration:

- **initialize_event_name**: (optional) event dispatched after checking is_granded and before form processing.
  Dispatches Softspring\Component\Events\GetResponseRequestEvent object
  It allows, for example, to redirect on custom situation.
- **form_init_event_name**: (optional) event dispatched after form creation but before process it
  Dispatches Softspring\Component\CrudlController\Event\GetResponseEntityEvent object
  It allows to modify form.
- **form_valid_event_name**: (optional) dispatched on form submitted and valid
  Dispatches Softspring\Component\CrudlController\Event\GetResponseFormEvent object
  It allows to modify model before saving it.
- **success_event_name**: (optional)
  Dispatches Softspring\Component\CrudlController\Event\GetResponseEntityEvent object
  It allows to make changes after changes are applied, or redirect.
- **form_invalid_event_name**: (optional) dispatched on form submitted and invalid
  Dispatches Softspring\Component\CrudlController\Event\GetResponseFormEvent object
  It allows to process form errors.
- **view_event_name**: (optional)
  Dispatches Softspring\Component\Events\ViewEvent object
  Allows data adding for the view.

## Read action configuration

This is the list action configuration reference:

```yaml
$config:
    read:
        is_granted: 'ROLE_ADMIN_PRODUCT_READ'
        param_converter_key: 'id'
        view: 'admin/products/read.html.twig'
        initialize_event_name: 'product_admin.read.initialize'
        view_event_name: 'product_admin.read.view'
```

Main fields:

- **view**: (required) the view path for rendering list
- **param_converter_key**: (optional) field used for quering, default value is 'id'

Events configuration:

- **initialize_event_name**: (optional) event dispatched after checking is_granded and before form processing.
  Dispatches Softspring\Component\Events\GetResponseRequestEvent object
  It allows, for example, to redirect on custom situation.
- **view_event_name**: (optional)
  Dispatches Softspring\Component\Events\ViewEvent object
  Allows data adding for the view.

## Update action configuration

This is the list action configuration reference:

```yaml
$config:
    update:
        is_granted: 'ROLE_ADMIN_PRODUCT_UPDATE'
        success_redirect_to: 'app_admin_product_list'
        view: 'admin/products/update.html.twig'
        initialize_event_name: 'product_admin.update.initialize'
        form_init_event_name: 'product_admin.update.form_init'
        form_valid_event_name: 'product_admin.update.form_valid'
        success_event_name: 'product_admin.update.success'
        form_invalid_event_name: 'product_admin.update.form_invalid'
        view_event_name: 'product_admin.update.view'
```

Main fields:

- **is_granted**: (optional) role name to check at the begining
- **view**: (required) the view path for rendering list
- **success_redirect_to**: (optional) route name to redirect o success

Events configuration:

- **initialize_event_name**: (optional) event dispatched after checking is_granded and before form processing.
  Dispatches Softspring\Component\Events\GetResponseRequestEvent object
  It allows, for example, to redirect on custom situation.
- **form_init_event_name**: (optional) event dispatched after form creation but before process it
  Dispatches Softspring\Component\CrudlController\Event\GetResponseEntityEvent object
  It allows to modify form.
- **form_valid_event_name**: (optional) dispatched on form submitted and valid
  Dispatches Softspring\Component\CrudlController\Event\GetResponseFormEvent object
  It allows to modify model before saving it.
- **success_event_name**: (optional)
  Dispatches Softspring\Component\CrudlController\Event\GetResponseEntityEvent object
  It allows to make changes after changes are applied, or redirect.
- **form_invalid_event_name**: (optional) dispatched on form submitted and invalid
  Dispatches Softspring\Component\CrudlController\Event\GetResponseFormEvent object
  It allows to process form errors.
- **view_event_name**: (optional)
  Dispatches Softspring\Component\Events\ViewEvent object
  Allows data adding for the view.

## Delete action configuration

This is the list action configuration reference:

```yaml
$config:
    delete:
        is_granted: 'ROLE_ADMIN_PRODUCT_DELETE'
        success_redirect_to: 'app_admin_product_list'
        view: 'admin/products/delete.html.twig'
        initialize_event_name: 'product_admin.delete.initialize'
        form_init_event_name: 'product_admin.delete.form_init'
        form_valid_event_name: 'product_admin.delete.form_valid'
        success_event_name: 'product_admin.delete.success'
        form_invalid_event_name: 'product_admin.delete.form_invalid'
        delete_exception_event_name: 'product_admin.delete.exception'
        view_event_name: 'product_admin.delete.view'
```

Main fields:

- **is_granted**: (optional) role name to check at the begining
- **view**: (required) the view path for rendering list
- **success_redirect_to**: (optional) route name to redirect o success

Events configuration:

- **initialize_event_name**: (optional) event dispatched after checking is_granded and before form processing.
  Dispatches Softspring\Component\Events\GetResponseRequestEvent object
  It allows, for example, to redirect on custom situation.
- **form_init_event_name**: (optional) event dispatched after form creation but before process it
  Dispatches Softspring\Component\CrudlController\Event\GetResponseEntityEvent object
  It allows to modify form.
- **form_valid_event_name**: (optional) dispatched on form submitted and valid
  Dispatches Softspring\Component\CrudlController\Event\GetResponseFormEvent object
  It allows to modify model before saving it.
- **success_event_name**: (optional)
  Dispatches Softspring\Component\CrudlController\Event\GetResponseEntityEvent object
  It allows to make changes after changes are applied, or redirect.
- **form_invalid_event_name**: (optional) dispatched on form submitted and invalid
  Dispatches Softspring\Component\CrudlController\Event\GetResponseFormEvent object
  It allows to process form errors.
- **delete_exception_event_name**: (optional) dispatched on entity deletion when it throws an exception.
  Dispatches Softspring\Component\CrudlController\Event\GetResponseExceptionFormEvent object
  It allows to process exception, show errors or redirect.
- **view_event_name**: (optional)
  Dispatches Softspring\Component\Events\ViewEvent object
  Allows data adding for the view.

## List action configuration

This is the list action configuration reference:

```yaml
$config:
    list:
        is_granted: 'ROLE_ADMIN_PRODUCT_LIST'
        read_route: 'app_admin_product_details'
        view: 'admin/products/list.html.twig'
        view_page: 'admin/products/list-page.html.twig'
        initialize_event_name: 'product_admin.list.initialize'
        filter_event_name: 'product_admin.list.filter'
        view_event_name: !php/const App\Events::ADMIN_PRODUCT_LIST_VIEW
        default_order_sort: <default_order_sort>
```

Main fields:

- **is_granted**: (optional) role name to check at the begining
- **read_route**: (optional) route name to read action, used to pass it to view
- **view**: (required) the view path for rendering list
- **view_page**: (optional) the view path for ajax requests to return only page results

Events configuration:

- **initialize_event_name**: (optional) event dispatched after checking is_granded and before form processing.
  Dispatches Softspring\Component\Events\GetResponseRequestEvent object
  It allows, for example, to redirect on custom situation.
- **filter_event_name**: (optional) event dispatched after form processing and before quering.
  Dispatches Softspring\Component\CrudlController\Event\FilterEvent object
  With this event you are able to modify quering criteria or other data before quering.
- **view_event_name**: (optional)
  Dispatches Softspring\Component\Events\ViewEvent object
  Allows data adding for the view.

Other fields:

- **default_order_sort**: (optional) is used in case no list filter form configured.

## Routing configuration

Now you need to configure the routes.

Remembrer, none of them is mandatory, you can configure just the ones you need.

```yaml
# config/routes/admin_product.yaml
app_admin_product_list:
    controller: product.controller::list
    path: /

app_admin_product_create:
    controller: product.controller::create
    path: /create

app_admin_product_update:
    controller: product.controller::update
    path: /{product}/update

app_admin_product_delete:
    controller: product.controller::delete
    path: /{product}/delete

app_admin_product_read:
    controller: product.controller::read
    path: /{product}
```

```yaml
# config/routes.yaml
app_admin_product_routes:
    resource: 'routes/admin_product.yaml'
    prefix: "/admin/product"
```
