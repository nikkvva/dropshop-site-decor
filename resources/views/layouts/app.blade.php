<!doctype html>
<html lang="uk">
<head>
   <meta charset="utf-8">
   <title>@yield('title', config('app.name'))</title>
   <meta name="description" content="@yield('description')">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="icon" type="image/png" href="{{ asset('bazaro/img/logo/favicon.png') }}">

   {{-- SEO: главный адрес страницы (без ?sort=...) и превью ссылки в Telegram/Viber --}}
   <link rel="canonical" href="@yield('canonical', url()->current())">
   <meta property="og:type" content="@yield('og_type', 'website')">
   <meta property="og:site_name" content="{{ config('app.name') }}">
   <meta property="og:title" content="@yield('title', config('app.name'))">
   <meta property="og:description" content="@yield('description')">
   <meta property="og:url" content="@yield('canonical', url()->current())">
   <meta property="og:image" content="@yield('og_image', asset('bazaro/img/logo/logo.png'))">
   @stack('head')

   @foreach (['bootstrap', 'animate', 'nice-select', 'custom-animation', 'swiper-bundle', 'slick', 'magnific-popup', 'font-awesome-pro', 'spacing', 'main'] as $css)
      <link rel="stylesheet" href="{{ asset("bazaro/css/$css.css") }}">
   @endforeach
   <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>

   @include('partials.offcanvas')
   @include('partials.header')

   <main>
      @yield('content')
   </main>

   @include('partials.footer')

   @foreach (['vendor/jquery', 'bootstrap-bundle', 'swiper-bundle', 'slick.min', 'magnific-popup', 'nice-select', 'purecounter', 'isotope-pkgd', 'lazysize.min', 'slider-active', 'imagesloaded-pkgd', 'main'] as $js)
      <script src="{{ asset("bazaro/js/$js.js") }}"></script>
   @endforeach
   @stack('scripts')
</body>
</html>
