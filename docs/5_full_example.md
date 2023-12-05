# Full Example

```php
# src/Entity/Product.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Product
{
    ...
}
```

```php
# src/Form/Admin/ProductCreateForm.php
namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;

class ProductCreateForm extends AbstractType
{
    ...
}
```

```php
# src/Form/Admin/ProductCreateForm.php
namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;

class ProductUpdateForm extends AbstractType
{
    ...
}
```

```php
# src/Form/Admin/ProductCreateForm.php
namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;

class ProductDeleteForm extends AbstractType
{
    ...
}
```

```php
# src/Form/Admin/ProductCreateForm.php
namespace App\Form\Admin;

use Softspring\Component\CrudlController\Form\EntityListFilterForm;

class ProductListFilterForm extends EntityListFilterForm
{
    ...
}
```

```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    product_manager:
        class: Softspring\Component\CrudlController\Manager\DefaultCrudlEntityManager
        arguments:
            $targetClass: 'App\Entity\Product'

    product.controller:
        class: Softspring\Component\CrudlController\Controller\CrudlController
        public: true
        tags: [ 'controller.service_arguments' ]
        arguments:
            $manager: '@product_manager'
            $config:
                entity_attribute: 'product'
                create:
                    form: '@App\Form\Admin\ProductCreateForm'
                    view: 'admin/products/create.html.twig'
                read:
                    view: 'admin/products/read.html.twig'
                update:
                    form: '@App\Form\Admin\ProductUpdateForm'
                    view: 'admin/products/update.html.twig'
                delete:
                    form: '@App\Form\Admin\ProductDeleteForm'
                    view: 'admin/products/delete.html.twig'
                list:
                    filterForm: '@App\Form\Admin\ProductListFilterForm'
                    view: 'admin/products/list.html.twig'
```

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

