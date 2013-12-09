## Torque for Android: PHP/MySQL Config ##

This repo contains the files for a minimally functional PHP script and MySQL database which accepts realtime ODB2 data logged from your car using the [Torque Pro](https://play.google.com/store/apps/details?id=org.prowl.torque) app for Android.


### Setup ###

These instructions assume you already have LAMP-like setup running on your server. If not, then Google is your friend to get one setup. If you're using Ubuntu 12.04, the [Linode docs](https://library.linode.com/lamp-guides/ubuntu-12.04-precise-pangolin) are always a great start.


##### Create MySQL Database #####

Start by creating the MySQL database and table needed using the `create_initial_db.sql` dumpfile:

```bash
mysql -u MYUSERNAME -p < create_initial_db.sql
```


##### Configure MySQL Credentials #####

Next modify the `torque.php` script by adding your MySQL username/password (and host if it is not localhost). Unless you modified the `create_initial_db.sql` file, leave the **$db_name** and **$db_table** fields as is.

```php
...
// Establish db connection
$db_host = "localhost";
$db_user = "MYUSERNAME";
$db_pass = "MYSECRETPASSWORD";
$db_name = "torque";
$db_table = "raw_logs";
...
```


##### Configure Webserver #####

Now move the `torque.php` script to your webserver. If the document root for your Apache server is */var/www*, you could do something like:

```bash
mkdir -p /var/www/torque
cp torque.php /var/www/torque/
```

Assuming your domain is `www.steve.com`, navigate to `www.steve.com/torque/torque.php` and check that your script returns "OK!". If it does, you're ready to enter the URL into your Torque app.


##### Configure Torque Settings #####

Open up the Torque app on your phone and go to `Settings` -> `Data Logging & Upload` -> `Webserver URL` and enter the URL for your **torque.php** script. Then go to `Test settings` and make sure Torque works with the script.

If your test worked properly, just check the appropriate boxes under *REALTIME WEB UPLOAD* (I have both *Upload to webserver* and *Only when ODB connected* checked).

Now you should be all setup and the next time you connect to your car with Torque the data will begin syncing into your MySQL database in realtime!


### Coming Soon ###

  * Dynamically render the data with visualizations in a webapp.
  * Integrate basic summary statistics and analyses of the data into the webapp.


