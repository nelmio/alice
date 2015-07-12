# Processors

Processors allow you to process objects before and/or after they are persisted. Processors
must implement the `ProcessorInterface`.

Here is an example where we may use this feature to make sure passwords are properly
hashed on a `User`:

```php
namespace Acme\DemoBundle\DataFixtures\ORM;

use Nelmio\Alice\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use User;

class UserProcessor implements ProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
        if (!($object instanceof User)) {
            return;
        }

        $hasher = $this->container->get('example_password_hasher');
        $object->password = $hasher->hash($object->password);
    }
}
```
You can add a list of processors in the load method, e.g.

```php
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager, $options, $processors);
```

Or, you can add them to your loader using the `addProcessor()` method, e.g.

```php
$loader = new \Nelmio\Alice\Fixtures($objectManager, $options);
$loader->addProcessor($processor);
$objects = $loader->loadFiles(__DIR__.'/fixtures.yml');
```

Previous chapter: [Customize Data Generation](customizing-data-generation.md)<br />
Got back to [Table of Contents](../README.md#table-of-contents)
