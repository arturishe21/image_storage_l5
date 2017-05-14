<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//todo merge this changes one with basic migration after patch
class ApiPatchMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vis_videos', function (Blueprint $table) {
            $table->renameColumn('id_youtube', 'api_id');
            $table->dropColumn('youtube_data');
            $table->string('api_provider', 32);
        });

        DB::table('vis_videos')->update(['api_provider' => 'youtube']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vis_videos', function (Blueprint $table) {
            $table->renameColumn('api_id', 'id_youtube');
            $table->text('youtube_data');
            $table->dropColumn('api_provider');
        });
    }
}
