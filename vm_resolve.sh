#!/bin/sh

#https://stackoverflow.com/questions/53687051/linux-command-line-error-message-temporary-failure-in-name-resolution
#if you can ping the ipaddress but cannot ping the domain name
#ping 8.8.8.8
#ping google.com
#ping: google.com: Temporary failure in name resolution

sudo apt install net-tools

#1) Disable the systemd-resolved service
sudo systemctl disable systemd-resolved.service
#2) Stop the Service
sudo systemctl stop systemd-resolved.service
#3) Remove the Configuration file manually
sudo rm /etc/resolv.conf
#4) Now, Create the file again
#sudo nano /etc/resolv.conf
#5) Enter this Lines and save it
echo "nameserver 8.8.8.8" > /etc/resolv.conf
#6) Enable the Service
sudo systemctl enable systemd-resolved.service
#7) Start the Service back 
sudo systemctl start systemd-resolved.service