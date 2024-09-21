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

namespace Tests\Feature\EInvoice;

use Tests\TestCase;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use Tests\MockAccountData;
use App\DataMapper\InvoiceItem;
use App\DataMapper\ClientSettings;
use App\DataMapper\CompanySettings;
use App\Factory\CompanyUserFactory;
use InvoiceNinja\EInvoice\EInvoice;
use InvoiceNinja\EInvoice\Symfony\Encode;
use App\Services\EDocument\Standards\Peppol;
use App\Services\EDocument\Standards\FatturaPANew;
use Illuminate\Routing\Middleware\ThrottleRequests;
use InvoiceNinja\EInvoice\Models\Peppol\PaymentMeans;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvoiceNinja\EInvoice\Models\FatturaPA\FatturaElettronica;
use InvoiceNinja\EInvoice\Models\Peppol\BranchType\FinancialInstitutionBranch;
use InvoiceNinja\EInvoice\Models\Peppol\FinancialAccountType\PayeeFinancialAccount;
use InvoiceNinja\EInvoice\Models\FatturaPA\FatturaElettronicaBodyType\FatturaElettronicaBody;
use InvoiceNinja\EInvoice\Models\FatturaPA\FatturaElettronicaHeaderType\FatturaElettronicaHeader;

/**
 * @test
 */
class PeppolTest extends TestCase
{
    use DatabaseTransactions;
    use MockAccountData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeTestData();


        // $this->markTestSkipped('prevent running in CI');

        $this->withoutMiddleware(
            ThrottleRequests::class
        );
    }

    public function testDeInvoice()
    {

        $settings = CompanySettings::defaults();
        $settings->address1 = 'Dudweilerstr. 34b';
        $settings->city = 'Ost Alessa'; 
        $settings->state = 'Bayern';
        $settings->postal_code = '98060';
        $settings->vat_number = 'DE923356489';
        $settings->country_id = '276';
        $settings->currency_id = '3';

        
        $einvoice = new \InvoiceNinja\EInvoice\Models\Peppol\Invoice();
        
        
        $fib = new FinancialInstitutionBranch();
        $fib->ID = "DEUTDEMMXXX"; //BIC
        $fib->Name = 'Deutsche Bank';
        
        $pfa = new PayeeFinancialAccount();
        $pfa->ID = 'DE89370400440532013000';
        $pfa->Name = 'PFA-NAME';
        $pfa->AliasName = 'PFA-Alias';
        $pfa->AccountTypeCode = 'CHECKING';
        $pfa->AccountFormatCode = 'IBAN';
        $pfa->CurrencyCode = 'EUR';
        $pfa->FinancialInstitutionBranch = $fib;

        $pm = new PaymentMeans();
        $pm->PayeeFinancialAccount = $pfa;        
        $einvoice->PaymentMeans[] = $pm;

        $company = Company::factory()->create([
            'account_id' => $this->account->id,
            'settings' => $settings,
            'e_invoice' => $einvoice,
        ]);
        
        $cu = CompanyUserFactory::create($this->user->id, $company->id, $this->account->id);
        $cu->is_owner = true;
        $cu->is_admin = true;
        $cu->is_locked = false;
        $cu->save();

        $client_settings = ClientSettings::defaults();
        $client_settings->currency_id = '3';

        $client = Client::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'name' => 'German Client Name',
            'address1' => 'Kinderhausen 96b',
            'address2' => 'Apt. 842',
            'city' => 'Süd Jessestadt',
            'state' => 'Bayern',
            'postal_code' => '33323',
            'country_id' => 276,
            'routing_id' => 'ABC1234',
            'settings' => $client_settings,
        ]);

                
        $item = new InvoiceItem();
        $item->product_key = "Product Key";
        $item->notes = "Product Description";
        $item->cost = 10;
        $item->quantity = 10;
        $item->tax_rate1 = 19;
        $item->tax_name1 = 'mwst';

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'discount' => 0,
            'uses_inclusive_taxes' => false,
            'status_id' => 1,
            'tax_rate1' => 0,
            'tax_name1' => '',
            'tax_rate2' => 0,
            'tax_rate3' => 0,
            'tax_name2' => '',
            'tax_name3' => '',
            'line_items' => [$item],
            'number' => 'DE-'.rand(1000, 100000),
            'date' => now()->format('Y-m-d')
        ]);

        $invoice = $invoice->calc()->getInvoice();
        $invoice->service()->markSent()->save();

        $this->assertEquals(119, $invoice->amount);

       
        $peppol = new Peppol($invoice);
        $peppol->setInvoiceDefaults();
        $peppol->run();

        $de_invoice = $peppol->getInvoice();

        $this->assertNotNull($de_invoice);

        $e = new EInvoice();
        $xml = $e->encode($de_invoice, 'xml');
        $this->assertNotNull($xml);

        nlog($xml);

        $errors = $e->validate($de_invoice);

        if(count($errors) > 0) {
            nlog($errors);
        }

        $this->assertCount(0, $errors);


    }


    public function testDeInvoiceInclusiveTaxes()
    {

        $settings = CompanySettings::defaults();
        $settings->address1 = 'Dudweilerstr. 34b';
        $settings->city = 'Ost Alessa'; 
        $settings->state = 'Bayern';
        $settings->postal_code = '98060';
        $settings->vat_number = 'DE923356489';
        $settings->country_id = '276';
        $settings->currency_id = '3';

        $einvoice = new \InvoiceNinja\EInvoice\Models\Peppol\Invoice();
        
        $fib = new FinancialInstitutionBranch();
        $fib->ID = "DEUTDEMMXXX"; //BIC
        $fib->Name = 'Deutsche Bank';
        
        $pfa = new PayeeFinancialAccount();
        $pfa->ID = 'DE89370400440532013000';
        $pfa->Name = 'PFA-NAME';
        $pfa->AliasName = 'PFA-Alias';
        $pfa->AccountTypeCode = 'CHECKING';
        $pfa->AccountFormatCode = 'IBAN';
        $pfa->CurrencyCode = 'EUR';
        $pfa->FinancialInstitutionBranch = $fib;

        $pm = new PaymentMeans();
        $pm->PayeeFinancialAccount = $pfa;        
        $einvoice->PaymentMeans[] = $pm;

        $company = Company::factory()->create([
            'account_id' => $this->account->id,
            'settings' => $settings,
            'e_invoice' => $einvoice,
        ]);

        $cu = CompanyUserFactory::create($this->user->id, $company->id, $this->account->id);
        $cu->is_owner = true;
        $cu->is_admin = true;
        $cu->is_locked = false;
        $cu->save();

        $client_settings = ClientSettings::defaults();
        $client_settings->currency_id = '3';

        $client = Client::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'name' => 'German Client Name',
            'address1' => 'Kinderhausen 96b',
            'address2' => 'Apt. 842',
            'city' => 'Süd Jessestadt',
            'state' => 'Bayern',
            'postal_code' => '33323',
            'country_id' => 276,
            'routing_id' => 'ABC1234',
            'settings' => $client_settings,
        ]);

                
        $item = new InvoiceItem();
        $item->product_key = "Product Key";
        $item->notes = "Product Description";
        $item->cost = 10;
        $item->quantity = 10;
        $item->tax_rate1 = 19;
        $item->tax_name1 = 'mwst';

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'discount' => 0,
            'uses_inclusive_taxes' => true,
            'status_id' => 1,
            'tax_rate1' => 0,
            'tax_name1' => '',
            'tax_rate2' => 0,
            'tax_rate3' => 0,
            'tax_name2' => '',
            'tax_name3' => '',
            'line_items' => [$item],
            'number' => 'DE-'.rand(1000, 100000),
            'date' => now()->format('Y-m-d')
        ]);

        $invoice = $invoice->calc()->getInvoice();
        $invoice->service()->markSent()->save();

        $this->assertEquals(100, $invoice->amount);

        $peppol = new Peppol($invoice);
        $peppol->setInvoiceDefaults();
        $peppol->run();

        $de_invoice = $peppol->getInvoice();

        $this->assertNotNull($de_invoice);

        $e = new EInvoice();
        $xml = $e->encode($de_invoice, 'xml');
        $this->assertNotNull($xml);

        nlog("inclusive");
        nlog($xml);

        $errors = $e->validate($de_invoice);

        if(count($errors) > 0) {
            nlog($errors);
        }

        $this->assertCount(0, $errors);

    }


    public function testInvoiceBoot()
    {

        $settings = CompanySettings::defaults();
        $settings->address1 = 'Via Silvio Spaventa 108';
        $settings->city = 'Calcinelli';

        $settings->state = 'PA';

        // $settings->state = 'Perugia';
        $settings->postal_code = '61030';
        $settings->country_id = '380';
        $settings->currency_id = '3';
        $settings->vat_number = '01234567890';
        $settings->id_number = '';

        $company = Company::factory()->create([
            'account_id' => $this->account->id,
            'settings' => $settings,
        ]);
        

        $cu = CompanyUserFactory::create($this->user->id, $company->id, $this->account->id);
        $cu->is_owner = true;
        $cu->is_admin = true;
        $cu->is_locked = false;
        $cu->save();

        $client_settings = ClientSettings::defaults();
        $client_settings->currency_id = '3';

        $client = Client::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'name' => 'Italian Client Name',
            'address1' => 'Via Antonio da Legnago 68',
            'city' => 'Monasterace',
            'state' => 'CR',
            // 'state' => 'Reggio Calabria',
            'postal_code' => '89040',
            'country_id' => 380,
            'routing_id' => 'ABC1234',
            'settings' => $client_settings,
        ]);

        $item = new InvoiceItem();
        $item->product_key = "Product Key";
        $item->notes = "Product Description";
        $item->cost = 10;
        $item->quantity = 10;
        $item->tax_rate1 = 22;
        $item->tax_name1 = 'IVA';

        $invoice = Invoice::factory()->create([
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'discount' => 0,
            'uses_inclusive_taxes' => false,
            'status_id' => 1,
            'tax_rate1' => 0,
            'tax_name1' => '',
            'tax_rate2' => 0,
            'tax_rate3' => 0,
            'tax_name2' => '',
            'tax_name3' => '',
            'line_items' => [$item],
            'number' => 'ITA-'.rand(1000, 100000),
            'date' => now()->format('Y-m-d')
        ]);

        $invoice->service()->markSent()->save();

        $peppol = new Peppol($invoice);
        $peppol->run();

        $fe = $peppol->getInvoice();

        $this->assertNotNull($fe);

        $this->assertInstanceOf(\InvoiceNinja\EInvoice\Models\Peppol\Invoice::class, $fe);

        $e = new EInvoice();
        $xml = $e->encode($fe, 'xml');
        $this->assertNotNull($xml);

        nlog($xml);

        $json = $e->encode($fe, 'json');
        $this->assertNotNull($json);

        nlog($json);
        
        $decode = $e->decode('Peppol', $json, 'json');

        $this->assertInstanceOf(\InvoiceNinja\EInvoice\Models\Peppol\Invoice::class, $decode);

        $errors = $e->validate($fe);

        if(count($errors) > 0) {
            nlog($errors);
        }

        $this->assertCount(0, $errors);

        nlog(json_encode($fe, JSON_PRETTY_PRINT));
    }
}
