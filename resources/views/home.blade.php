{{-- Свой вариант главной (index-furniture.html Bazaro): при git pull upstream конфликт здесь — оставлять свою версию --}}
@extends('layouts.app')

@section('title', config('app.name').' — '.config('niche.home_title'))
@section('description', config('niche.home_description'))

@php
   // Тексты — config/niche.php. Картинки слайдера и баннеров меняются в админке («Картинки сайта»), здесь — заглушки шаблона
   $slidePlaceholders = [['slide-1', 'slide-1'], ['slide-2', 'slide-2-1'], ['slide-3', 'slide-3-1']];
   $slides = collect(config('niche.slides'))->map(fn ($s, $i) => $s + ['img' => $slidePlaceholders[$i][0], 'mobile' => $slidePlaceholders[$i][1]]);
   $banners = collect(config('niche.banners'))->map(fn ($b, $i) => [['banner-3-2', 'banner-3-1'][$i], $b['label'], $b['title']]);
   $features = [
      ['Доставка по Україні', 'Відправка за 1–3 робочі дні','<path d="M7.60001 13.6001V0.600098H22.5999V13.6001H7.60001ZM7.60001 13.6001L0.599976 13.6001V8.60011L3.59996 5.6001H7.59995L7.60001 13.6001ZM15.6 16.1001C15.6 17.4808 16.7193 18.6001 18.1 18.6001C19.4807 18.6001 20.5999 17.4808 20.5999 16.1001C20.5999 14.7194 19.4807 13.6001 18.1 13.6001C16.7193 13.6001 15.6 14.7194 15.6 16.1001ZM2.59997 16.1001C2.59997 17.4808 3.71925 18.6001 5.09996 18.6001C6.48066 18.6001 7.59995 17.4808 7.59995 16.1001C7.59995 14.7194 6.48066 13.6001 5.09996 13.6001C3.71925 13.6001 2.59997 14.7194 2.59997 16.1001Z" stroke="currentcolor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>', '0 0 24 20'],
      ['Оплата при отриманні', 'Без передоплати', '<path d="M7.24487 0.700195V24.7002M12.7 5.06348H4.51813C3.50549 5.06348 2.53432 5.46575 1.81827 6.1818C1.10222 6.89784 0.699951 7.86901 0.699951 8.88166C0.699951 9.8943 1.10222 10.8655 1.81827 11.5815C2.53432 12.2976 3.50549 12.6998 4.51813 12.6998H9.97268C10.9853 12.6998 11.9565 13.1021 12.6725 13.8182C13.3886 14.5342 13.7909 15.5054 13.7909 16.518C13.7909 17.5307 13.3886 18.5018 12.6725 19.2179C11.9565 19.9339 10.9853 20.3362 9.97268 20.3362H0.699951" stroke="currentcolor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>', '0 0 15 26'],
      ['Обмін і повернення', '14 днів, якщо товар не використовувався','<path d="M2.52966 14.4778L14.4797 2.52783M9.70154 16.8772L10.9015 15.6772M12.3929 14.1874L14.7829 11.7974M0.599976 20.5972H20.6M2.20125 8.83763L8.84125 2.19763C10.9612 0.0776333 12.0212 0.0676333 14.1212 2.16763L19.0312 7.07763C21.1312 9.17763 21.1212 10.2376 19.0012 12.3576L12.3612 18.9976C10.2412 21.1176 9.18124 21.1276 7.08124 19.0276L2.17125 14.1176C0.0712457 12.0176 0.0712454 10.9676 2.20125 8.83763Z" stroke="currentcolor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>', '0 0 22 22'],
      ['Консультація', 'Допоможемо з вибором','<path d="M0.599976 15.6V9.60003C0.599976 7.2131 1.54817 4.92393 3.23597 3.23612C4.92378 1.5483 7.21293 0.600098 9.59984 0.600098C11.9868 0.600098 14.2759 1.5483 15.9637 3.23612C17.6515 4.92393 18.5997 7.2131 18.5997 9.60003V15.6M18.5997 16.6001C18.5997 17.1305 18.389 17.6392 18.0139 18.0143C17.6389 18.3893 17.1302 18.6 16.5997 18.6H15.5997C15.0693 18.6 14.5606 18.3893 14.1856 18.0143C13.8105 17.6392 13.5998 17.1305 13.5998 16.6001V13.6001C13.5998 13.0697 13.8105 12.5609 14.1856 12.1859C14.5606 11.8108 15.0693 11.6001 15.5997 11.6001H18.5997V16.6001ZM0.599976 16.6001C0.599976 17.1305 0.810686 17.6392 1.18575 18.0143C1.56082 18.3893 2.06952 18.6 2.59995 18.6H3.59993C4.13035 18.6 4.63905 18.3893 5.01412 18.0143C5.38919 17.6392 5.5999 17.1305 5.5999 16.6001V13.6001C5.5999 13.0697 5.38919 12.5609 5.01412 12.1859C4.63905 11.8108 4.13035 11.6001 3.59993 11.6001H0.599976V16.6001Z" stroke="currentcolor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>', '0 0 20 20'],
   ];
   $arrow = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M0.75 5.75H10.75M10.75 5.75L5.75 0.75M10.75 5.75L5.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

@section('content')

   <h1 class="visually-hidden">{{ config('niche.tagline') }}</h1>

   {{-- Hero: в шаблоне видео, у нас — 3 слайда с картинками из админки --}}
   <div class="swiper dcr-hero-active">
      <div class="swiper-wrapper">
         @foreach ($slides as $i => $slide)
            <div class="swiper-slide">
               <div class="fr-hero-area fr-hero-overlay p-relative fix">
                  <img class="fr-hero-video d-none d-md-block" src="{{ \App\Models\Setting::image('slide_'.($i + 1), "bazaro/img/slider/{$slide['img']}.jpg") }}" alt="">
                  <img class="fr-hero-video d-md-none" src="{{ \App\Models\Setting::image('slide_'.($i + 1).'_mobile', "bazaro/img/slider/{$slide['mobile']}.jpg") }}" alt="">
                  <div class="container container-1830">
                     <div class="row">
                        <div class="offset-xl-1 col-xl-11">
                           <div class="fr-hero-info text-center p-relative z-index-2" data-bg-color="#F7F2ED">
                              <span class="aq-section-subtitle ff-satoshi-reg mb-15 d-inline-block">{{ $slide['label'] }}</span>
                              <h3 class="aq-section-title ff-satoshi-med fs-50 mb-20">{!! $slide['title'] !!}</h3>
                              <p>{{ $slide['note'] }}</p>
                              <a class="aq-btn-black btn-coffee-bg radius-30 w-100" href="{{ route('catalog') }}">До каталогу</a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         @endforeach
      </div>
      <div class="swiper-pagination dcr-hero-pagination"></div>
   </div>

   {{-- Преимущества --}}
   <div id="delivery" class="fr-feature-area">
      <div class="container container-1830">
         <div class="fr-feature-wrap d-flex justify-content-between align-items-center">
            @foreach ($features as [$title, $text, $path, $viewBox])
               <div class="fr-feature-item" title="{{ $text }}">
                  <i class="fr-feature-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="{{ $viewBox }}" fill="none">{!! $path !!}</svg></i>
                  <span>{{ $title }}</span>
               </div>
            @endforeach
         </div>
      </div>
   </div>

   {{-- Баннеры --}}
   <div class="fr-project-area pt-90">
      <div class="container container-1830">
         <div class="row">
            @foreach ($banners as $i => [$img, $label, $title])
               <div class="col-md-6">
                  <div class="fr-project-item p-relative mb-30">
                     <div class="fr-project-thumb">
                        <img class="w-100" src="{{ \App\Models\Setting::image('banner_'.($i + 1), "bazaro/img/banner/$img.jpg") }}" alt="">
                     </div>
                     <div class="fr-project-content text-center">
                        <span>{{ $label }}</span>
                        <h4 class="aq-section-title ff-satoshi-med fs-32"><a href="{{ route('catalog') }}">{{ $title }}</a></h4>
                        <a class="fr-project-link aq-btn-underline" href="{{ route('catalog') }}">Дивитися <span>{!! $arrow !!}</span></a>
                     </div>
                  </div>
               </div>
            @endforeach
         </div>
      </div>
   </div>

   {{-- Новинки --}}
   <div class="fr-product-area csm-product-style pt-90 pb-90">
      <div class="container container-1830">
         <div class="fr-product-top mb-50">
            <div class="row align-items-end">
               <div class="col-md-8">
                  <div class="fr-product-title-box text-center text-md-start">
                     <h2 class="aq-section-title ff-satoshi-med fs-50">Новинки</h2>
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="text-center text-md-end mt-20 mt-md-0">
                     <a class="aq-btn-link aq-btn-underline" href="{{ route('catalog') }}">Усі товари <span>{!! $arrow !!}</span></a>
                  </div>
               </div>
            </div>
         </div>
         <div class="row gx-15 row-cols-xl-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
            @foreach ($arrivals as $product)
               <div class="col">@include('partials.product-card')</div>
            @endforeach
         </div>
      </div>
   </div>

   {{-- Один товар крупно --}}
   @if ($featured)
      <div class="elt-product-watch-area mb-120">
         <div class="container container-1830">
            <div class="elt-product-watch-wrap fix" data-bg-color="#EAE2D3">
               <div class="row justify-content-center align-items-center">
                  <div class="col-xxl-7 col-lg-6">
                     <div class="elt-product-watch-thumb text-center p-4">
                        <img src="{{ $featured->image() }}" alt="{{ $featured->name }}" style="max-height: 480px">
                     </div>
                  </div>
                  <div class="col-xxl-5 col-lg-6">
                     <div class="aq-product-details-wrap pt-80 pb-80 pr-105">
                        @if ($featured->vendor)
                           <div class="aq-product-details-category"><span>{{ $featured->vendor }}</span></div>
                        @endif
                        <h3 class="aq-product-details-title mb-10">{{ $featured->name }}</h3>
                        <div class="aq-product-details-price-wrap mb-30">
                           <ins><span class="aq-product-details-price new-price">{{ $featured::money($featured->price) }}</span></ins>
                        </div>
                        <a class="aq-btn-black btn-coffee-bg radius-30" href="{{ route('product', $featured) }}">Детальніше</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   @endif

@endsection

{{-- Стили только этой главной — здесь, а не в site.css (тот общий с заготовкой) --}}
@push('head')
   <style>
      @media (max-width: 767px) { .fr-feature-item { width: 100%; } }
   </style>
@endpush

@push('scripts')
   <script>
      new Swiper('.dcr-hero-active', {
         effect: 'fade', fadeEffect: { crossFade: true }, rewind: true, speed: 800,
         autoplay: { delay: 6000 },
         pagination: { el: '.dcr-hero-pagination', clickable: true },
      });
   </script>
@endpush
