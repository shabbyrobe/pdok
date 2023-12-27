**⚠️ NOTE: This has not been maintained in a _very_ long time. It should thus be considered "_very deprecated_".
Please do not use it, and update any usage you have of it to use something else.**

**⚠️ ALSO NOTE: This repo will relocate at some point in 2024.**

---


PDOK: Improved PDO Interface
============================

Ever found yourself thinking PDO would be a lot nicer if the API was tweaked just a little
bit?

If so, PDOK may be for you. PDOK provides classes that should be almost completely
compatible with PDO and PDOStatement, but with some enhancements:

- Fluent interface
- Connect on demand, not on instantiation
- Disconnect and reconnect
- Serialize, unserialize, clone support
- Consistent method names (``PDO->execute()``, ``PDOStatement->exec()``)
- Shorthand fetch methods: ``fetchAssoc``, ``fetchNum``
- Iteration support - every ``fetch`` method has a corresponding ``each`` method which
  returns an iterator.

If you already use ``PDO::ERRMODE_EXCEPTION``, this should work as a drop-in replacement.
Please familiarise yourself with the :ref:`caveats` and :ref:`limitations` sections if you
are migrating an existing project.

Requires PHP 5.4 or greater, works with HHVM.


Improvements
------------

Fluent interface for PDOStatement (even when using persistent connections):

.. code-block:: php
    
    <?php
    $pdo = new \PDOK\Connector('sqlite::memory:');
    $pdo->prepare('SELECT * FROM mytable WHERE mycol=?')->execute(['yep'])->fetchAll();


Fluent interface for PDO:

.. code-block:: php
    
    <?php
    $pdo->beginTransaction()
        ->execute('INSERT INTO foo VALUES(1, "yep");')
        ->execute('INSERT INTO foo VALUES(2, "yay");')
        ->commit();


``PDO::ERRMODE_EXCEPTION`` is always used:

.. code-block:: php
    
    <?php
    $pdo = new \PDOK\Connector('sqlite::memory:');
    try {
        $pdo->query("SOLOCT blergh FRAM gorgle");
    }
    catch (\PDOException $e) {
        echo "KABOOM!";
    }


Connect on demand:

.. code-block:: php
    
    <?php
    $pdo = new \PDOK\Connector('...')
    assert($pdo->isConnected() == false);
   
    $pdo->query("SELECT * FROM mytable");
    assert($pdo->isConnected() == true);


Or you can force the connection yourself:

.. code-block:: php

    <?php
    $pdo = new \PDOK\Connector('...');
    $pdo->connect();
    assert($pdo->isConnected() == true);


Disconnect, reconnect, clone and serialize:

.. code-block:: php

    <?php
    $pdo->disconnect();
    assert($pdo->isConnected() == false);
   
    $pdo->connect();
    $cloned = clone $pdo;
    assert($pdo->isConnected() == true && $cloned->isConnected() == false);
   
    $unserialized = unserialize(serialize($pdo));
    assert($unserialized->isConnected() == false);


Array-based static constructor:

.. code-block:: php
    
    <?php
    $ini = <<<INI
    dsn = "mysql:host=localhost"
    user = "myuser"
    pass = "mypass"
    db = "hello"
    options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true
    statements[] = "SET NAMES \"utf8\""
    INI;
   
    $settings = parse_ini_string($ini);
    $pdo = \PDOK\Connector::create($settings);


Method naming consistency (choose your poison, but stick to it):

.. code-block:: php

    <?php
    $pdo->exec('SELECT * FROM mytable');
    $pdo->execute('SELECT * FROM mytable');
   
    $stmt->exec();
    $stmt->execute();


Query count:

.. code-block:: php

    <?php
    $pdo->execute('UPDATE mytable1 SET foo=1');
    $pdo->execute('UPDATE mytable2 SET bar=1');
    $pdo->prepare("UPDATE mytable3 SET baz=1")->execute();
    assert($pdo->queries == 3);


Shorthand fetch methods:

.. code-block:: php

    <?php
    $stmt = $connector->query("SELECT * FROM mytable");
    
    // equivalent
    $stmt->fetchAssoc();
    $stmt->fetch(\PDO::FETCH_ASSOC);
   
    // equivalent
    $stmt->fetchNum();
    $stmt->fetch(\PDO::FETCH_NUM);


Every ``fetch`` method has a corresponding ``each`` method:

.. code-block:: php

    <?php
    foreach ($stmt->eachAssoc() as $row) {
        // stuff
    }
    foreach ($stmt->eachNum() as $row) {
        // stuff
    }
    foreach ($stmt->each(\PDO::FETCH_ASSOC) as $row) {
        // stuff
    }


Interfaces! If you want to make your own statement class, implement
``PDOK\StatementInterface`` and use ``PDOK\StatementTrait``:

.. code-block:: php
 
    <?php
    class MyStatement implements \PDOK\StatementInterface
    {
        use \PDOK\StatementTrait;
   
        /* ... */
    }


.. _limitations:

Limitations
-----------

- You can only use ``PDO::ERRMODE_EXCEPTION`` for ``PDO::ATTR_ERRMODE``.

- Many methods return boolean on failure instead of being fluent. This is a decision that
  hasn't been made yet - I'm leaning towards them raising exceptions instead of returning
  false as error messages like "Tried to call function execute() on a non object" is not
  exactly developer friendly.


.. _caveats:

Caveats
-------

- PDOK should be backward compatible with vanilla PDO provided you do not use type hints.
  You can replace your existing PDO type hints with a call to
  ``PDOK\Functions::ensurePDO($pdo)``, and your existing ``PDOStatement`` hints with 
  ``PDOK\Functions::ensureStatement($stmt)``.

- PDOK does not connect on demand. If your code requires that a connection be established
  on instantiation, you will need to modify it to call ``PDOK\Connector->connect()``
  directly afterwards.

