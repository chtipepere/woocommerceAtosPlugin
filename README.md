# woocommerceAtosPlugin
Wordpress plugin that enable Atos payment for Woocommerce.

Install
-------
Depending on your web server, copy the correct binary files on your server.
If you are on Linux, and want to know if you run 32 or 64 bits, just type:

    getconf LONG_BIT

For these binaries, don't forget to add execution rights.

    chmod +x

Put your params files too on your web server.
 
To use the credit cards logos given with this plugin, change images path in your param/pathfile.

```
D_LOGO!/wp-content/plugins/woocommerceAtos/images/!
```

Take a look at the examples atos files provided with this plugin to put the correct values in **YOUR** param files.

**Automatic response**

Create a page that contains shortcode above and fill the automatic_response_url field in admin.

```
[woocommerce_atos_automatic_response]
```
/!\ Make sure the page is public (ie no htaccess or anything that could avoid the automatic call from the bank service).

----------

Test mode
---------
Use these values to test your installation.

Credit card success infos

    Credit card n°: 4974934125497800
    Crypt key: 600
    Expiration date: anything in the future

Credit card failed infos

    Credit cart n°: 4974934125497800
    Crypt key: 655
    Expiration date: anything in the future

For 3D secure test

    Valid password: 00000000
    Invalid password: anything else

Security
---------
For security purpose, the param files **must** be located somewhere outside the webroot of your site.
Example: if your wordpress installation is something like
```
/var/www/wordpress
```
Then your param files should be located in:
```
/var/atos/param/ 
```
 
