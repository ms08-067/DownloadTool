#dev service up and running script 

############################################################## DEFAULT QUEUE WORKER #
sudo cp /var/www/src/alpine/openrc_queue_scripts/laravel-queue-worker-service-default /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-default
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-default
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/laravel-queue-worker-service-default.sh
sudo rc-service laravel-queue-worker-service-default start
sudo rc-service laravel-queue-worker-service-default status

############################################################## WEBSOCKETS SERVE WORKER #
sudo cp /var/www/src/alpine/openrc_queue_scripts/laravel-websockets-service /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-websockets-service
sudo chmod a+x /etc/init.d/laravel-websockets-service
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/laravel-websockets-service.sh
sudo rc-service laravel-websockets-service start
sudo rc-service laravel-websockets-service status




############################################################## MANUAL DOWNLOAD QUEUE WORKERS IN SEQUENCE #
sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_download /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualdl_download
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualdl_download
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_download.sh
sudo rc-service laravel-queue-worker-service-manualdl_download start
sudo rc-service laravel-queue-worker-service-manualdl_download status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_unzipchecks /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualdl_unzipchecks
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualdl_unzipchecks
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_unzipchecks.sh
sudo rc-service laravel-queue-worker-service-manualdl_unzipchecks start
sudo rc-service laravel-queue-worker-service-manualdl_unzipchecks status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_unzip /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualdl_unzip
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualdl_unzip
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_unzip.sh
sudo rc-service laravel-queue-worker-service-manualdl_unzip start
sudo rc-service laravel-queue-worker-service-manualdl_unzip status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_extractedcheckscan /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualdl_extractedcheckscan
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualdl_extractedcheckscan
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_extractedcheckscan.sh
sudo rc-service laravel-queue-worker-service-manualdl_extractedcheckscan start
sudo rc-service laravel-queue-worker-service-manualdl_extractedcheckscan status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_movetodirectory /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualdl_movetodirectory
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualdl_movetodirectory
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_movetodirectory.sh
sudo rc-service laravel-queue-worker-service-manualdl_movetodirectory start
sudo rc-service laravel-queue-worker-service-manualdl_movetodirectory status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_messageaftermovetodirectory /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualdl_messageaftermovetodirectory
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualdl_messageaftermovetodirectory
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_download/laravel-queue-worker-service-manualdl_messageaftermovetodirectory.sh
sudo rc-service laravel-queue-worker-service-manualdl_messageaftermovetodirectory start
sudo rc-service laravel-queue-worker-service-manualdl_messageaftermovetodirectory status






############################################################## AUTOMATIC DOWNLOAD QUEUE WORKERS IN SEQUENCE #
sudo cp /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-download /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-download
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-download
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-download.sh
sudo rc-service laravel-queue-worker-service-download start
sudo rc-service laravel-queue-worker-service-download status

sudo cp /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-unzipchecks /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-unzipchecks
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-unzipchecks
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-unzipchecks.sh
sudo rc-service laravel-queue-worker-service-unzipchecks start
sudo rc-service laravel-queue-worker-service-unzipchecks status

sudo cp /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-unzip /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-unzip
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-unzip
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-unzip.sh
sudo rc-service laravel-queue-worker-service-unzip start
sudo rc-service laravel-queue-worker-service-unzip status

sudo cp /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-extractedcheckscan /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-extractedcheckscan
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-extractedcheckscan
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-extractedcheckscan.sh
sudo rc-service laravel-queue-worker-service-extractedcheckscan start
sudo rc-service laravel-queue-worker-service-extractedcheckscan status

sudo cp /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-movetodirectory /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-movetodirectory
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-movetodirectory
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-movetodirectory.sh
sudo rc-service laravel-queue-worker-service-movetodirectory start
sudo rc-service laravel-queue-worker-service-movetodirectory status

sudo cp /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-messageaftermovetodirectory /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-messageaftermovetodirectory
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-messageaftermovetodirectory
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/automatic_download/laravel-queue-worker-service-messageaftermovetodirectory.sh
sudo rc-service laravel-queue-worker-service-messageaftermovetodirectory start
sudo rc-service laravel-queue-worker-service-messageaftermovetodirectory status






############################################################## MANUAL UPLOAD QUEUE WORKERS IN SEQUENCE #
sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_checkthenzip /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualul_checkthenzip
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualul_checkthenzip
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_checkthenzip.sh
sudo rc-service laravel-queue-worker-service-manualul_checkthenzip start
sudo rc-service laravel-queue-worker-service-manualul_checkthenzip status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory.sh
sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory start
sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message.sh
sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message start
sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3 /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3.sh
sudo rc-service laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3 start
sudo rc-service laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3 status

sudo cp /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3 /etc/init.d/
sudo sed -i -e 's/\r//g' /etc/init.d/laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3
sudo chmod a+x /etc/init.d/laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3
sudo chmod a+x /var/www/src/alpine/openrc_queue_scripts/manual_upload/laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3.sh
sudo rc-service laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3 start
sudo rc-service laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3 status