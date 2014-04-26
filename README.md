This repo contains a set of scripts and instructions to setup a minimally functional server/database for uploading ODB2 data logged from your car in real-time using the [Torque Pro](https://play.google.com/store/apps/details?id=org.prowl.torque) app for Android.


### Setup ###

These instructions assume you already have a LAMP-like server or have access to one. Specifically, you'll need the following:

  * MySQL database
  * Apache webserver
  * PHP server-side scripting

Everything was tested on a computer running Ubuntu 12.04 with MySQL 5.5, Apache 2.2, and PHP 5.3, but many other configurations will work. If you need help setting up these prerequisites, there is obviously a ton of information on Google on how to configure a LAMP server, but I'd recommend one of [these guides](https://library.linode.com/lamp-guides/ubuntu-12.04-precise-pangolin) by Linode or [this guide](https://help.ubuntu.com/community/ApacheMySQLPHP) by Ubuntu.

In general, you don't necessarily need to do everything exactly the same way as I do it here, but my intentions are to provide a reliable configuration that incorporates as many best practices as possible.


### Create Empty MySQL Database & Configure User ###

First we'll create an empty database that will be configured further in the next section. Once we have an empty database, we'll then create a MySQL user and provide them with the necessary permissions on the database.

Start by opening a MySQL shell as the root user. Then create a database named `torque` and create a user with permission to insert and read data from the database. In this tutorial, we'll create a user `steve` with password `zissou44` that has access to all tables in the database `torque` from `localhost`:

```sql
CREATE DATABASE torque;
CREATE USER 'steve'@'localhost' IDENTIFIED BY 'zissou44';
GRANT USAGE, FILE TO 'steve'@'localhost'; -- FILE permission required for csv export
GRANT ALL PRIVILEGES ON torque.* TO 'steve'@'localhost';
FLUSH PRIVILEGES;
```

### Create MySQL Options File ###

An [options file](https://dev.mysql.com/doc/refman/5.5/en/option-files.html) is a MySQL configuration file that allows us to login to the database without typing out the username and password each time. We'll also set the permissions on this file so that it is just as secure as typing in your MySQL user/password manually each time.

Create a file in your home directory called `.my.cnf` (e.g. */home/myuser/.my.cnf*) and enter the following text into it, replacing the user/password with the one you created:

```
[client]
user="steve"
password="zissou44"
```

To protect the contents of this file, set the permissions on it so only the owner of the file (i.e. your system user) can read it and write to it:

```bash
chmod 600 ~/.my.cnf
```


### Create MySQL Table ###


Next we'll create a table in the database to store the raw log data sent from Torque. I've provided a shell script in this repo that will do this for you. Open a terminal in the folder where you cloned this repo and, assuming you put your MySQL options file in your home directory, simply run:

```
git clone https://github.com/econpy/torque
cd torque
mysql < create_torque_log_table.sql
```


### Configure Webserver ###


At this point, the MySQL settings are all configured. The only thing left to do related to the database is to add your MySQL user/password to the PHP script. Open the `creds.php` file and enter your MySQL user and password in the blank **$db_user** and **$db_pass** fields as I've done below:

```php
...
$db_host = "localhost";
$db_user = "steve";
$db_pass = "zissou44";
$db_name = "torque";
$db_table = "raw_logs";
...
```

Now move the `torque.php` and `creds.php` file to your webserver and set the appropriate permissions on it. Assuming the document root for your Apache server is located at /var/www, you could do:

```bash
mkdir /var/www/torque
cp torque.php /var/www/torque/
cp creds.php /var/www/torque/
chmod 755 /var/www/torque/
chmod 644 /var/www/torque/torque.php
```

The last two lines set the permissions seperately for the directory we made and the PHP file. In general, directories on your webserver should have 755 permissions and files should have 644.


### Configure Torque Settings ###


To use your database/server with Torque, open the app on your phone and navigate to:

```
Settings -> Data Logging & Upload -> Webserver URL
```

Enter the URL to your **torque.php** script and press `OK`. Test that it works by clicking `Test settings` and you should see a success message like the image on the right:

<div align="center" style="padding-bottom:15px;"><a href="https://storage.googleapis.com/torque_github/torque_webserver_url.png" target="_blank"><img src="https://storage.googleapis.com/torque_github/torque_webserver_url.png" width="49%" align="left"></img></a><a href="https://storage.googleapis.com/torque_github/torque_test_passed.png" target="_blank"><img src="https://storage.googleapis.com/torque_github/torque_test_passed.png" width="49%" align="right"></img></a></div>

The final thing you'll want to do before going for a drive is to check the appropriate boxes on the `Data Logging & Upload` page under the `REALTIME WEB UPLOAD` section. Personally, I have both **Upload to webserver** and **Only when ODB connected** checked.

At this point, you should be all setup. The next time you connect to Torque in your car, data will begin syncing into your MySQL database in real-time!


### Mapping Data in Real Time ###

The `map.php` file provides a website that plots the Latitude/Longitude data from the database in real time.

Most of the PHP/MySQL settings are in the `mapdata.php` file. Some that you may be interested in changing are the limit on the data points selected from the database or the centering location of the map. By default, up to 5000 points are selected and the map is centered on the most recent point.

The [Google Maps API](https://developers.google.com/maps/documentation/javascript/tutorial) settings in the JavaScript of the `map.php` file can also be modified to suit your needs. For example, you may want to alter the default zoom of the map, the color/opacity/etc of the roadmap line, and any other Google Maps API settings. Parts of the JavaScript are generated from PHP variables generated in `mapdata.php` such as the latitude/longitude points making up the path of the roadmap and the centering location of the map.

To use the map, simply move the `map.php` and `mapdata.php` to the same folder you put `creds.php` (or just make another copy of creds.php).

```bash
cp map.php /var/www/torque/
cp mapdata.php /var/www/torque/
chmod 644 -R /var/www/torque/*.php
```

Then you can view a Google Map with real time location data pulled from the created from the database used with Torque by going to `www.yourdomain.com/torque/map.php`. The resulting map will be style like this:

<div align="center"><img src="https://s3.amazonaws.com/torque_maps/mapexample.png"></div>


### Getting Raw Data From the Database ###


Once you've collected some data in the database, you will eventually want to get it out and look at it. In this repo you'll find a shell script `dbdump_to_csv.sh` which will dump all of the data out of the database into a nicely formatted CSV file. It uses the `~/.my.cnf` file created earlier to login to the database and creates a folder `torque_data` in the repo (the first time it is run) before putting creating a CSV file named with today's date in the folder.

The `dbdump_to_csv.sh` script would work well as a cronjob if you wanted to create a CSV file every day with your data. Otherwise to run it manually, simply do:

```bash
sh ./dbdump_to_csv.sh
```


### Coming Soon ###

  * Create dynamic visualizations of the data in the database inside a webapp.
  * Provide Python scripts that use [pandas](http://github.com/pydata/pandas) to parse/clean the dumped CSV files and perform analyses on the data.


