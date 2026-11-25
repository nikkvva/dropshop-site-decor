@php
   $notices = [
      'Доставка Новою поштою по всій Україні',
      'Оплата при отриманні — без передоплати',
      'Обмін і повернення протягом 14 днів',
   ];
   $menu = [
      'Головна' => route('home'),
      'Каталог' => route('catalog'),
      'Доставка і оплата' => route('home').'#delivery',
      'Контакти' => '#contacts',
   ];
@endphp

<header>
   <div class="aq-header-top-area aq-header-top-style-4" data-bg-color="#E1EEB7">
      <div class="container container-1890">
         <div class="row justify-content-center">
            <div class="col-xxl-5 col-xl-8">
               <div class="aq-header-discount d-flex align-items-center">
                  <div class="aq-header-3-arrow">
                     <button class="aq-header-prev" aria-label="Назад">
                        <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none"><path d="M4.75 8.75L0.75 4.75L4.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                     </button>
                  </div>
                  <div class="swiper aq-header-discount-active">
                     <div class="swiper-wrapper">
                        @foreach ($notices as $notice)
                           <div class="swiper-slide">
                              <div class="aq-header-discount-item text-center"><p>{{ $notice }}</p></div>
                           </div>
                        @endforeach
                     </div>
                  </div>
                  <div class="aq-header-3-arrow">
                     <button class="aq-header-next" aria-label="Далі">
                        <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none"><path d="M0.75 8.75L4.75 4.75L0.75 0.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="aq-header-area p-relative">
      <div class="container">
         <div class="row align-items-center">
            <div class="col-4 d-xl-none">
               <div class="aq-header-bar-wrap">
                  <button class="aq-header-bar aq-offcanvas-toggle" aria-label="Меню">
                     <span></span><span></span><span></span>
                  </button>
               </div>
            </div>
            <div class="col-xl-2 col-4">
               <div class="aq-header-logo text-center text-xl-start">
                  <a href="{{ route('home') }}"><img data-width="120" src="{{ asset('bazaro/img/logo/logo.png') }}" alt="{{ config('app.name') }}"></a>
               </div>
            </div>
            <div class="col-xl-7 d-none d-xl-block">
               <div class="aq-header-menu menu-height-62 aq-header-dropdown menu-black text-center">
                  <nav class="aq-mobile-menu-active">
                     <ul>
                        @foreach ($menu as $title => $url)
                           <li><a href="{{ $url }}">{{ $title }}</a></li>
                        @endforeach
                     </ul>
                  </nav>
               </div>
            </div>
            <div class="col-xl-3 col-4">
               <div class="aq-header-right-options text-end">
                  <ul>
                     @php($cartCount = app(\App\Services\Cart::class)->count())
                     <li class="aq-header-top-cart">
                        <a href="{{ route('cart') }}" aria-label="Кошик">
                           @if ($cartCount)
                              <span class="count-box">{{ $cartCount }}</span>
                           @endif
                           <i>
                              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none"><path d="M5.48681 5.07041C5.48681 2.68433 7.4211 0.750039 9.80717 0.750039C10.9562 0.74517 12.0598 1.1982 12.874 2.00895C13.6882 2.81971 14.1459 3.92139 14.1458 5.07041M6.84107 9.57384H6.88684M12.6721 9.57388H12.7179M5.62368 19.972H13.9715C17.0379 19.972 19.3903 18.8645 18.7221 14.4068L17.944 8.3656C17.5321 6.14134 16.1134 5.29008 14.8685 5.29008H4.69004C3.42688 5.29008 2.0905 6.20542 1.61453 8.3656L0.836493 14.4068C0.268988 18.361 2.55732 19.972 5.62368 19.972Z" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                           </i>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
   </div>
</header>
