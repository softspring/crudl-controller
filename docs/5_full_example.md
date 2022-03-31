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

use Softspring\Component\CrudlController\Form\EntityCreateFormInterface;
use Symfony\Component\Form\AbstractType;

class ProductCreateForm extends AbstractType implements EntityCreateFormInterface
{
    ...
}
```

```php
# src/Form/Admin/ProductCreateForm.php
namespace App\Form\Admin;

use Softspring\Component\CrudlController\Form\EntityUpdateFormInterface;
use Symfony\Component\Form\AbstractType;

class ProductUpdateForm extends AbstractType implements EntityUpdateFormInterface
{
    ...
}
```

```php
# src/Form/Admin/ProductCreateForm.php
namespace App\Form\Admin;

use Softspring\Component\CrudlController\Form\EntityDeleteFormInterface;
use Symfony\Component\Form\AbstractType;

class ProductDeleteForm extends AbstractType implements EntityDeleteFormInterface
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
    calls:
      - { method: setContainer, arguments: ['@service_container'] }
    arguments:
      $manager: '@product_manager'
      $createForm: '@App\Form\Admin\ProductCreateForm'
      $updateForm: '@App\Form\Admin\ProductUpdateForm'
      $deleteForm: '@App\Form\Admin\ProductDeleteForm'
      $listFilterForm: '@App\Form\Admin\ProductListFilterForm'
      $config:
        entity_attribute: 'product'
        create:
          view: 'admin/products/create.html.twig'
        read:
          view: 'admin/products/read.html.twig'
        update:
          view: 'admin/products/update.html.twig'
        delete:
          view: 'admin/products/delete.html.twig'
        list:
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

