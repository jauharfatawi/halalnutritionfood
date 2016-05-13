<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCertificate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cCode');
            $table->date('cExpire');
            $table->integer('cStatus');
            $table->string('cOrganization');
            $table->timestamps();
        });

        Schema::create('foodProduct_certificate', function(Blueprint $table)
        {
            $table->integer('foodProduct_id')->unsigned()->index();
            $table->foreign('foodProduct_id')->references('id')->on('foodProducts')->onDelete('cascade');

            $table->integer('certificate_id')->unsigned()->index();
            $table->foreign('certificate_id')->references('id')->on('certificates')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('certificates');
        Schema::drop('foodProduct_certificate');
    }
}
