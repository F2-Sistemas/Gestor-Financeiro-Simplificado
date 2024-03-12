<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id()->index();
            $table->string('group')->index();
            $table->string('key')->index();
            $table->json('content')->nullable();
            $table->boolean('active')->index()->nullable()->default(true);
            $table->timestamps();

            $table->index('created_at');
            $table->index('updated_at');

            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
