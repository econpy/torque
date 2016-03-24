This repo contains everything needed to setup an interface for uploading ODB2 data logged from your car in real-time using the [Torque Pro](https://play.google.com/store/apps/details?id=org.prowl.torque) app for Android.

The interface allows the user to:

  * View a Google Map showing your trips logged via Torque
  * Create time series plots of OBD2 data
  * Easily export data to CSV or JSON

# Demo #

[Check out the demo!](http://hda.surfrock66.com/torquetest/)

# Requirements #

These instructions assume you already have a LAMP-like server (on a Linux/UNIX based host) or have access to one. Specifically, you'll need the following:

  * MySQL database
  * Apache webserver
  * PHP server-side scripting

If in doubt, I'd recommend using Ubuntu LTS.

# Server Setup #

First clone the repo:

```bash
git clone https://github.com/surfrock66/torque
cd torque
```

### Configure MySQL ###

To get started, create a database named `torque` and a user with permission to insert and read data from the database. In this tutorial, we'll create a user `steve` with password `zissou` that has access to all tables in the database `torque` from `localhost`:

```sql
CREATE DATABASE torque;
CREATE USER 'steve'@'localhost' IDENTIFIED BY 'zissou';
GRANT USAGE, FILE TO 'steve'@'localhost';
GRANT ALL PRIVILEGES ON torque.* TO 'steve'@'localhost';
FLUSH PRIVILEGES;
```

Then create a table in the database to store the logged data using the `create_torque_log_table.sql`, the `create_torque_sessions_table.sql`, and the `create_torque_keys_table.sql` files provided in the `scripts` folder of this repo: 

```bash
mysql -u yoursqlusername -p < scripts/create_torque_log_table.sql
mysql -u yoursqlusername -p < scripts/create_torque_sessions_table.sql
mysql -u yoursqlusername -p < scripts/create_torque_keys_table.sql
```


### Configure Webserver ###

Move the contents of the `web` folder to your webserver and set the appropriate permissions. For example, using an Apache server located at `/var/www`:

```bash
mv web /var/www/torque
cd /var/www/torque
find . -type d -exec chmod 755 {} +
find . -type f -exec chmod 644 {} +
```

Rename the `creds-sample.php` file to `creds.php`:

```bash
mv creds-sample.php creds.php
```

Then edit/enter your MySQL username and password in the empty **$db_user** and **$db_pass** fields:

```php
...
$db_host = 'localhost';
$db_user = '**steve**';
$db_pass = '**zissou**';
$db_name = 'torque';
$db_table = 'raw_logs';
$db_keys_table = 'keys';
$db_sessions_table = 'sessions';
...
```


# Settings in Torque App #

To use your database/server with Torque, open the app on your phone and navigate to:

```
Settings -> Data Logging & Upload
```
Below are the options which seem to work best for optimal logging, the suggested "File Logging" interval is 1s:

<div align="center" style="padding:15px; display:block;"><img src="http://www.surfrock66.com/images/torque_screenshot_1.png" width="45%" float="left" /><img src="http://www.surfrock66.com/images/torque_screenshot_2.png" width="45%" float="right" /></div>

Additionally, I recommend changing the web logging interval to >5s.  This staggers the amount of time between upload attempts to the server, though it DOESN'T reduce the datapoints, simply spreads out their upload:

<div align="center" style="padding-bottom:15px; display:block;"><img src="http://www.surfrock66.com/images/torque_screenshot_3.png" width="45%" float="left" /><img src="http://www.surfrock66.com/images/torque_screenshot_4.png" width="45%" float="right" /></div>

Enter the URL to your **upload_data.php** script under "Webserver URL" and press `OK`. Test that it works by clicking `Test settings` and you should see a success message like the image on the right:

<div align="center" style="padding-bottom:15px; display:block;"><img src="http://www.surfrock66.com/images/torque_screenshot_5.png" width="45%" float="left" /><img src="http://www.surfrock66.com/images/torque_screenshot_6.png" width="45%" float="right" /></div>


At this point, you should be all setup. The next time you connect to Torque in your car, data will begin syncing into your MySQL database in real-time!

