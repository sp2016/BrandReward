
DigitalWindow API Client

Copyright (C) 2008 Digital Window Ltd.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.


Introduction
============
This DigitalWindow API Client is provided without any level of guarantee or support. Its purpose is to demonstrate the correct implementation of the DigitalWindow APIs and to potentially form the platform, upon which bespoke applications can be deployed.



Installation & Usage
====================
All you need to do is open up the "constants.inc.php" file and enter your API username & password along with which API you want to use and what version.

The client works independently, provided that your system meets the minimum requirements of PHP v.4.3.1, compiled with all the necessary extensions to successfully issue and receive SOAP messages.

You can also include the client, within your own DigitalWindow API based application.



Troubleshooting
===============
Q:  I get an error message like: "WSDL file cannot be loaded from [...]"

A: This is usually due to your server's firewall or PHP settings. 
For PHP settings, login with root access to the server, then change the php.ini setting "allow_url_fopen" to be ON and restart your HTTP server. For Firewall settings, make the necessary changes to allow a HTTP connection from your server to ours and vice versa.

