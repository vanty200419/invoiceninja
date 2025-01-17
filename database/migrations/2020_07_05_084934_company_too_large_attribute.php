<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/vanty200419/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_large')->default(0);
        });

        Schema::table('company_tokens', function (Blueprint $table) {
            $table->boolean('is_system')->default(0);
        });

        Schema::rename('subscriptions', 'webhooks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
