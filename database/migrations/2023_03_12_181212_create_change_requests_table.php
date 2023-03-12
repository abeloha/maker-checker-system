<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('admin_id')
                ->constrained('admins')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');


            $table->enum('type', ['create', 'update', 'delete']);
            $table->json('data')->nullable();

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
        Schema::dropIfExists('change_requests');
    }
}
