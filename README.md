GORM (Graph Object Relational Model) - WIP
====================================
If you are familiar with ORM's and MVC framworks, then you know that a lot of developers prefer to treat db entities and their relations as objects.
Thereby, we are able to produce queries like: 
$user->findOne('username'); or get data like so: $user->username;

A graph database makes this a little bit harder, because for starters, everything is related:
A graph is a set of connected edges and nodes. 

The second problem is to be able to utilize the inherent relationships in graphs.
So, I basically did the same thing you are able to do with an ORM, but for a graph, and extended it a bit.

Let's say you were dealing with facebook data, with GORM you can do this:
$user->friends->likes->find('url'); or $user->comments->map(); (to get all of them)

Please consider that this is a Work in Progress, and feel free to fork and add your own stuff...

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