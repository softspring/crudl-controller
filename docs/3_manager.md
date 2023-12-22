# Manager

Manager interface defines 

- getTargetClass()
- getEntityClass()
- getEntityClassReflection()
- getRepository()
- createEntity()
- saveEntity()
- deleteEntity()
- getEntityManager()

# Create your Manager

It's recommended to create an interface, especially if you are creating a bundle and you
want to allow extending it.

```php
namespace App\Manager;

use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

interface ProductManagerInterface extends CrudlEntityManagerInterface
{

}
```

Create the manager:

```php
namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;
use App\Entity\Product;

class ProductManager implements ProductManagerInterface
{
    use CrudlEntityManagerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return Product::class;
    }
}
```

You can also extend the Softspring\Component\CrudlController\Manager\DefaultCrudlEntityManager

```php
namespace App\Manager;

use Softspring\Component\CrudlController\Manager\DefaultCrudlEntityManager;

class ProductManager extends DefaultCrudlEntityManager implements ProductManagerInterface
{

}
```

and configure the service:

```yaml
services:
    App\Manager\ProductManagerInterface:
      class: App\Manager\ProductManager
      arguments:
        $targetClass: 'App\Entity\Product'
```

## Using Doctrine target entities

If you are creating a model provider bundle, probably you will want to extend your model.

CRUDL supports it in its managers.

```yaml
doctrine:
    orm:
        resolve_target_entities:
            My\Bundle\Model\ExampleInterface: App\Entity\Example
```

This will be the service provided by your bundle:

```yaml
services:
    My\Bundle\Manager\ExampleManagerInterface:
      class: My\Bundle\Manager\ExampleManager
      arguments:
        $targetClass: 'My\Bundle\Model\ExampleInterface'
```

The manager will return the following values:

- getTargetClass() => My\Bundle\Model\ExampleInterface
- getEntityClass() => App\Entity\Example
