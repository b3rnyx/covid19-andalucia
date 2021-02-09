<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateListsTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
			
			DB::statement('SET FOREIGN_KEY_CHECKS = 0');

			// Comunidades autónomas
			Schema::create('regions', function (Blueprint $table) {

				$table->string('code', 3)->primary();
				$table->string('name', 100);
				$table->integer('population');

				// Índices
				$table->index(
					[
						'code', 'name',
					],
					'main_index'
				);

			});

			// Provincias
			Schema::create('provinces', function (Blueprint $table) {

				$table->string('code', 2)->primary();
				$table->string('region', 3);
				$table->string('name', 100);
				$table->integer('population');

				// Índices
				$table->index(
					[
						'code', 'region', 'name',
					],
					'main_index'
				);

				// Claves foráneas
				$table->foreign('region')
					->references('code')
					->on('regions');

			});

			// Distritos
			Schema::create('districts', function (Blueprint $table) {

				$table->string('code', 5)->primary();
				$table->string('region', 3);
				$table->string('province', 2);
				$table->string('name', 100);
				$table->integer('population');

				// Índices
				$table->index(
					[
						'code', 'region', 'province', 'name',
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

			});

			// Municipios
			Schema::create('cities', function (Blueprint $table) {

				$table->string('code', 5)->primary();
				$table->string('region', 3);
				$table->string('province', 2);
				$table->string('district', 5);
				$table->string('name', 100);
				$table->integer('population');

				// Índices
				$table->index(
					[
						'code', 'region', 'province', 'district', 'name',
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
			Schema::dropIfExists('regions');
			Schema::dropIfExists('provinces');
			Schema::dropIfExists('districts');
			Schema::dropIfExists('cities');
			DB::statement('SET FOREIGN_KEY_CHECKS = 1');
			
		}
		
}
