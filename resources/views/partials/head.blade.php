<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - MyTasks' : 'MyTasks' }}
</title>

<link rel="icon" href="/favicon.png" sizes="any">
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/favicon.png">

<!-- Primary SEO Meta Tags -->
<meta name="description" content="{{ $description ?? 'A modern and secure task tracking app.' }}" />
<meta name="keywords" content="{{ $keywords ?? '' }}" />
<meta name="author" content="{{ $author ?? 'MyTasks' }}" />
<link rel="canonical" href="{{ $canonical ?? url()->current() }}" />

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $og_type ?? 'website' }}" />
<meta property="og:title" content="{{ filled($title ?? null) ? $title.' - MyTasks' : 'MyTasks' }}" />
<meta property="og:description" content="{{ $description ?? 'A modern and secure task tracking app.' }}" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:site_name" content="MyTasks" />
<meta property="og:image" content="{{ $og_image ?? asset('og.png') }}" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ filled($title ?? null) ? $title.' - MyTasks' : 'MyTasks' }}" />
<meta name="twitter:description" content="{{ $description ?? 'A modern and secure task tracking app.' }}" />
<meta name="twitter:image" content="{{ $og_image ?? asset('og.png') }}" />

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
