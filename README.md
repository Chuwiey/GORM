GORM (Graph Object Relational Model) - WIP
====================================
If you are familiar with ORM's and MVC framworks, then you know that a lot of developers prefer to treat db entities and their relations as objects.
Thereby, we are able to produce queries like: 
$user->findOne('username'); or get data like so: $user->username;

A graph database makes this a little bit harder, because for starters, everything is related:
A graph is a set of connected edges and nodes. 

The second problem is to be able to utilize the inherent relationships in graphs.
So, I basically did the same thing you are able to do with an ORM, but for a graph, and extended it a bit.
My basic premise, just like Neo4j's is that nodes and edges are both "first class citizens". This means that you can start with an edge,
and get to nodes and keep on going. However, the example I gave is one where edges are used as labels with parameters to enhance the relationship
between nodes. Therefore, you can reach nodes through edges with certain labels and basically traverse the graph as if you were dealing with objects.

For isntance, let's say you were dealing with facebook data, with GORM you can do this:
$user->friends->likes->find('url'); or $user->comments->map(); (to get all of them)

Please consider that this is a Work in Progress, and feel free to fork and add your own stuff...

I have added to examples for actual data models within /GraphModel/Models
As a convention I name node structures with a small "o" in the file name (as in oUser.php)
and edges are named with "_" in the file name (as in _Friend.php)

Needs to be done:
* Exception handling
* Better handling for setters and getters


This works alongside Josh Adell's excellent neo4jphp: 
https://github.com/jadell/Neo4jPHP
// I've added a couple of things to Neo4jPHP which allow for cypher batches and accessing returned data blocks in an easier manner
// I'm working on getting that pulled in...

The excellent graph DB I am using for this project is Neo4j:
https://github.com/neo4j/neo4j

Of course the same concept could be transported to a different db as well...