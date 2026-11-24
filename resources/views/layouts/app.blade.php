<!doctype html>
<html lang="uk">
<head>
   <meta charset="utf-8">
   <title>@yield('title', config('app.name'))</title>
   <meta name="description" content="@yield('description')">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="icon" type="image/png" href="{{ asset('bazaro/img/logo/favicon.png') }}">

   @foreach (['bootstrap', 'animate', 'nice-select', 'custom-animation', 'swiper-bundle', 'magnific-popup', 'font-awesome-pro', 'spacing', 'main'] as $css)
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

   @foreach (['vendor/jquery', 'bootstrap-bundle', 'swiper-bundle', 'magnific-popup', 'nice-select', 'purecounter', 'isotope-pkgd', 'lazysize.min', 'slider-active', 'imagesloaded-pkgd', 'main'] as $js)
      <script src="{{ asset("bazaro/js/$js.js") }}"></script>
   @endforeach
</body>
</html>
