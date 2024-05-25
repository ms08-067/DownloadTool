#!/bin/bash

#The most common debug method that you have been applying to fix the download upload server ..

#need to verify that all the failed jobs are only from auto download mechanism
# because you don't want to affect the manual_dl and manual_ul
# or treat them separately... 

cd /var/www/src/alpine
php artisan regularly:quickly_perform_revert_back_to_working_state  [TODO]
#1) set the scheduled task toggle to 2 (2 = dsiabled. To by pass the scheduler so no more jobs are processed)
#2) check if there are any failed jobs.. put them back into the jobs queue

php artisan queue:retry --queue="default"

SELECT * FROM jobs WHERE queue = ''

default

manualdl_messageaftermovetodirectory
manualdl_download
manualdl_unzip
manualdl_unzipchecks
manualdl_movetodirectory
manualdl_extractedcheckscan

autodl_download
autodl_unzip
autodl_messageaftermovetodirectory
autodl_unzipchecks
autodl_extractedcheckscan
autodl_movetodirectory

manualul_afterzip_movetojobfolderreadydirectory
manualul_send_the_ready_zip_to_s3
manualul_check_the_progress_of_ready_zip_to_s3
manualul_afterzip_movetojobfolderreadydirectory_send_message
manualul_checkthenzip


#the script needs to be three parts sadly.. because the only way to rectify a NETWORK error is to restart the container ..
#and I don't think that simply issuing sudo reboot now will cut it



#3) get a list of all the jobs on the jobs table, and extract the caseIDs .. (these are the case id's that are most likely to have failed. )
#4) if you can use the aws sdk to actually download the xml from the bucket and then copy them back to the 

scan /br24storage/br24/xml/17258/tmpXml
copy to /br24storage/br24/xml/17258/

#delete traces from tasks_downloads_files table
#delete traces from tasks_downloads table
SELECT * FROM tasks_downloads_files WHERE state !="notified"



#on the container need to remove all the logs from
#remove any log/ files in these folders
sudo rm /var/www/src/alpine/storage/logs/openrc/*.*
sudo rm /var/www/src/alpine/storage/logs/laravel.log

sudo rm /var/www/src/alpine/storage/logs/data_sdb/*.*
sudo rm /var/www/src/alpine/storage/logs/data_sdb/progress/*.*
sudo rm /var/www/src/alpine/storage/logs/data_sdb/unzip_log/*.*

#remove any folders contained in these folders
sudo rm -R /var/www/src/alpine/storage/app/data_sdb_unzipfolder/*
sudo rm -R /var/www/src/alpine/storage/app/data_sdb_temp/job/*
sudo rm -R /var/www/src/alpine/storage/app/data_sdb_temp/job_check_newzip_contains_example/*
sudo rm -R /var/www/src/alpine/storage/app/data_sdb_temp/xml/*