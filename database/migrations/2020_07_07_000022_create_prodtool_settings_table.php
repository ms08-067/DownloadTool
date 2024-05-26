<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateProdtoolSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prodtool_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50);
            $table->text('value')->nullable()->default(null);
            $table->string('note')->nullable()->default(null);

            $table->unique(["type"]);
        });
        if(env('APP_ENV') == 'dev' || env('APP_ENV') == 'test' || env('APP_ENV') == 'prod'){
            $data = [
                [
                    'id' => 1,
                    'type' => 'meeting_minute_default',
                    'value' => '15',
                    'note' => NULL
                ],
                [
                    'id' => 2,
                    'type' => 'meeting_minute',
                    'value' => NULL,
                    'note' => NULL
                ],
                [
                    'id' => 3,
                    'type' => 'meeting_team',
                    'value' => NULL,
                    'note' => NULL
                ],
                [
                    'id' => 4,
                    'type' => 'level_1_bonus',
                    'value' => '250',
                    'note' => 'vnd'
                ],
                [
                    'id' => 5,
                    'type' => 'level_2_bonus',
                    'value' => '300',
                    'note' => 'vnd'
                ],
                [
                    'id' => 6,
                    'type' => 'level_3_bonus',
                    'value' => '500',
                    'note' => 'vnd'
                ],
                [
                    'id' => 7,
                    'type' => 'level_4_bonus',
                    'value' => '600',
                    'note' => 'vnd'
                ],
                [
                    'id' => 8,
                    'type' => 'level_5_bonus',
                    'value' => '750',
                    'note' => 'vnd'
                ],
                [
                    'id' => 9,
                    'type' => 'level_6_bonus',
                    'value' => '850',
                    'note' => 'vnd'
                ],
                [
                    'id' => 10,
                    'type' => 'level_7_bonus',
                    'value' => '950',
                    'note' => 'vnd'
                ],
                [
                    'id' => 11,
                    'type' => 'level_8_bonus',
                    'value' => '1050',
                    'note' => 'vnd'
                ],
                [
                    'id' => 12,
                    'type' => 'fix_bonus',
                    'value' => '{\"1\":\"750\", \"2\":\"3000\", \"3\":\"6000\"}',
                    'note' => 'vnd'
                ],
                [
                    'id' => 13,
                    'type' => 'training_bonus',
                    'value' => '0.50',
                    'note' => '%'
                ],
                [
                    'id' => 14,
                    'type' => 'admin_bonus',
                    'value' => '0.15',
                    'note' => '%'
                ],
                [
                    'id' => 15,
                    'type' => 'wp_bonus',
                    'value' => '0.15',
                    'note' => '%'
                ],
                [
                    'id' => 16,
                    'type' => 'tr_bonus',
                    'value' => '0.15',
                    'note' => '%'
                ],
                [
                    'id' => 17,
                    'type' => 'js_bonus',
                    'value' => '0.10',
                    'note' => '%'
                ],
                [
                    'id' => 18,
                    'type' => 'qc_bonus',
                    'value' => '0.55',
                    'note' => '%'
                ],
                [
                    'id' => 19,
                    'type' => 'pm_bonus',
                    'value' => '0.05',
                    'note' => '%'
                ],
                [
                    'id' => 20,
                    'type' => 'pause_limit',
                    'value' => '30',
                    'note' => 'minute, can pause if shiftpause <= pause_limit. pause_limit = 0 is unlimited'
                ],
                [
                    'id' => 21,
                    'type' => 'qc_fines',
                    'value' => '0,30000,40000,50000,60000,70000,80000,90000,100000',
                    'note' => NULL
                ],
                [
                    'id' => 22,
                    'type' => 'qc_fine_default',
                    'value' => '30000',
                    'note' => NULL
                ],
                [
                    'id' => 23,
                    'type' => 'ldap_host',
                    'value' => 'ldaps://192.168.1.210',
                    'note' => 'ldaps://filesrv.br24vn.com'
                ],
                [
                    'id' => 24,
                    'type' => 'ldap_port',
                    'value' => '636',
                    'note' => NULL
                ],
                [
                    'id' => 25,
                    'type' => 'ldap_default_user',
                    'value' => 'read-only',
                    'note' => NULL
                ],
                [
                    'id' => 26,
                    'type' => 'ldap_default_pass',
                    'value' => '):\':8B`b-eD`ZuW%',
                    'note' => NULL
                ],
                [
                    'id' => 27,
                    'type' => 'ldap_domain',
                    'value' => 'br24vn.com',
                    'note' => NULL
                ],
                [
                    'id' => 28,
                    'type' => 'ldap_base_dn',
                    'value' => 'DC=BR24VN,DC=COM',
                    'note' => NULL
                ],
                [
                    'id' => 29,
                    'type' => 'ldap_user_filter',
                    'value' => 'memberOf=CN=Br24 User,OU=BR24VIETNAM,DC=br24vn,DC=com',
                    'note' => NULL
                ],
                [
                    'id' => 30,
                    'type' => 'ldap_new_user_ou',
                    'value' => 'NewUser',
                    'note' => 'Organizational Unit'
                ],
                [
                    'id' => 31,
                    'type' => 'ldap_br24vn_ou',
                    'value' => 'OU=BR24VIETNAM,DC=br24vn,DC=com',
                    'note' => NULL
                ],
                [
                    'id' => 32,
                    'type' => 'forbidden_file_extension',
                    'value' => 'db,ini,xml,^ds_store.*,txt,zip,rar,7z,bat,cos,^bridge.*,exe,bat,log,info,rtf,^doc.*,^xls.*,od.*,tmp,lnk,json,xmp,^_.*,otf,^fuse.*,lst,html',
                    'note' => ' '
                ],
                [
                    'id' => 33,
                    'type' => 'upload_forbidden_file_extension',
                    'value' => 'db,ini,xml,^ds_store.*,txt,zip,rar,7z,bat,cos,^bridge.*,exe,bat,log,info,rtf,,tmp,lnk,json,xmp,^_.*,otf,^fuse.*,lst,xlsx#',
                    'note' => NULL
                ],
                [
                    'id' => 34,
                    'type' => 'exiftool_properties',
                    'value' => '-DocumentID -SourceFile -FileName -Directory -FileSize -FileType -FileTypeExtension -XResolution -YResolution -Compression -PhotoshopQuality -PathCount -ClippingPathName -ICCProfileName -AlphaChannelsNames -LayerUnicodeNames -LayerIDs -WorkingPath -LayerCount -ImageWidth -ImageHeight -Path* -Color* -BitDepth -BitsPerSample -PixelsPerUnitX -PixelsPerUnitY -ProfileDescription',
                    'note' => NULL
                ],
                [
                    'id' => 35,
                    'type' => 'realstate_bonus',
                    'value' => '{\"1\":\"5000\", \"2\":\"7500\", \"3\":\"10000\", \"4\":\"12500\", \"5\":\"15000\", \"6\":\"17500\"}',
                    'note' => 'vnd'
                ],
                [
                    'id' => 36,
                    'type' => 'local_ip',
                    'value' => '192.168,118.70.56.8',
                    'note' => NULL
                ],
                [
                    'id' => 37,
                    'type' => 'bonus_expense',
                    'value' => '0',
                    'note' => NULL
                ],
            ];
            DB::table('prodtool_settings')->insert($data);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists('prodtool_settings');
     }
}
