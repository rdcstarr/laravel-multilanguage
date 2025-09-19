# Laravel Multilanguage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)

> Elegant package for managing **multilanguage mldata** in Laravel — with intelligent per‑language caching and a fluent API.

## ✨ Features

- 🌍 **Multiple languages** – easily switch & manage localized content
- 🔑 **Key-value mldata** – structured, namespaced keys per language
- ⚡ **Smart cache** – per-language forever cache with auto invalidation
- 📦 **Batch operations** – set or fetch many keys at once
- 🔄 **Fluent API** – expressive chaining ( `mldata()->lang('en')->set(...)` )
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
mldata()->lang('en')->set('site.title', 'Welcome to our website');
mldata()->lang('ro')->set('site.title', 'Bine ai venit pe site-ul nostru');

// Batch (multiple keys)
mldata()->lang('en')->setMany([
    'nav.home'        => 'Home',
    'nav.about'       => 'About',
    'nav.contact'     => 'Contact',
    'site.description'=> 'Best website ever',
]);
```

### Get Values
```php
$title = mldata()->lang('en')->get('site.title');
$titleWithDefault = mldata()->lang('es')->get('site.title', 'Default Title');

// Multiple
$navigation = mldata()->lang('en')->getMany(['nav.home', 'nav.about', 'nav.contact']);

// All for a language
$allEnglish = mldata()->lang('en')->all();

// Current app locale (app()->getLocale())
$localized = mldata()->get('site.title', 'Fallback');
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
    mldata()->lang($lang)->set("demo.message", "Message for {$lang}");
}
```

### Facade
```php
use Rdcstarr\Multilanguage\Facades\Mldata;

Mldata::lang('en')->set('app.name', 'My App');
Mldata::lang('ro')->set('app.name', 'Aplicația Mea');
$appName = Mldata::lang('en')->get('app.name');
```

### Helper
```php
// Manager instance
overload($manager = mldata()); // same as app('mldata')

// Direct access (current locale)
$title = mldata('site.title', 'Default Title');
```

### Extra Operations
```php
mldata()->lang('en')->has('site.title');          // existence
mldata()->lang('en')->forget('old.unused.key');   // delete one
mldata()->lang('en')->flushCache();               // clear cache for one language
mldata()->flushAllCache();                        // clear cache for all languages
```

## 🎨 Blade Directives
```php
{{-- Current locale value (with optional default) --}}
@mldata('site.title', 'Default Title')

{{-- Specific language --}}
@mldataForLang('"en"', '"site.title"', '"Default"')

{{-- Conditional (current locale) --}}
@hasMldata('site.title')
    <h1>{{ mldata('site.title') }}</h1>
@endhasMldata

{{-- Conditional (specific language) --}}
@hasMldataForLang('"ro"', '"site.title"')
    <h1>{{ mldata()->lang('ro')->get('site.title') }}</h1>
@endhasMldataForLang
```

### Placeholders

```php
$line = "Hello :NAME, your :type_plural are ready! :message_limit";
$result = mldata()->placeholders($line, [
    'name' => 'john doe',        // key stays lowercase
    'type' => 'order',
    'message' => 'This is a very long message that will be truncated at 50 characters automatically',
]);
// Result: "Hello JOHN DOE, your orders are ready! This is a very long message that will be truncat..."
```

## 💡 Examples

### Website Content
```php
mldata()->lang('en')->setMany([
  'home.hero.title'    => 'Welcome to Our Platform',
  'home.hero.subtitle' => 'The best solution for your business',
]);
mldata()->lang('ro')->setMany([
  'home.hero.title'    => 'Bine ai venit pe Platforma Noastră',
  'home.hero.subtitle' => 'Cea mai bună soluție pentru afacerea ta',
]);
```

### SEO Meta
```php
mldata()->lang('en')->setMany([
  'seo.home.title' => 'Home - Best Platform Ever',
  'seo.home.description' => 'Discover our amazing platform features',
]);
<title>{{ mldata()->get('seo.' . request()->route()->getName() . '.title', 'Default') }}</title>
```

### User Personalization
```php
$userId = auth()->id();
mldata()->lang('en')->setMany([
  "user.{$userId}.welcome" => 'Welcome back!',
]);
mldata()->lang('ro')->setMany([
  "user.{$userId}.welcome" => 'Bine ai revenit!',
]);
```

### Product Catalog
```php
$productId = 123;
mldata()->lang('en')->setMany([
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

### mldata
```sql
CREATE TABLE mldata (
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
$title = mldata()->lang('en')->get('site.title');

// Current locale
$title = mldata()->get('site.title');

// Change locale then fetch
app()->setLocale('ro');
$title = mldata()->get('site.title');
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
