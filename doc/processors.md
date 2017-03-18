# Processors

Processors allow you to process objects before and/or after they are persisted. Processors
must implement the [`Nelmio\Alice\ProcessorInterface`](../src/Nelmio/Alice/ProcessorInterface.php).

Here is an example where we may use this feature to make sure passwords are properly
hashed on a `User`:

```php
namespace MyApp\DataFixtures\Processor;

use Nelmio\Alice\ProcessorInterface;
use MyApp\Hasher\PasswordHashInterface;
use User;

final class UserProcessor implements ProcessorInterface
{
    /**
     * @var PasswordHashInterface
     */
    private $passwordHasher;

    /**
     * @param PasswordHashInterface $passwordHasher
     */
    public function __construct(PasswordHashInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {
        if (false === $object instanceof User) {
            return;
        }

        $object->password = $this->passwordHasher->hash($object->password);
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
        // do nothing
    }
}
```
You can add a list of processors in the load method, e.g.

```php
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager, $options, $processors);
```

Or, you can add them to your loader using the `::addProcessor()` method, e.g.

```php
$loader = new \Nelmio\Alice\Fixtures($objectManager, $options);
$loader->addProcessor($processor);
$objects = $loader->loadFiles(__DIR__.'/fixtures.yml');
```


<br />
<hr />

« [Table of Contents](../README.md#table-of-contents) • [Customize Data Generation](customizing-data-generation.md) »
