{{-- Контакты — заглушки, заменить на настоящие перед запуском --}}
<footer id="contacts">
   <div class="aq-footer-area pt-100" data-bg-color="#F1EFEE">
      <div class="container">
         <div class="aq-footer-widget-wrap">
            <div class="row">
               <div class="col-lg-4 col-sm-6">
                  <div class="aq-footer-widget footer-col-6-1 mb-90">
                     <div class="aq-footer-logo mb-25">
                        <a href="{{ route('home') }}"><img data-width="120" src="{{ asset('bazaro/img/logo/logo.png') }}" alt="{{ config('app.name') }}"></a>
                     </div>
                     <div class="aq-footer-contact-wrap">
                        <div class="aq-footer-contact-item d-flex align-items-start">
                           <a href="mailto:info@example.com">info@example.com</a>
                        </div>
                        <div class="aq-footer-contact-item d-flex align-items-start">
                           <a class="fs-bold" href="tel:+380000000000">+38 (000) 000-00-00</a>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-4 col-sm-6">
                  <div class="aq-footer-widget mb-90">
                     <h4 class="aq-footer-widget-title">Покупцям</h4>
                     <div class="aq-footer-widget-menu">
                        <ul>
                           <li><a href="{{ route('catalog') }}">Каталог</a></li>
                           <li><a href="{{ route('home') }}#delivery">Доставка і оплата</a></li>
                           <li><a href="{{ route('home') }}#delivery">Обмін і повернення</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
               <div class="col-lg-4 col-sm-6">
                  <div class="aq-footer-widget mb-90">
                     <h4 class="aq-footer-widget-title">Доставка</h4>
                     <div class="aq-footer-widget-menu">
                        <ul>
                           <li>Нова пошта по всій Україні</li>
                           <li>Відправка за 1–3 робочі дні</li>
                           <li>Посилка чекає у відділенні 7 днів</li>
                           <li>Оплата при отриманні</li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="aq-copyright-area">
         <div class="container">
            <div class="aq-copyright-border pt-15 pb-15">
               <div class="aq-copyright-text text-center text-md-start pb-20">
                  <p class="mb-0">© {{ date('Y') }} {{ config('app.name') }}</p>
               </div>
            </div>
         </div>
      </div>
   </div>
</footer>
