# Laravel Multilanguage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)

> Elegant package for managing **multilanguage metadata** in Laravel — with intelligent caching, language-specific content, and intuitive API.

---

## ✨ Features

- 🌍 **Multiple Languages** – manage content in multiple languages with easy switching
- 🔑 **Metadata System** – store key-value pairs for each language
- ⚡ **Intelligent Cache** – built-in cache layer optimized per language
- 🎯 **Elegant API** – clean, intuitive syntax with method chaining
- 📦 **Batch Operations** – set multiple metadata values at once
- 🔄 **Fluent Interface** – chain methods for clean, readable code
- 🗄️ **Database Relations** – proper Eloquent relationships with referential integrity

---

## 📦 Installation

```bash
composer require rdcstarr/laravel-multilanguage
```

Publish & migrate:

```bash
php artisan vendor:publish --provider="Rdcstarr\Multilanguage\MultilanguageServiceProvider" --tag="migrations" // optional
php artisan migrate
php artisan multilanguage:install
```

Or use the install command that will handle everything automatically:
```bash
php artisan multilanguage:install
```

This command will:
- Publish and run migrations
- Seed the database with default languages (English, Romanian, French)
- Set up the required database structure

## 🚀 Quick Start

```php
// Set metadata for different languages
metadata()->lang('en')->set('site.title', 'Welcome to our website');
metadata()->lang('ro')->set('site.title', 'Bine ai venit pe site-ul nostru');
metadata()->lang('fr')->set('site.title', 'Bienvenue sur notre site');

// Get metadata values
$title = metadata()->lang('en')->get('site.title');
$titleWithDefault = metadata()->lang('es')->get('site.title', 'Default Title');

// Work with current app locale
$localizedTitle = metadata()->get('site.title'); // Uses app()->getLocale()
```

## 🔑 Usage

### Setting Metadata Values
```php
// Single value for specific language
metadata()->lang('en')->set('nav.home', 'Home');
metadata()->lang('ro')->set('nav.home', 'Acasă');

// Multiple values at once
metadata()->lang('en')->setMany([
    'nav.home' => 'Home',
    'nav.about' => 'About',
    'nav.contact' => 'Contact',
    'site.description' => 'Best website ever'
]);

metadata()->lang('ro')->setMany([
    'nav.home' => 'Acasă',
    'nav.about' => 'Despre',
    'nav.contact' => 'Contact',
    'site.description' => 'Cel mai bun site'
]);
```

### Getting Metadata Values
```php
// Single value with optional default
$title = metadata()->lang('en')->get('site.title', 'Default Title');

// Multiple values
$navigation = metadata()->lang('en')->getMany([
    'nav.home',
    'nav.about',
    'nav.contact'
]);

// All metadata for a language
$allEnglish = metadata()->lang('en')->all();

// Using current app locale
$localizedContent = metadata()->get('site.welcome_message');
```

### Facade Usage
```php
use Rdcstarr\Multilanguage\Facades\Metadata;

Metadata::lang('en')->set('app.name', 'My App');
Metadata::lang('ro')->set('app.name', 'Aplicația Mea');

$appName = Metadata::lang('en')->get('app.name');
```

### Helper Functions
```php
// Using the global helper
$title = metadata()->lang('en')->get('site.title', 'Default');
$manager = metadata(); // Returns MetadataManager instance

// Direct value access (uses current locale)
$localizedTitle = metadata('site.title', 'Default Title');
```

### Utility Operations
```php
// Check if metadata exists
$exists = metadata()->lang('en')->has('site.title');

// Delete metadata
metadata()->lang('en')->forget('old.unused.key');

// Cache management
metadata()->lang('en')->flushCache();    // Clear cache for specific language
metadata()->flushAllCache();             // Clear cache for all languages
```
---
## � Language Management

### Working with Languages
```php
use Rdcstarr\Multilanguage\Models\Language;

// Create a new language
Language::create([
    'name' => 'Spanish',
    'code' => 'es',
    'flag' => '🇪🇸'
]);

// Get all available languages
$languages = Language::all();

// Find language by code
$english = Language::where('code', 'en')->first();
```

### Language-specific Metadata
```php
// Set up multilingual site content
$languages = ['en', 'ro', 'fr', 'es'];
$content = [
    'en' => ['site.welcome' => 'Welcome!', 'nav.home' => 'Home'],
    'ro' => ['site.welcome' => 'Bine ai venit!', 'nav.home' => 'Acasă'],
    'fr' => ['site.welcome' => 'Bienvenue!', 'nav.home' => 'Accueil'],
    'es' => ['site.welcome' => '¡Bienvenido!', 'nav.home' => 'Inicio']
];

foreach ($languages as $lang) {
    metadata()->lang($lang)->setMany($content[$lang]);
}
```

## 🎨 Blade Integration

```php
{{-- Get metadata for current locale --}}
{{ metadata('site.title', 'Default Title') }}

{{-- Get metadata for specific language --}}
@php
    $title = metadata()->lang('en')->get('site.title', 'Default');
@endphp

{{-- In a loop for multiple languages --}}
@foreach(['en', 'ro', 'fr'] as $lang)
    <h1>{{ metadata()->lang($lang)->get('site.title') }}</h1>
@endforeach
```

## 💡 Real-World Examples

### Website Content Management
```php
// Set up homepage content in multiple languages
metadata()->lang('en')->setMany([
    'home.hero.title' => 'Welcome to Our Platform',
    'home.hero.subtitle' => 'The best solution for your business',
    'home.features.title' => 'Amazing Features'
]);

metadata()->lang('ro')->setMany([
    'home.hero.title' => 'Bine ai venit pe Platforma Noastră',
    'home.hero.subtitle' => 'Cea mai bună soluție pentru afacerea ta',
    'home.features.title' => 'Funcționalități Minunate'
]);

// In your controller
public function index()
{
    $heroTitle = metadata()->get('home.hero.title');
    $heroSubtitle = metadata()->get('home.hero.subtitle');

    return view('home', compact('heroTitle', 'heroSubtitle'));
}
```

### SEO Meta Tags
```php
// Set SEO metadata for different pages and languages
metadata()->lang('en')->setMany([
    'seo.home.title' => 'Home - Best Platform Ever',
    'seo.home.description' => 'Discover our amazing platform features',
    'seo.about.title' => 'About Us - Our Story',
    'seo.about.description' => 'Learn more about our company'
]);

metadata()->lang('ro')->setMany([
    'seo.home.title' => 'Acasă - Cea Mai Bună Platformă',
    'seo.home.description' => 'Descoperă funcționalitățile platformei noastre',
    'seo.about.title' => 'Despre Noi - Povestea Noastră',
    'seo.about.description' => 'Află mai multe despre compania noastră'
]);

// In your layout blade file
<title>{{ metadata()->get('seo.' . request()->route()->getName() . '.title', 'Default Title') }}</title>
<meta name="description" content="{{ metadata()->get('seo.' . request()->route()->getName() . '.description', 'Default description') }}">
```

### User Preferences with Language Support
```php
// Store user-specific settings per language
$userId = auth()->id();
metadata()->lang('en')->setMany([
    "user.{$userId}.dashboard.welcome" => "Welcome back, John!",
    "user.{$userId}.preferences.theme" => "dark"
]);

metadata()->lang('ro')->setMany([
    "user.{$userId}.dashboard.welcome" => "Bine ai revenit, John!",
    "user.{$userId}.preferences.theme" => "dark"
]);
```

### E-commerce Product Information
```php
// Multi-language product descriptions
$productId = 123;
metadata()->lang('en')->setMany([
    "product.{$productId}.name" => "Premium Laptop",
    "product.{$productId}.description" => "High-performance laptop for professionals",
    "product.{$productId}.features" => "Fast processor, 16GB RAM, SSD storage"
]);

metadata()->lang('ro')->setMany([
    "product.{$productId}.name" => "Laptop Premium",
    "product.{$productId}.description" => "Laptop de înaltă performanță pentru profesioniști",
    "product.{$productId}.features" => "Procesor rapid, 16GB RAM, stocare SSD"
]);
```

## 🏗️ Database Structure

### Languages Table
```sql
CREATE TABLE languages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,     -- 'English', 'Română', 'Français'
    code VARCHAR(10) NOT NULL,      -- 'en', 'ro', 'fr'
    flag VARCHAR(10),               -- '🇺🇸', '🇷🇴', '🇫🇷'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_code (code)
);
```

### Metadata Table
```sql
CREATE TABLE metadata (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    language_id BIGINT NOT NULL,
    key VARCHAR(255) NOT NULL,      -- 'site.title', 'nav.home'
    value TEXT,                     -- 'Welcome', 'Home'
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    UNIQUE KEY unique_lang_key (language_id, key)
);
```

## ⚡ Performance Features

### Intelligent Caching
- **Per-language caching**: Each language has its own cache key
- **Automatic cache invalidation**: Cache is cleared when metadata is updated
- **Memory efficient**: Only requested languages are loaded into cache
- **Forever cache**: Uses Laravel's `rememberForever` for optimal performance

### Optimized Queries
- **Eager loading**: Relationships are optimized to prevent N+1 queries
- **Scoped queries**: Database queries are scoped by language code
- **Batch operations**: Multiple metadata values can be set in single transaction

## 🔧 Configuration

The package uses Laravel's default locale (`app()->getLocale()`) as the fallback language. You can customize this behavior by setting the language explicitly:

```php
// Use specific language
$title = metadata()->lang('en')->get('site.title');

// Use app's current locale
$title = metadata()->get('site.title');

// Set app locale and use it
app()->setLocale('ro');
$title = metadata()->get('site.title'); // Will use 'ro'
```

## 📖 Resources
 - [API Guide](METADATA_API_GUIDE.md) - Detailed API documentation and examples
 - [Changelog](CHANGELOG.md) - Version history and changes
 - [Contributing](CONTRIBUTING.md) - How to contribute to the project
 - [Security Vulnerabilities](../../security/policy) - Security policy and reporting

## 👥 Credits
 - [Rdcstarr](https://github.com/rdcstarr) - Package author and maintainer

## 📜 License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🧪 Testing
```bash
composer test
```

## 📁 Project Structure
```
src/
├── Commands/
│   └── InstallCommand.php          # Installation command
├── Facades/
│   └── Metadata.php               # Metadata facade
├── Models/
│   ├── Language.php               # Language model
│   └── Metadata.php               # Metadata model
├── helpers.php                    # Global helper functions
├── MetadataManager.php            # Core metadata manager
└── MultilanguageServiceProvider.php # Service provider

database/
├── migrations/
│   └── create_multilanguage_table.php # Database migrations
└── seeders/
    ├── languages.json             # Default languages data
    └── LanguagesSeeder.php        # Languages seeder
```
