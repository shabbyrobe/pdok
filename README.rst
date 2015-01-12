PDOK: Improved PDO Interface
============================

Improvements
------------

Fluent interface for PDOStatement (even when using persistent connections):

.. code-block:: php
    
    <?php
    $pdo = new \PDOK\Connector('sqlite::memory:');
    $pdo->prepare('SELECT * FROM mytable WHERE mycol=?')->execute(['yep'])->fetchAll();


``PDO::ERRMODE_EXCEPTION`` is always used:

.. code-block:: php
    
    <?php
    $pdo = new \PDOK\Connector('sqlite::memory:');
    $pdo->query("SOLOCT blergh FRAM gorgle");   // KABOOM!


Connect on demand:

.. code-block:: php
    
    <?php
    $pdo = new \PDOK\Connector('...')
    assert($pdo->isConnected() == false);

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
    $ini = '
    dsn = "mysql:host=localhost"
    user = "myuser"
    pass = "mypass"
    db = "hello"
    options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true
    statements[] = "SET NAMES \"utf8\""
    ';

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
    assert($pdo->queries == 2);


Limitations
-----------

- You can only use ``PDO::ERRMODE_EXCEPTION`` for ``PDO::ATTR_ERRMODE``.
- Type hints of ``PDO`` and ``PDOStatement`` are no longer useful.


Caveats
-------

- PDOK should be backward compatible with vanilla PDO provided you do not use type hints.
  You can replace your existing PDO type hints with a call to
  ``PDOK\Functions::ensurePDO($pdo)``.

- ``PDOK\Connector->prepare()`` and ``PDOK\Connector->query()`` may return an instance of
  ``PDOK\Statement`` or ``PDOK\StatementWrapper``. These do not share a common subtype -
  this can be worked around by ``PDOK\Functions::ensureStatement($stmt)``.

