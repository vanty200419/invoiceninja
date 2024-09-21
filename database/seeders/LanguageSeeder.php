<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        // https://github.com/caouecs/Laravel-lang
        // https://www.loc.gov/standards/iso639-2/php/code_list.php

        $languages = [
            ['id' => 1, 'name' => 'English - United States', 'locale' => 'en'],
            ['id' => 2, 'name' => 'Vietnamse', 'locale' => 'vi'],

        ];

        foreach ($languages as $language) {
            $record = Language::whereLocale($language['locale'])->first();
            if ($record) {
                $record->name = $language['name'];
                $record->save();
            } else {
                Language::create($language);
            }
        }

    }
}
