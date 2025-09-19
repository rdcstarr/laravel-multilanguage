# Laravel Multilanguage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)

> Elegant package for managing **multilanguage metadata** in Laravel — with intelligent per‑language caching and a fluent API.

## ✨ Features

- 🌍 **Multiple languages** – easily switch & manage localized content
- 🔑 **Key-value metadata** – structured, namespaced keys per language
- ⚡ **Smart cache** – per-language forever cache with auto invalidation
- 📦 **Batch operations** – set or fetch many keys at once
- 🔄 **Fluent API** – expressive chaining ( `metadata()->lang('en')->set(...)` )
- 🧩 **Blade directives** – simple templating helpers & conditionals
- 🗄️ **Clean schema** – normalized tables with FK constraints

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
- --force : Skip interactive confirmation.

## 🔑 Usage

### Set Values
```php
// Single key per language
metadata()->lang('en')->set('site.title', 'Welcome to our website');
metadata()->lang('ro')->set('site.title', 'Bine ai venit pe site-ul nostru');

// Batch (multiple keys)
metadata()->lang('en')->setMany([
    'nav.home'        => 'Home',
    'nav.about'       => 'About',
    'nav.contact'     => 'Contact',
    'site.description'=> 'Best website ever',
]);
```

### Get Values
```php
$title = metadata()->lang('en')->get('site.title');
$titleWithDefault = metadata()->lang('es')->get('site.title', 'Default Title');

// Multiple
$navigation = metadata()->lang('en')->getMany(['nav.home', 'nav.about', 'nav.contact']);

// All for a language
$allEnglish = metadata()->lang('en')->all();

// Current app locale (app()->getLocale())
$localized = metadata()->get('site.title', 'Fallback');
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
    metadata()->lang($lang)->set("demo.message", "Message for {$lang}");
}
```

### Facade
```php
use Rdcstarr\Multilanguage\Facades\Metadata;

Metadata::lang('en')->set('app.name', 'My App');
Metadata::lang('ro')->set('app.name', 'Aplicația Mea');
$appName = Metadata::lang('en')->get('app.name');
```

### Helper
```php
// Manager instance
overload($manager = metadata()); // same as app('metadata')

// Direct access (current locale)
$title = metadata('site.title', 'Default Title');
```

### Extra Operations
```php
metadata()->lang('en')->has('site.title');          // existence
metadata()->lang('en')->forget('old.unused.key');   // delete one
metadata()->lang('en')->flushCache();               // clear cache for one language
metadata()->flushAllCache();                        // clear cache for all languages
```

## 🎨 Blade Directives
```php
{{-- Current locale value (with optional default) --}}
@metadata('site.title', 'Default Title')

{{-- Specific language --}}
@metadataForLang('"en"', '"site.title"', '"Default"')

{{-- Conditional (current locale) --}}
@hasMetadata('site.title')
    <h1>{{ metadata('site.title') }}</h1>
@endhasMetadata

{{-- Conditional (specific language) --}}
@hasMetadataForLang('"ro"', '"site.title"')
    <h1>{{ metadata()->lang('ro')->get('site.title') }}</h1>
@endhasMetadataForLang
```

### Placeholders

#### Basic placeholders with transformations
```php
$line = "Hello :NAME, your :type_plural are ready! :message_limit";
$result = metadata()->placeholders($line, [
    'name' => 'john doe',        // key stays lowercase
    'type' => 'order',
    'message' => 'This is a very long message that will be truncated at 50 characters automatically',
]);
// Result: "Hello JOHN DOE, your orders are ready! This is a very long message that will be truncat..."
```

#### Available transformations
```php
// From a single key 'name' => 'john doe', you can use:
$line = "Hello :name! Welcome :Name! Shout :NAME! Method: :name_camel, File: :name_snake.txt";
$result = metadata()->placeholders($line, ['name' => 'john doe']);
// Result: "Hello john doe! Welcome John doe! Shout JOHN DOE! Method: johnDoe, File: john_doe.txt"
```

#### Advanced placeholders with pipe transformations
```php
$line = "Welcome to :app_name|config! Your profile: :user|route(profile.show)";
$result = metadata()->advancedPlaceholders($line, [
    'app_name' => 'app.name',           // will call config('app.name')
    'user' => 'profile.show',           // will call route('profile.show')
]);
```

#### Laravel context helpers
```php
// laravelPlaceholders() auto-populates common Laravel data
$data = metadata()->laravelPlaceholders(['custom' => 'value']);

// $data now contains:
// [
//     'custom' => 'value',                    // your custom data
//     'app_name' => 'Laravel',                // config('app.name')
//     'app_env' => 'local',                   // config('app.env')
//     'app_url' => 'http://localhost',        // config('app.url')
//     'user_id' => 1,                         // auth()->user()->id (if logged in)
//     'user_name' => 'John Doe',              // auth()->user()->name (if logged in)
//     'user_email' => 'john@example.com',     // auth()->user()->email (if logged in)
//     'request_path' => 'home',               // request()->path()
//     'request_url' => 'http://localhost/home', // request()->url()
//     'request_method' => 'GET',              // request()->method()
//     'now' => '2025-09-19 10:30:00',         // now()->toDateTimeString()
//     'today' => '2025-09-19',                // today()->toDateString()
//     'timestamp' => 1726740600,              // time()
// ]

$line = "App: :app_name, User: :user_name, Today: :today, Custom: :custom_upper";
$result = metadata()->placeholders($line, $data);
// Result: "App: Laravel, User: John Doe, Today: 2025-09-19, Custom: VALUE"
```

## 💡 Examples

### Website Content
```php
metadata()->lang('en')->setMany([
  'home.hero.title'    => 'Welcome to Our Platform',
  'home.hero.subtitle' => 'The best solution for your business',
]);
metadata()->lang('ro')->setMany([
  'home.hero.title'    => 'Bine ai venit pe Platforma Noastră',
  'home.hero.subtitle' => 'Cea mai bună soluție pentru afacerea ta',
]);
```

### SEO Meta
```php
metadata()->lang('en')->setMany([
  'seo.home.title' => 'Home - Best Platform Ever',
  'seo.home.description' => 'Discover our amazing platform features',
]);
<title>{{ metadata()->get('seo.' . request()->route()->getName() . '.title', 'Default') }}</title>
```

### User Personalization
```php
$userId = auth()->id();
metadata()->lang('en')->setMany([
  "user.{$userId}.welcome" => 'Welcome back!',
]);
metadata()->lang('ro')->setMany([
  "user.{$userId}.welcome" => 'Bine ai revenit!',
]);
```

### Product Catalog
```php
$productId = 123;
metadata()->lang('en')->setMany([
  "product.{$productId}.name" => 'Premium Laptop',
  "product.{$productId}.description" => 'High-performance laptop',
]);
```

## 🏗️ Database Schema

### languages
```sql
CREATE TABLE languages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL,
    flag VARCHAR(10),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_code (code)
);
```

### metadata
```sql
CREATE TABLE metadata (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    language_id BIGINT NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    `value` TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lang_key (language_id, `key`)
);
```

## ⚡ Performance
- Per-language cache buckets
- Forever cache via rememberForever
- Automatic invalidation on writes / deletes
- Minimal queries (one load per language as needed)
- Batch write operations

## 🔧 Configuration
```php
// Explicit language
$title = metadata()->lang('en')->get('site.title');

// Current locale
$title = metadata()->get('site.title');

// Change locale then fetch
app()->setLocale('ro');
$title = metadata()->get('site.title');
```

## 🧪 Testing
```bash
composer test
```

## 📖 Resources
- [Changelog](CHANGELOG.md) for recent changes.

## 👥 Credits
- [Rdcstarr](https://github.com/rdcstarr)

## 📜 License
- [License](LICENSE.md) for more information.
