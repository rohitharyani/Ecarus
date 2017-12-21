Place the 'ara' folder in the C:/wamp64/www folder. <br/>
<br/>
<br/>



To make wamp services available paste the following code in the C:\wamp64\alias\phpmyadmin.conf file
<br/>
---------------------------------------------------------- <br/>
Alias /phpmyadmin "c:/wamp64/apps/phpmyadmin4.6.4/" <br/>
 <br/>
<Directory "c:/wamp64/apps/phpmyadmin4.6.4/"> <br/> 
	Options Indexes FollowSymLinks MultiViews <br/>
  AllowOverride all <br/>
  Require all granted <br/>
 <br/>
# To import big file you can increase values  <br/>
  php_admin_value upload_max_filesize 128M <br/>
  php_admin_value post_max_size 128M <br/>
  php_admin_value max_execution_time 360 <br/>
  php_admin_value max_input_time 360 <br/>
</Directory> <br/>
