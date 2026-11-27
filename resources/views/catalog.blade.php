@extends('layouts.app')

@section('title', config('niche.catalog_title').' — '.config('app.name'))
@section('description', config('niche.catalog_description'))
{{-- сортировка — та же страница, поэтому в canonical её нет; номер страницы — есть --}}
@section('canonical', route('catalog', $products->currentPage() > 1 ? ['page' => $products->currentPage()] : []))

@section('content')

   <div class="aq-breadcrumb-area pt-20 pb-30" data-bg-color="#F0EFED">
      <div class="container">
         <div class="aq-breadcrumb-wrap">
            <div class="aq-breadcrumb-list mb-40">
               <span><a href="{{ route('home') }}">Головна</a></span>
               <span>/</span>
               <span>Каталог</span>
            </div>
            <div class="aq-breadcrumb-content">
               <h1 class="aq-breadcrumb-title">Каталог</h1>
               <p>{{ config('niche.tagline') }}</p>
            </div>
         </div>
      </div>
   </div>

   <div class="aq-product-area pt-60 pb-80">
      <div class="container">
         <div class="aq-product-sidebar-top pb-10">
            <div class="row align-items-center">
               <div class="col-md-6">
                  <div class="aq-product-sidebar-text mb-20">
                     <p class="mb-0">Товарів: {{ $products->total() }}</p>
                  </div>
               </div>
               <div class="col-md-6">
                  {{-- без JS: при выборе форма сама отправляется, ?sort=... --}}
                  <form class="aq-product-sidebar-right justify-content-md-end mb-20" method="get" action="{{ route('catalog') }}">
                     <p>Сортувати:</p>
                     <div class="aqf-bundle-select aq-select">
                        <select name="sort" onchange="this.form.submit()" aria-label="Сортування">
                           @foreach (\App\Http\Controllers\CatalogController::SORTS as $value => $label)
                              <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                           @endforeach
                        </select>
                     </div>
                  </form>
               </div>
            </div>
         </div>

         @if ($products->isEmpty())
            <p class="text-center py-5">Товарів поки немає. Зазирніть трохи пізніше.</p>
         @else
            <div class="row gx-15 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
               @foreach ($products as $product)
                  <div class="col">@include('partials.product-card')</div>
               @endforeach
            </div>
            <div class="mt-30">{{ $products->onEachSide(1)->links('partials.pagination') }}</div>
         @endif
      </div>
   </div>

@endsection
