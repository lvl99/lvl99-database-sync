LVL99 Database Sync
===================

LVL99 Database Sync is a WordPress plugin which allows you to easily save your WP database to an SQL file, and to also restore the database from an SQL file.

Created and maintained by [Matt Scheurich](http://www.lvl99.com)

It's creation was inspired by how I manage WP site development. I often have a local development server, a staging server and a live server. These can all each have their own database or a shared database (often I have two staging servers: one which uses local development server files and database, the other uses local development server files and the staging server's database). I've found it frustrating keeping database entries consistent across multiple sites/databases, so I figured I'd create an easy and simple solution to save and load SQL data.

Since I also use [git](http://git-scm.com/) I can use that to watch the directory where the SQL files are located and using a file naming scheme I can save and restore per development environment.

* Visit [lvl99.com/code/database-sync](http://lvl99.com/code/database-sync) for news and updates
* Fork development of this plugin at [github.com/lvl99/lvl99-database-sync](github.com/lvl99/lvl99-database-sync)