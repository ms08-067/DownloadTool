#dev service up and running script 
# laravel default queue worker
sudo rc-update add laravel-queue-worker-service-default

# laravel websockets
sudo rc-update add laravel-websockets-service


# manual download
sudo rc-update add laravel-queue-worker-service-manualdl_download
sudo rc-update add laravel-queue-worker-service-manualdl_unzipchecks
sudo rc-update add laravel-queue-worker-service-manualdl_unzip
sudo rc-update add laravel-queue-worker-service-manualdl_extractedcheckscan
sudo rc-update add laravel-queue-worker-service-manualdl_movetodirectory
sudo rc-update add laravel-queue-worker-service-manualdl_messageaftermovetodirectory

# automatic download
sudo rc-update add laravel-queue-worker-service-download
sudo rc-update add laravel-queue-worker-service-unzipchecks
sudo rc-update add laravel-queue-worker-service-unzip
sudo rc-update add laravel-queue-worker-service-extractedcheckscan
sudo rc-update add laravel-queue-worker-service-movetodirectory
sudo rc-update add laravel-queue-worker-service-messageaftermovetodirectory

# manual upload
sudo rc-update add laravel-queue-worker-service-manualul_checkthenzip
sudo rc-update add laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory
sudo rc-update add laravel-queue-worker-service-manualul_afterzip_movetojobfolderreadydirectory_send_message
sudo rc-update add laravel-queue-worker-service-manualul_send_the_ready_zip_to_s3
sudo rc-update add laravel-queue-worker-service-manualul_check_the_progress_of_ready_zip_to_s3