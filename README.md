## TimeTree extension for PHP NeoClient

This library extends [Neo4j PHP NeoClient](https://github.com/neoxygen/neo4j-neoclient) by adding convenient methods
for working with the [GraphAware TimeTree Plugin](https://github.com/graphaware/neo4j-timetree).

The library mixes at some point the high valuable features of the TimeTree plugin with Cypher to keep consistency regarding
response result used by NeoClient that permits to use node objects.

## Installation and setup

You need to require the library in your dependencies and register the extension in NeoClient :


composer.json

```json
"require": {
    "neoxygen/neoclient": "~2.1",
    "graphaware/neoclient-timetree": "~1.0"
    }
```

Register the extension when building the client :

```php

use Neoxygen\NeoClient\ClientBuilder;

$client = ClientBuilder::create()
    ->addDefaultLocalConnection()
    ->setAutoFormatResponse(true)
    ->registerExtension('graphaware_timetree', 'GraphAware\NeoClientExtension\TimeTree\TimeTreeExtension')
    ->build();
```

Note that the extension name `graphaware-timetree` can be changed to whatever you want.

### Adding schema indexes for the TimeTree time nodes

With this simple method, it will add the schema indexes on the Year, Month, Day, Hour, Minute, Second and Millisecond 
nodes.

```php
$client->createTimeTreeIndexes();
```

Generally this should be runned only once when building your application.


## Usage

For all the following methods, the `timestamp` is optional and the "NOW" timestamp will be used when omitted.

### Getting a time instant node

By simply passing a timestamp to the following method, you'll get the time instant node id and all the time tree
created magically :

```php
$today = $client->getTimeNode(time());
```

The default resolution of the TimeTree plugin is `day`, you can increase or decrease the resolution by using the 
timetree constants provided by the extension :

```php
use GraphAware\NeoClientExtension\TimeTree\TimeTreeExtension;

$now = $client->getTimeNode(time(), TimeTreeExtension::TIMETREE_RESOLUTION_MILLISECOND);
```

### Adding a node to a time instant

You need to pass the node id of the event along with the time and the desired relationship type. Note that 
the relationship is directed from the time instant node to the event node.

```php

// Example for adding a node to a timeTree after its creation :

$q = 'CREATE (n:SuperEvent) RETURN n';
$result = $client->sendCypherQuery($q)->getResult();
$nodeId = $result->get('n')->getId();

$client->addNodeToTime($nodeId, time(), 'EVENT_OCCUR_ON');
// Returns true
```

### Retrieving events nodes for a time :

This mixes the timetree functionality with Cypher in order to retrieve all the nodes informations in your objects, like
labels, etc...

```php
$result = $client->getTimeEvents(time())->getResult();

// Returns you a collection of nodes, the identifier of the collection is "n"
$events = $result->get('n');

// OR

$events = $result->getNodes();
```

### Retrieving event nodes that occur between a time range

You should here specify the start time and the end time is default to `NOW`.

```php
$result = $client->getTimeEventsInRange(112556325)->getResult();
// Again the identifier is n

$events = $result->get('n');
```

You can also specify the resolution, for e.g. to hours :

```php
$result = $client->getTimeEventsInRange(
    112556325, 
    time(), 
    TimeTreeExtension::TIMETREE_RESOLUTION_HOUR
    )->getResult();
// Again the identifier is n

$events = $result->get('n');
```

----

### To-Do :

- [ ] Support for more TT features
- [ ] Support for multiple tree roots
- [ ] More Cypher/TT integration for e.g. retrieving nodes by label attached to times
- [ ] User defined method for retrieving events (Cypher or TT)

### Tests :

Run the test suite with PHPUnit :

```bash
./vendor/bin/phpunit
```

## License

Copyright (c) 2014 GraphAware

GraphAware is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, 
either version 3 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. 
You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.



