<?php

namespace Database\Seeders;

use App\Models\EmailBlacklist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class EmailBlacklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // URL from which to fetch the list of disposable domains
        $url = config('email-blacklist.url');

        // Fetch the text data from the URL
        $response = Http::get($url);

        // Check if the request was successful
        if ($response->successful()) {
            // Get the response body as plain text
            $domainsText = $response->body();

            // Split the text by new lines to get each domain
            $domains = explode("\n", $domainsText);

            // Iterate through each domain and insert it into the database
            foreach ($domains as $domain) {
                // Clean up any extra spaces or empty lines
                $domain = trim($domain);

                if (! empty($domain) && ! EmailBlacklist::where('domain', $domain)->exists()) {
                    // Insert the domain into the EmailBlacklist table if it's not already present
                    EmailBlacklist::create(['domain' => $domain]);
                }
            }

            $this->command->info('从 URL 更新邮箱域名黑名单成功');
        } else {
            $this->command->error('从 URL 更新邮箱域名黑名单失败');
        }

        // Append local domains from configuration file, check for duplicates
        $localDomains = config('email-blacklist.append');

        foreach ($localDomains as $domain) {
            // Clean the domain and check if it already exists in the database
            $domain = trim($domain);

            if (! empty($domain) && ! EmailBlacklist::where('domain', $domain)->exists()) {
                // Insert the domain if it's not already present in the database
                EmailBlacklist::create(['domain' => $domain]);
            }
        }

        $this->command->info('添加本地邮箱域名黑名单成功');
    }
}
