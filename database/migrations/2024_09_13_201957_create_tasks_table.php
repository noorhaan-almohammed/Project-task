<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['New', 'In Progress','Pending','Completed','In Testing' , 'Successed' , 'Failed']);
            $table->enum('priority', ['Low', 'Medium', 'High']);
            $table->integer('execute_time');
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->string('tester_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
