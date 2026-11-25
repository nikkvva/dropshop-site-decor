{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <url><loc>{{ route('home') }}</loc></url>
   <url><loc>{{ route('catalog') }}</loc></url>
@foreach ($products as $product)
   <url><loc>{{ route('product', $product) }}</loc><lastmod>{{ $product->updated_at?->toDateString() }}</lastmod></url>
@endforeach
</urlset>
