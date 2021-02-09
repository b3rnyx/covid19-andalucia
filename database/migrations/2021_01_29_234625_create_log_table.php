<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

			DB::statement('SET FOREIGN_KEY_CHECKS = 0');

			Schema::create('log', function (Blueprint $table) {

				$table->dateTime('date')->nullable();
				$table->string('action')->nullable();
				$table->text('text')->nullable();
				
				// Índices
				$table->index([
					'date',
					'action',
				]);

			});

			DB::statement('SET FOREIGN_KEY_CHECKS = 1');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

			DB::statement('SET FOREIGN_KEY_CHECKS = 0');
			Schema::dropIfExists('log');
			DB::statement('SET FOREIGN_KEY_CHECKS = 1');

		}
		
}
