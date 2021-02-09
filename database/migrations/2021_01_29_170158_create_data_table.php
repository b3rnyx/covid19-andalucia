<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

			DB::statement('SET FOREIGN_KEY_CHECKS = 0');

			Schema::create('data', function (Blueprint $table) {

				$table->increments('id');

				$table->date('date')->nullable();

				$table->string('region', 3)->nullable();
				$table->string('province', 2)->nullable();
				$table->string('district', 5)->nullable();
				$table->string('city', 5)->nullable();

				$table->integer('confirmed')->nullable();
				$table->integer('increase')->nullable();
				$table->integer('confirmed_7d')->nullable();
				$table->decimal('incidence_7d', 12, 5)->nullable();
				$table->integer('confirmed_14d')->nullable();
				$table->decimal('incidence_14d', 12, 5)->nullable();
				$table->integer('confirmed_total')->nullable();
				$table->integer('hospitalized')->nullable();
				$table->integer('hospitalized_total')->nullable();
				$table->integer('uci')->nullable();
				$table->integer('uci_total')->nullable();
				$table->integer('recovered')->nullable();
				$table->integer('dead')->nullable();
				$table->integer('dead_total')->nullable();

				// Índices
				$table->index(
					[
						'date', 'region', 'province', 'district', 'city',
					],
					'main_index'
				);

				// Claves foráneas
				$table->foreign('region')
					->references('code')
					->on('regions');
				$table->foreign('province')
					->references('code')
					->on('provinces');
				$table->foreign('district')
					->references('code')
					->on('districts');
				$table->foreign('city')
					->references('code')
					->on('cities');
				
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
			Schema::dropIfExists('data');
			DB::statement('SET FOREIGN_KEY_CHECKS = 1');
			
    }
}
