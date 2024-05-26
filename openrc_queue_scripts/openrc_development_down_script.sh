#dev service up and running script 

#laravel default queue worker
sudo rc-service laravel-queue-worker-service-default stop
sudo rc-service laravel-queue-worker-service-default status

# laravel websockets
sudo rc-service laravel-websockets-service stop
sudo rc-service laravel-websockets-service status

# manual download queue worker
sudo rc-service laravel-queue-worker-service-manualdl_download stop
sudo rc-service laravel-queue-worker-service-manualdl_download status

sudo rc-service laravel-queue-worker-service-manualdl_unzipchecks stop
sudo rc-service laravel-queue-worker-service-manualdl_unzipchecks status

sudo rc-service laravel-queue-worker-service-manualdl_unzip stop
sudo rc-service laravel-queue-worker-service-manualdl_unzip status

sudo rc-service laravel-queue-worker-service-manualdl_extractedcheckscan stop
sudo rc-service laravel-queue-worker-service-manualdl_extractedcheckscan status

sudo rc-service laravel-queue-worker-service-manualdl_movetodirectory stop
sudo rc-service laravel-queue-worker-service-manualdl_movetodirectory status

sudo rc-service laravel-queue-worker-service-manualdl_messageaftermovetodirectory stop
sudo rc-service laravel-queue-worker-service-manualdl_messageaftermovetodirectory status


#automatic download queue worker
sudo rc-service laravel-queue-worker-service-download stop
sudo rc-service laravel-queue-worker-service-download status

sudo rc-service laravel-queue-worker-service-unzipchecks stop
sudo rc-service laravel-queue-worker-service-unzipchecks status

sudo rc-service laravel-queue-worker-service-unzip stop
sudo rc-service laravel-queue-worker-service-unzip status

sudo rc-service laravel-queue-worker-service-extractedcheckscan stop
sudo rc-service laravel-queue-worker-service-extractedcheckscan status

sudo rc-service laravel-queue-worker-service-movetodirectory stop
sudo rc-service laravel-queue-worker-service-movetodirectory status

sudo rc-service laravel-queue-worker-service-messageaftermovetodirectory stop
sudo rc-service laravel-queue-worker-service-messageaftermovetodirectory status


# manual upload queue worker
sudo rc-service laravel-queue-worker-service-manualul_checkthenzip stop
sudo rc-service laravel-queue-worker-service-manualul_checkthenzip status

sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory stop
sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory status

sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message stop
sudo rc-service laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message status

sudo rc-service laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3 stop
sudo rc-service laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3 status

sudo rc-service laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3 stop
sudo rc-service laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3 status


#kill all of the worker processes
pkill -f queue
pkill -f websockets