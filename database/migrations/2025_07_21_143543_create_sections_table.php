<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->json('contents');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->string('title');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sections');
    }
}
