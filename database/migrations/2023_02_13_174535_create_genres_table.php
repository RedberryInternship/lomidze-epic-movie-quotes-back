<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('genres', function (Blueprint $table) {
			$table->id();
			$table->timestamps();
			$table->string('name', 500);
		});
	}

	public function down()
	{
		Schema::dropIfExists('genres');
	}
};
