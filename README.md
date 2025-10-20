# Laravel Multilanguage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)

> Elegant package for managing **multilanguage locale data** in Laravel — with intelligent per‑language caching and a fluent API.

## ✨ Features

-   🌍 **Multiple languages** – easily switch & manage localized content
-   🔑 **Key-value locale data** – structured, namespaced keys per language
-   ⚡ **Smart cache** – per-language forever cache with auto invalidation
-   📦 **Batch operations** – set or fetch many keys at once
-   🔄 **Fluent API** – expressive chaining ( `localedata()->lang('en')->set(...)` )
-   🧩 **Blade directives** – simple templating helpers & conditionals
-   🗄️ **Clean schema** – normalized tables with FK constraints

## 📦 Installation

Install via Composer:

```bash
composer require rdcstarr/laravel-multilanguage
```

1. (Optional) Publish migration files:
    ```bash
    php artisan vendor:publish --provider="Rdcstarr\Multilanguage\MultilanguageServiceProvider"
    ```
2. Run migrations:
    ```bash
    php artisan migrate
    ```
3. (Recommended) Use the install command (runs migrations & seeds default languages):
    ```bash
    php artisan multilanguage:install
    ```

Default seeded languages: English (en), Romanian (ro), French (fr).

## 🛠️ Artisan Commands

#### Install package (migrations + seed languages)

```bash
php artisan multilanguage:install [--force]
```

-   --force : Skip interactive confirmation.

## 🔑 Usage

### Set Values

```php
// Single key per language
localedata()->lang('en')->set('site.title', 'Welcome to our website');
localedata()->lang('ro')->set('site.title', 'Bine ai venit pe site-ul nostru');

// Batch (multiple keys)
localedata()->lang('en')->setMany([
    'nav.home'        => 'Home',
    'nav.about'       => 'About',
    'nav.contact'     => 'Contact',
    'site.description'=> 'Best website ever',
]);
```

### Get Values

```php
$title = localedata()->lang('en')->get('site.title');
$titleWithDefault = localedata()->lang('es')->get('site.title', 'Default Title');

// Multiple
$navigation = localedata()->lang('en')->getMany(['nav.home', 'nav.about', 'nav.contact']);

// All for a language
$allEnglish = localedata()->lang('en')->all();

// Current app locale (app()->getLocale())
$localized = localedata()->get('site.title', 'Fallback');
```

### Working with Languages

```php
use Rdcstarr\Multilanguage\Models\Language;

// Create a language
Language::create([
    'name' => 'Spanish',
    'code' => 'es',
    'flag' => '🇪🇸'
]);

// Retrieve
$languages = Language::all();
$english   = Language::where('code', 'en')->first();

// Seed multiple values dynamically
foreach (['en','ro','fr','es'] as $lang) {
    localedata()->lang($lang)->set("demo.message", "Message for {$lang}");
}
```

### Facade

```php
use Rdcstarr\Multilanguage\Facades\LocaleData;

LocaleData::lang('en')->set('app.name', 'My App');
LocaleData::lang('ro')->set('app.name', 'Aplicația Mea');
$appName = LocaleData::lang('en')->get('app.name');
```

### Helper

```php
// Manager instance
localedata(); // same as app('localedata')

// Direct access (current locale)
$title = localedata('site.title', 'Default Title');
```

### Extra Operations

```php
localedata()->lang('en')->has('site.title');          // existence
localedata()->lang('en')->forget('old.unused.key');   // delete one
localedata()->lang('en')->flushCache();               // clear cache for one language
localedata()->flushAllCache();                        // clear cache for all languages
```

## 🎨 Blade Directives

```php
{{-- Current locale value (with optional default) --}}
@localedata('site.title', 'Default Title')

{{-- Specific language --}}
@localedataForLang('"en"', '"site.title"', '"Default"')

{{-- Conditional (current locale) --}}
@hasLocaledata('site.title')
    <h1>{{ localedata('site.title') }}</h1>
@endhasLocaledata

{{-- Conditional (specific language) --}}
@hasLocaledataForLang('"ro"', '"site.title"')
    <h1>{{ localedata()->lang('ro')->get('site.title') }}</h1>
@endhasLocaledataForLang
```

### Placeholders

```php
// welcome.user => "Hello :NAME, your :type_plural are ready! :message_limit";
$result = localedata()->get('welcome.user')->placeholders([
    'name' => 'john doe',
    'type' => 'order',
    'message' => 'This is a very long message that will be truncated at 50 characters automatically',
]);
// Result: "Hello JOHN DOE, your orders are ready! This is a very long message that will be truncat..."
```

## 💡 Examples

### Website Content

```php
localedata()->lang('en')->setMany([
  'home.hero.title'    => 'Welcome to Our Platform',
  'home.hero.subtitle' => 'The best solution for your business',
]);
localedata()->lang('ro')->setMany([
  'home.hero.title'    => 'Bine ai venit pe Platforma Noastră',
  'home.hero.subtitle' => 'Cea mai bună soluție pentru afacerea ta',
]);
```

### SEO Meta

```php
localedata()->lang('en')->setMany([
  'seo.home.title' => 'Home - Best Platform Ever',
  'seo.home.description' => 'Discover our amazing platform features',
]);
<title>{{ localedata()->get('seo.' . request()->route()->getName() . '.title', 'Default') }}</title>
```

### User Personalization

```php
$userId = auth()->id();
localedata()->lang('en')->setMany([
  "user.{$userId}.welcome" => 'Welcome back!',
]);
localedata()->lang('ro')->setMany([
  "user.{$userId}.welcome" => 'Bine ai revenit!',
]);
```

### Product Catalog

```php
$productId = 123;
localedata()->lang('en')->setMany([
  "product.{$productId}.name" => 'Premium Laptop',
  "product.{$productId}.description" => 'High-performance laptop',
]);
```

## 🎯 Placeholders

The package includes powerful placeholder support with default date/time placeholders and the ability to add custom ones.

### Default Placeholders (Available Automatically)

**Date & Time:**

-   `:year`, `:month`, `:day`, `:hour`, `:minute`, `:second`
-   `:month_name`, `:day_name`, `:week`, `:quarter`
-   `:date`, `:time`, `:datetime`, `:timestamp`
-   `:iso`, `:ago` (human readable)

**App Info:**

-   `:app_name`, `:app_env`, `:app_url`

### Usage in Translations

```php
localedata()->set('footer.copyright', '© :year :app_name. All rights reserved.');
// Output: © 2025 My App. All rights reserved.

localedata()->set('report.generated', 'Generated on :date at :time_short');
// Output: Generated on 2025-10-20 at 14:30
```

### Custom Placeholders

#### Method 1: Config File (Recommended for static values)

Publish config:

```bash
php artisan vendor:publish --tag=multilanguage-config
```

Edit `config/multilanguage.php`:

```php
return [
    'placeholders' => [
        'company_name' => env('COMPANY_NAME', 'My Company'),
        'support_email' => 'support@example.com',
        'support_phone' => '+40 123 456 789',
    ],
];
```

#### Method 2: In AppServiceProvider (For dynamic values)

```php
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;

public function boot()
{
    LocaleDataPlaceholders::setCustomPlaceholders([
        'total_users' => User::count(),
        'active_sessions' => Session::where('active', true)->count(),
    ]);
}
```

#### Method 3: Helper Function

```php
// Single placeholder
set_placeholder('username', 'John Doe');

// Multiple placeholders
set_placeholder([
    'product_name' => 'Super Widget',
    'product_price' => '$99.99',
]);
```

#### Method 4: Runtime (Context-specific)

```php
$message = localedata()->get('user.welcome', '', [
    'username' => $user->name,
    'last_login' => $user->last_login_at->diffForHumans(),
]);
```

### Placeholder Transformations

All placeholders support automatic transformations:

```php
set_placeholder('company_name', 'my company');

// In translations:
:company_name           // my company
:Company_name           // My company (ucfirst)
:COMPANY_NAME           // MY COMPANY (uppercase)
:company_name_camel     // myCompany
:company_name_studly    // MyCompany
:company_name_kebab     // my-company
:company_name_slug      // my-company
:company_name_plural    // my companies
:company_name_upper     // MY COMPANY
:company_name_title     // My Company
```

### Real-World Example

```php
// AppServiceProvider
LocaleDataPlaceholders::setCustomPlaceholders([
    'site_name' => config('app.name'),
    'total_products' => Product::count(),
]);

// Translation
localedata()->set('home.hero', 'Welcome to :site_name! We have :total_products amazing products.');

// Controller
$greeting = localedata()->get('user.greeting', '', [
    'username' => $user->name,
]);
// Translation: "Hello :Username! Last seen: :ago"
```

📖 **[Full Placeholders Guide](PLACEHOLDERS.md)** with more examples and advanced usage.

## ⚡ Performance

-   Per-language cache buckets
-   Forever cache via rememberForever
-   Automatic invalidation on writes / deletes
-   Minimal queries (one load per language as needed)
-   Batch write operations

## 🧪 Testing

```bash
composer test
```

## 📖 Resources

-   [Changelog](CHANGELOG.md) for recent changes.

## 👥 Credits

-   [Rdcstarr](https://github.com/rdcstarr)

## 📜 License

-   [License](LICENSE.md) for more information.
