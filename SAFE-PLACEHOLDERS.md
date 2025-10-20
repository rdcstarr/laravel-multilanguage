# Safe Placeholder Usage with User Input

## Problema

Când folosești placeholders cu date de la utilizatori, există riscul ca utilizatorii să introducă text care conține sintaxa placeholder-urilor (`:year`, `:app_name`, etc.), ceea ce ar fi înlocuit automat.

**Exemplu problematic:**

```php
// Utilizatorul caută ":year"
$query = ':year';

// Traducere
_ld()->set('search.meta_title', 'Search results for: :query');

// Folosire
$title = _ld('search.meta_title')->placeholders(['query' => $query]);
// Rezultat: "Search results for: 2025" ❌ (GREȘIT! :year din query a fost înlocuit)
```

## Soluția: Escape User Input

### Metoda 1: Folosind `escape_placeholders()` helper

```php
// În Controller
public function search(Request $request)
{
    $query = $request->input('q');

    // ESCAPE query-ul utilizatorului
    $safeQuery = escape_placeholders($query);

    $title = _ld('search.meta_title')->placeholders([
        'query' => $safeQuery
    ]);

    return view('search.results', compact('title', 'query'));
}
```

**Exemplu:**

```php
$userInput = ':year in review';
$safe = escape_placeholders($userInput);

$title = _ld('search.meta_title')->placeholders(['query' => $safe]);
// Rezultat: "Search results for: :year in review" ✅ (CORECT!)
```

### Metoda 2: Folosind metoda statică

```php
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;

$safeValue = LocaleDataPlaceholders::escape($userInput);
```

### Metoda 3: Folosind Facade

```php
use Rdcstarr\Multilanguage\Facades\Placeholders;

$safeValue = Placeholders::escape($userInput);
```

## Exemple Practice

### 1. Search Results

```php
// routes/web.php
Route::get('/search', function (Request $request) {
    $query = $request->input('q');

    // Traducere în DB
    // 'search.meta_title' => 'Search results for: :query - :app_name'

    $metaTitle = _ld('search.meta_title')->placeholders([
        'query' => escape_placeholders($query), // Escape user input!
        // :app_name va fi înlocuit cu numele aplicației (safe)
    ]);

    return view('search', compact('metaTitle'));
});
```

**Test:**

-   User caută `"laravel"` → `"Search results for: laravel - My App"` ✅
-   User caută `":year"` → `"Search results for: :year - My App"` ✅
-   User caută `":app_name"` → `"Search results for: :app_name - My App"` ✅

### 2. User Profile

```php
// Controller
public function show(User $user)
{
    // 'profile.meta_title' => ':username\'s Profile | :app_name - :year'

    $metaTitle = _ld('profile.meta_title')->placeholders([
        'username' => escape_placeholders($user->name), // Escape username!
        // :app_name și :year sunt safe (default placeholders)
    ]);

    return view('profile.show', compact('user', 'metaTitle'));
}
```

**Test:**

-   Username: `"John Doe"` → `"John Doe's Profile | My App - 2025"` ✅
-   Username: `":admin:year"` → `":admin:year's Profile | My App - 2025"` ✅

### 3. Blog Post Title

```php
// Controller
public function show(Post $post)
{
    // 'blog.meta_title' => ':title | Posted on :date | :app_name'

    $metaTitle = _ld('blog.meta_title')->placeholders([
        'title' => escape_placeholders($post->title), // Escape post title!
        // :date și :app_name sunt safe
    ]);

    return view('blog.show', compact('post', 'metaTitle'));
}
```

### 4. Comment System

```php
// Controller
public function store(Request $request, Post $post)
{
    $comment = $post->comments()->create([
        'content' => $request->content,
        'user_id' => auth()->id(),
    ]);

    // 'comment.notification' => ':username commented: :comment_preview on :date'

    $notification = _ld('comment.notification')->placeholders([
        'username' => escape_placeholders(auth()->user()->name),
        'comment_preview' => escape_placeholders(Str::limit($comment->content, 50)),
        // :date este safe (default placeholder)
    ]);

    // Send notification...
}
```

### 5. Email Subject

```php
// Mailable
public function build()
{
    $userName = escape_placeholders($this->user->name);

    // 'email.welcome.subject' => 'Welcome :username! Your account on :app_name'

    return $this->subject(
        _ld('email.welcome.subject')->placeholders([
            'username' => $userName,
        ])
    );
}
```

## Când să folosești Escape

### ✅ ÎNTOTDEAUNA escape pentru:

-   Input-uri de la utilizatori (search queries, comments, usernames)
-   Titluri de articole/posturi create de utilizatori
-   Descrieri create de utilizatori
-   Orice date user-generated care ajung în placeholders

### ❌ NU trebuie să faci escape pentru:

-   Valori din baza de date controlate de admin
-   Constante din cod
-   Valori calculate (counters, dates generate de sistem)
-   Default placeholders (`:year`, `:app_name`, etc.)

## Unescape

Dacă trebuie să afișezi valoarea originală (cu `:` înapoi):

```php
$escaped = escape_placeholders(':year in review');
// $escaped = '&#58;year in review'

$original = unescape_placeholders($escaped);
// $original = ':year in review'
```

## Best Practices

### 1. Escape la nivel de Controller (recomandat)

```php
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = escape_placeholders($request->input('q'));

        $title = _ld('search.title')->placeholders(['query' => $query]);

        return view('search.index', compact('title'));
    }
}
```

### 2. Escape în Blade (pentru cazuri simple)

```php
@php
    $safeQuery = escape_placeholders($query);
@endphp

<title>{{ _ld('search.title')->placeholders(['query' => $safeQuery]) }}</title>
```

### 3. Custom Request cu Escape Automat

```php
class SearchRequest extends FormRequest
{
    public function getSafeQuery(): string
    {
        return escape_placeholders($this->input('q'));
    }
}

// În Controller
public function index(SearchRequest $request)
{
    $title = _ld('search.title')->placeholders([
        'query' => $request->getSafeQuery()
    ]);
}
```

### 4. Accessor în Model

```php
class User extends Model
{
    public function getSafeNameAttribute(): string
    {
        return escape_placeholders($this->name);
    }
}

// Folosire
$title = _ld('profile.title')->placeholders([
    'username' => $user->safe_name
]);
```

## Performanță

Funcția `escape_placeholders()` este foarte rapidă (doar un `str_replace`), deci nu există overhead semnificativ.

```php
// Micro-benchmark
$iterations = 100000;
$start = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    escape_placeholders(':year some text :app_name');
}

$end = microtime(true);
echo "Time: " . ($end - $start) . " seconds"; // ~0.02 seconds pentru 100k
```

## Alternative

Dacă vrei să dezactivezi complet placeholder-urile pentru anumite valori:

```php
// Folosește ->raw() pentru a obține valoarea fără niciun placeholder
$rawValue = _ld('some.key')->raw();

// Apoi adaugă manual user input
$final = str_replace(':query', $userQuery, $rawValue);
```

## Rezumat

🔐 **Golden Rule:**

> Orice valoare care vine de la utilizatori trebuie escapată înainte de a fi folosită în placeholders!

```php
// ❌ GREȘIT
$title = _ld('page.title')->placeholders(['query' => $userInput]);

// ✅ CORECT
$title = _ld('page.title')->placeholders(['query' => escape_placeholders($userInput)]);
```
