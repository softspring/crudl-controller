# Configure your controller

You need to configure your controller as a pure Symfony service.

The controller requires 6 arguments:

- The manager implementing Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface
- Symfony event dispatcher
- Symfony twig environment
- Symfony form factory
- Symfony authorization Checker
- Symfony router
- configs: an optional array with controller configuration (also can be configured each method separately)

You can set autowire to true to avoid configuring Symfony components manually.

```yaml
services:
    _defaults:
        autowire: true
        
    product.controller:
        class: Softspring\Component\CrudlController\Controller\CrudlController
        public: true
        arguments:
            $manager: '@App\Manager\ProductManagerInterface'
            $configs:
                ...
```

## Configure actions

- [Create action configuration](docs/4_1_create_action.md)
- [Read action configuration](docs/4_2_read_action.md)
- [Update action configuration](docs/4_3_update_action.md)
- [Delete action configuration](docs/4_4_delete_action.md)
- List action configuration

## Routing configuration

Now you need to configure the routes.

Remember, none of them is mandatory, you can configure just the ones you need.

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
