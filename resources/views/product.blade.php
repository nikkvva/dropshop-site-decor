@extends('layouts.app')

@section('title', $product->name.' — купити за '.$product::money($product->price).' | '.config('app.name'))
@section('description', $product->metaDescription())
@section('og_type', 'product')
@if ($product->image())
   @section('og_image', $product->image())
@endif

{{-- Разметка для Google: цена и наличие прямо в выдаче --}}
@php
   $jsonLd = [
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $product->name,
      'image' => $product->pictures ?? [],
      'description' => $product->metaDescription(),
      'sku' => $product->vendor_code,
      'brand' => $product->vendor ? ['@type' => 'Brand', 'name' => $product->vendor] : null,
      'offers' => [
         '@type' => 'Offer',
         'url' => route('product', $product),
         'priceCurrency' => 'UAH',
         'price' => $product->price,
         'availability' => $product->is_available ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
         'itemCondition' => 'https://schema.org/NewCondition',
      ],
   ];
@endphp
@push('head')
   <script type="application/ld+json">@json(array_filter($jsonLd))</script>
@endpush

@section('content')

   <div class="pd-breadcrumb-area">
      <div class="container">
         <div class="pd-breadcrumb-content">
            <div class="pd-breadcrumb-list">
               <span><a href="{{ route('home') }}">Головна</a></span>
               <span>/</span>
               <span><a href="{{ route('catalog') }}">Каталог</a></span>
               <span>/</span>
               <span>{{ $product->name }}</span>
            </div>
         </div>
      </div>
   </div>

   <div class="aq-product-details-thumbnails-style pb-80">
      <div class="container">
         <div class="row">
            {{-- Галерея: slider-active.js включает карусель (slick), клик — фото крупно --}}
            <div class="col-lg-6">
               @if ($product->pictures)
                  <div class="product-details-slider-wrap aq-sticky-on">
                     <div class="row">
                        <div class="col-xl-2">
                           <div class="product-slider-nav slider-nav">
                              @foreach ($product->pictures as $picture)
                                 <div class="product-slider-nav-img"><img src="{{ $picture }}" alt=""></div>
                              @endforeach
                           </div>
                        </div>
                        <div class="col-xl-10">
                           <div class="product-slider-main-wrap p-relative">
                              <div class="product-slider-main slider-for p-relative">
                                 @foreach ($product->pictures as $picture)
                                    <div class="product-slider-for-img">
                                       <a class="popup-image" href="{{ $picture }}">
                                          <img src="{{ $picture }}" alt="{{ $product->name }}{{ $loop->first ? '' : ' — фото '.$loop->iteration }}">
                                       </a>
                                    </div>
                                 @endforeach
                              </div>
                              <div class="product-slider-arrow"></div>
                           </div>
                        </div>
                     </div>
                  </div>
               @endif
            </div>

            <div class="col-lg-6">
               <div class="aq-product-details-wrap pt-25 ml-30">
                  <div class="aq-product-details-radius-style d-flex">
                     <div class="aq-product-sucess mb-15">
                        @if ($product->is_available)
                           <span class="clr-sale">В наявності</span>
                        @else
                           <span class="site-out-of-stock">Немає в наявності</span>
                        @endif
                     </div>
                  </div>
                  @if ($product->vendor)
                     <div class="aq-product-details-category"><span>{{ $product->vendor }}</span></div>
                  @endif
                  <h1 class="aq-product-details-title mb-10">{{ $product->name }}</h1>
                  @if ($product->vendor_code)
                     <p class="site-sku mb-15">Артикул: {{ $product->vendor_code }}</p>
                  @endif

                  <div class="aq-product-details-price-wrap mb-25">
                     <ins><span class="aq-product-details-price new-price">{{ $product::money($product->price) }}</span></ins>
                     @if ($product->discount())
                        <del class="ms-2"><span class="aq-product-details-price old-price">{{ $product::money($product->old_price) }}</span></del>
                     @endif
                  </div>

                  @if ($variants->isNotEmpty())
                     <div class="site-variants mb-25">
                        <p class="mb-10">Інші варіанти:</p>
                        <div class="d-flex flex-wrap gap-2">
                           @foreach ($variants as $variant)
                              <a href="{{ route('product', $variant) }}" title="{{ $variant->name }}">
                                 <img src="{{ $variant->image() }}" alt="{{ $variant->name }}" loading="lazy">
                              </a>
                           @endforeach
                        </div>
                     </div>
                  @endif

                  {{-- Корзина — шаг 4.4: пока кнопка неактивна --}}
                  <div class="aq-product-details-action-wrapper mb-25">
                     <div class="aq-product-details-action-item-wrapper d-sm-flex align-items-center">
                        <div class="aq-product-details-quantity">
                           <div class="aq-product-quantity mb-10 mr-10">
                              <span class="aq-cart-minus"><svg width="11" height="2" viewBox="0 0 11 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1H10" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"/></svg></span>
                              <input class="aq-cart-input" type="text" value="1" aria-label="Кількість">
                              <span class="aq-cart-plus"><svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 6H10M5.5 1.5V10.5" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round"/></svg></span>
                           </div>
                        </div>
                        <div class="aq-product-details-add-to-cart-btn w-100 mb-10">
                           <button class="aq-btn-black btn-square w-100" disabled>{{ $product->is_available ? 'До кошика' : 'Немає в наявності' }}</button>
                        </div>
                     </div>
                  </div>

                  <div class="aq-product-details-mesg-wrap">
                     <ul>
                        <li><div class="aq-product-details-mesg"><span><label>Доставка:</label> Новою поштою по всій Україні, відправка за 1–3 робочі дні.</span></div></li>
                        <li><div class="aq-product-details-mesg"><span><label>Оплата:</label> при отриманні, без передоплати.</span></div></li>
                        <li><div class="aq-product-details-mesg"><span><label>Повернення:</label> протягом 14 днів, якщо товар не використовувався.</span></div></li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   {{-- Описание и характеристики --}}
   <div class="product-details-tab pb-80">
      <div class="container">
         <div class="product-details-tab-nav">
            <nav>
               <div class="product-details-nav nav nav-tab justify-content-center p-relative" role="tablist">
                  <button class="nav-links active" id="nav-description-tab" data-bs-toggle="tab" data-bs-target="#nav-description" type="button" role="tab" aria-controls="nav-description" aria-selected="true">Опис</button>
                  <button class="nav-links" id="nav-params-tab" data-bs-toggle="tab" data-bs-target="#nav-params" type="button" role="tab" aria-controls="nav-params" aria-selected="false">Характеристики</button>
               </div>
            </nav>
            <div class="tab-content">
               <div class="tab-pane fade show active" id="nav-description" role="tabpanel" aria-labelledby="nav-description-tab" tabindex="0">
                  <div class="product-details-tab-item">
                     <div class="product-details-desc-wrap site-description">
                        {!! $product->safeDescription() !!}
                     </div>
                  </div>
               </div>
               <div class="tab-pane fade" id="nav-params" role="tabpanel" aria-labelledby="nav-params-tab" tabindex="0">
                  <div class="product-details-tab-item">
                     <div class="product-details-additional-info">
                        <table>
                           <tbody>
                              @if ($product->vendor)
                                 <tr><td>Бренд</td><td>{{ $product->vendor }}</td></tr>
                              @endif
                              @foreach ($product->params ?? [] as $name => $value)
                                 <tr><td>{{ $name }}</td><td>{{ $value }}</td></tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   @if ($related->isNotEmpty())
      <div class="pb-100">
         <div class="container">
            <h2 class="aq-section-title ff-onest-bold fs-36 text-center mb-35">Схожі товари</h2>
            <div class="row gx-15 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
               @foreach ($related as $item)
                  <div class="col">@include('partials.product-card', ['product' => $item])</div>
               @endforeach
            </div>
         </div>
      </div>
   @endif

@endsection
