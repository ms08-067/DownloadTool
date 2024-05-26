<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('case_id', 20);
            $table->string('jobIdTitle', 20)->nullable()->default(null);
            $table->unsignedInteger('interpreter_id')->nullable()->default(null);
            $table->string('qc_checking_id')->nullable()->default(null);
            $table->unsignedTinyInteger('check_interpreter')->nullable()->default('0');
            $table->string('customerFtp')->nullable()->default(null);
            $table->string('jobTitle')->nullable()->default(null);
            $table->smallInteger('amount')->nullable()->default('0');
            $table->dateTime('deliveryProduction')->nullable()->default(null);
            $table->unsignedTinyInteger('isExpress')->nullable()->default(null);
            $table->unsignedTinyInteger('vip_job')->nullable()->default('0');
            $table->unsignedTinyInteger('mix_level')->nullable()->default('0');
            $table->float('minutesPerImage')->nullable()->default(null);
            $table->float('minute_per_image_vn')->nullable()->default(null);
            $table->text('jobInfo')->nullable()->default(null);
            $table->text('jobInfo_trans')->nullable()->default(null);
            $table->text('jobInfoProduction')->nullable()->default(null);
            $table->text('jobInfoProduction_trans')->nullable()->default(null);
            $table->unsignedInteger('customer_id')->nullable()->default(null);
            $table->unsignedInteger('status')->nullable()->default('0');
            $table->unsignedTinyInteger('sub_status')->nullable()->default('0');
            $table->unsignedTinyInteger('redo')->nullable()->default('0')->comment('0: new , 1: redo , 2: do more');
            $table->unsignedTinyInteger('is_test')->nullable()->default('0')->comment('Customer wana test some file before');
            $table->unsignedTinyInteger('passed')->nullable()->default('0');
            $table->dateTime('ready_date')->nullable()->default(null);
            $table->integer('material_id')->nullable()->default('0');
            $table->unsignedInteger('formular_id')->nullable()->default('1');
            $table->unsignedInteger('organize_id')->nullable()->default(null);
            $table->integer('is_priv')->nullable()->default(null);
            $table->tinyInteger('is_psd')->nullable()->default('0');
            $table->unsignedInteger('priv_material_id')->nullable()->default('9');
            $table->unsignedInteger('priv_formular_id')->nullable()->default('31');
            $table->unsignedTinyInteger('is_ready')->nullable()->default(null);
            $table->unsignedTinyInteger('is_wp')->nullable()->default('0');
            $table->unsignedTinyInteger('priority')->nullable()->default('1');
            $table->string('note')->nullable()->default(null);
            $table->tinyInteger('is_reopen')->nullable()->default('0');
            $table->tinyInteger('high_light')->default('0')->comment('1 hight light, 0: normal');
            $table->tinyInteger('is_output')->default('1')->comment('1 = output, 0 = input.');
            $table->smallInteger('is_paid')->default('0')->comment('0: not paid, 1: paided, 2: no paid');
            $table->smallInteger('is_bonus')->default('0')->comment('0: don\'t know, 1: yes, 2: no');
            $table->tinyInteger('is_redo')->default('0')->comment('Dau hieu nhan biet job bi redo');
            $table->text('bonus_info')->nullable()->default(null)->comment('proccess paid bonus');
            $table->integer('check_me')->default('0')->comment('QC request for checking bonus on this job');
            $table->text('reason')->comment('QC enter reason for checking');
            $table->text('review_reason')->nullable()->default(null);
            $table->unsignedInteger('customer_workflow_id')->nullable()->default(null);
            $table->unsignedSmallInteger('workflow_file_assign')->default('0');
            $table->unsignedTinyInteger('workflow_done')->default('0');
            $table->tinyInteger('is_temp_stop')->default('0');
            $table->tinyInteger('is_upload_structure')->default('0');
            $table->string('superqc');
            $table->unsignedSmallInteger('count_redo')->default('0')->comment('số lượt làm lại redo');
            $table->unsignedTinyInteger('workflow_activated')->default('0');
            $table->tinyInteger('is_fixed')->default('0');
            $table->tinyInteger('is_training')->default('0');
            $table->tinyInteger('training_created')->default('0');
            $table->tinyInteger('is_deleted')->default('0');
            $table->dateTime('created')->nullable()->default(null);
            $table->dateTime('modified')->nullable()->default(null);
            $table->tinyInteger('is_upload')->default('0');
            $table->integer('pic_will_upload')->default('0');
            $table->integer('pic_processed')->default('0');
            $table->integer('pic_uploaded')->default('0');
            $table->tinyInteger('is_scan')->default('0');
            $table->string('assign_type', 25);
            $table->string('project_id', 25);
            $table->tinyInteger('is_pair')->default('0');
            $table->dateTime('dateReady')->nullable()->default(null);
            $table->integer('upload_time')->default('0');
            $table->tinyInteger('sent_to_darlim')->default('0');
            $table->tinyInteger('is_move')->default('0');
            $table->integer('is_spliting')->default('0');
            $table->tinyInteger('is_fix')->default('0')->comment('0: normal; 1: Fix bonus');
            $table->text('list_deleted')->nullable()->default(null)->comment('files were deleted');
            $table->integer('total_file')->nullable()->default(null);
            $table->float('actual_bonus')->nullable()->default('0');
            $table->string('parent_case_id', 50)->nullable()->default(null);
            $table->string('originalJobId', 50)->nullable()->default(null);
            $table->double('bonus_setting')->nullable()->default('0');
            $table->text('move_finish_url')->nullable()->default(null)->comment('url in old_data');
            $table->tinyInteger('is_auto_assign')->nullable()->default(null);
            $table->float('minute_per_input')->nullable()->default(null);
            $table->tinyInteger('has_mapping_name')->nullable()->default(null)->comment('0: no,1: yes');
            $table->integer('realestate_level')->nullable()->default(null)->comment('3: basic; 2: basic plus, 1: premium');

            $table->index(["status"]);

            $table->index(["customer_id"]);

            $table->index(["material_id"]);

            $table->index(["is_test"]);

            $table->index(["is_training"]);

            $table->index(["deliveryProduction"]);

            $table->index(["organize_id"]);

            $table->index(["formular_id"]);

            $table->index(["is_upload"]);

            $table->unique(["case_id"]);

            // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('no action')->onUpdate('no action');
            // $table->foreign('material_id')->references('id')->on('materials')->onDelete('no action')->onUpdate('no action');
            // $table->foreign('organize_id')->references('id')->on('organizes')->onDelete('no action')->onUpdate('no action');
            // $table->foreign('formular_id')->references('id')->on('formulars')->onDelete('no action')->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('tasks');
     }
}
