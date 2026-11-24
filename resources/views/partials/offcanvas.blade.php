{{-- Мобильное меню: main.js сам копирует сюда пункты из шапки (.aq-mobile-menu-active) --}}
<div class="aq-offcanvas-wrap">
   <div class="aq-offcanvas-top d-flex align-items-center justify-content-between">
      <div class="aq-offcanvas-logo">
         <a href="{{ route('home') }}"><img width="115" src="{{ asset('bazaro/img/logo/logo.png') }}" alt="{{ config('app.name') }}"></a>
      </div>
      <div class="aq-offcanvas-close">
         <span>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M10.75 0.75L0.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M0.75 0.75L10.75 10.75" stroke="currentcolor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
         </span>
      </div>
   </div>
   <div class="aq-offcanvas-menu-wrap">
      <div class="aq-offcanvas-menu">
         <nav></nav>
      </div>
   </div>
</div>
<div class="body-overlay"></div>
