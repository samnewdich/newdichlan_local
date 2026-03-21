NEWDICH LAN(LOCAL AREA NETWORK LOCAL WEB ENGINNERING ON KALI LINUX)
NOTE: NODE.JS SERVICE SCRIPTS ARE STORED IN THE nodejs_services folder

MANAGING PLANS
(1) TO pull plans(plans_table) from the internet(lan.newdich.tech), the local enpoint is:
/api/addplans That is 192.168.200.1:8080/api/addplans
(2) This will pull the online endpoint: https://lan.newdich.tech/api/addplans
(3) A php systemd service is running on the background that checks and updates plans table locally.
(4) The name of the php script systemd service is ADD_PLANS.PHP

MANAGING WIFI AND CONNECTIONS
(1) Once users tries to connect to the wifi, it gets their details and passes it to the local endpoint at:
/newdichlan/ansofra/api/register  That is http://192.168.200.1:8080/newdichlan/ansofra/api/register
(1b) A Node.js script named PORTAL.JS detects the user's details and redirect them to the frontend 
landing page on the local web(http://192.168.200.1:8080/newdichlan/ansofra/pay). It is this page 
now that will send request to http://192.168.200.1:8080/newdichlan/ansofra/api/register
(2) Then http://192.168.200.1:8080/newdichlan/ansofra/api/register will pull the online endpoint: https://lan.newdich.tech/api/register
(3) Note: It will register the user both locally and on the API(lan.newdich.tech) and it will also 
create reserved accounts for the user both locally and on the API(lan.newdich.tech)
(4) Note: That is if the user's mac address is the first time
(5) Note: If it's an existing device, it will popup reserved accounts for the user to pay, after he has 
chosen the plan he's going for.
(6) Note: a PHP systemd service is running on the background that confirms transactions at regular 
intervals(say every 30 seconds)
(7) The name of the backgound service php script is CONFIRM_TRANSACTION.PHP
(8) Note: Another Node.js script is running a systemd service on the background that gets every connected device's mac address 
and check if the device has paid and eligible for internet access. The node.js script depends on 
information from the CONFIRM_TRANSACTION.PHP service
the name of the node.js script is WIFI_MANAGER.JS as it manages the block and unblock devices locally, 
based on their sub_status.
(9) Note: There is another PHP systemd service running on the background that takes the data and status 
of users(and devices) to the API(lan.newdich.tech) to update it. The name of the PHP service script 
is UPDATE_USER.PHP