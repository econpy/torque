This repo contains everything needed to setup a server/database for uploading ODB2 data logged from your car in real-time using the [Torque Pro](https://play.google.com/store/apps/details?id=org.prowl.torque) app for Android.

Besides being able to store data from Torque into MySQL, this repo also contains a set of PHP scripts that make up a website you can use to view Torque session-level data including:

  * Google Maps of GPS data from Torque
  * Time series plots of OBD2 data.
  * Easily export data in MySQL to CSV via the web interface.


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


At this point, the MySQL settings are all configured. The only thing left to do related to the database is to add your MySQL user/password to the PHP script. Rename the `creds-sample.php` file to `creds.php` and enter your MySQL user and password in the blank **$db_user** and **$db_pass** fields as I've done below:

```php
...
$db_host = "localhost";
$db_user = "steve";
$db_pass = "zissou44";
$db_name = "torque";
$db_table = "raw_logs";
...
```

Now move the `php` files, `assets` folder, and `data` folder to your webserver and set the appropriate permissions. Assuming the document root for your Apache server is located at /var/www, you could do:

```bash
mkdir /var/www/torque
cp ./*.php /var/www/torque/
cp -r assets /var/www/torque/assets
cp -r data /var/www/torque/data
chmod -R 644 /var/www/torque/*
chmod 755 /var/www/torque/
```

The last two lines set the permissions seperately for the directory we made and the PHP files. In general, directories on your webserver should have 755 permissions and files should have 644.


### Configure Torque Settings ###


To use your database/server with Torque, open the app on your phone and navigate to:

```
Settings -> Data Logging & Upload -> Webserver URL
```

Enter the URL to your **upload_data.php** script and press `OK`. Test that it works by clicking `Test settings` and you should see a success message like the image on the right:

<div align="center" style="padding-bottom:15px;"><a href="https://storage.googleapis.com/torque_github/torque_webserver_url.png" target="_blank"><img src="https://storage.googleapis.com/torque_github/torque_webserver_url.png" width="49%" align="left"></img></a><a href="https://storage.googleapis.com/torque_github/torque_test_passed.png" target="_blank"><img src="https://storage.googleapis.com/torque_github/torque_test_passed.png" width="49%" align="right"></img></a></div>

The final thing you'll want to do before going for a drive is to check the appropriate boxes on the `Data Logging & Upload` page under the `REALTIME WEB UPLOAD` section. Personally, I have both **Upload to webserver** and **Only when ODB connected** checked.

At this point, you should be all setup. The next time you connect to Torque in your car, data will begin syncing into your MySQL database in real-time!


### GUI Screenshots ###

Here are some screenshots of what the GUI will look like once you get everything setup.

First, you'll have a drop down menu to choose between all your sessions of logged data to choose which you want to visualize:
![Session Dropdown](http://storage.googleapis.com/torque_github/demo/session_chooser.png)

Then you will also be able to choose different plots and easily export the raw data to CSV:
![Chart Dropdown](http://storage.googleapis.com/torque_github/demo/chart_dropdown.png)
![Data Export Dropdown](http://storage.googleapis.com/torque_github/demo/export_session_data.png)

As a side note, an exported CSV file can easily be opened up in Python using something like <a href="https://github.com/pydata/pandas" target="_blank">pandas</a>:
![Read CSV in Python with Pandas](http://storage.googleapis.com/torque_github/demo/read_export_csv_python.png)

If you click into the charts, you can plot 2 different series against one and other:
![View Charts](http://storage.googleapis.com/torque_github/demo/charts_1.png)

And of course, you can still switch between sessions and export data at this screen:
![Dropdown on Charts Screen](http://storage.googleapis.com/torque_github/demo/charts_1.png)

### Coming Up Next ###

  * Plot any series in any session against any other series in any session.
  * Clean up CSS so it works better on all browsers and mobile devices.
  * Add JSON data export.
  * + MORE
 
