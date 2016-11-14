<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ImageStorageMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vis_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('file_folder');
            $table->text('file_source');
            $table->string('title',255);
            $table->text('exif_data');
            $table->dateTime('date_time_source');
            $table->timestamps();
        });

        Schema::create('vis_galleries', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title',255);
            $table->dateTime('event_date');
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        Schema::create('vis_tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title',255);
            $table->tinyInteger('is_active');
            $table->timestamps();
        });

        Schema::create('vis_galleries2tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_gallery')->unsigned();
            $table->integer('id_tag')->unsigned();

            $table->foreign('id_gallery')->references('id')->on('vis_galleries')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_tag')->references('id')->on('vis_tags')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('vis_images2galleries', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_image')->unsigned();
            $table->integer('id_gallery')->unsigned();
            $table->tinyInteger('is_preview');
            $table->tinyInteger('priority');


            $table->foreign('id_image')->references('id')->on('vis_images')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_gallery')->references('id')->on('vis_galleries')->onDelete('cascade')->onUpdate('cascade');

        });

        Schema::create('vis_images2tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_image')->unsigned();
            $table->integer('id_tag')->unsigned();

            $table->foreign('id_image')->references('id')->on('vis_images')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_tag')->references('id')->on('vis_tags')->onDelete('cascade')->onUpdate('cascade');
        });




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vis_images2galleries');
        Schema::dropIfExists('vis_images2tags');
        Schema::dropIfExists('vis_galleries2tags');
        Schema::dropIfExists('vis_images');
        Schema::dropIfExists('vis_galleries');
        Schema::dropIfExists('vis_tags');


    }
}
