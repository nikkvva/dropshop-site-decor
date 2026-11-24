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
                           <li><a href="#">Каталог</a></li>
                           <li><a href="#delivery">Доставка і оплата</a></li>
                           <li><a href="#delivery">Обмін і повернення</a></li>
                        </ul>
                     </div>
                  </div>
               </div>
               <div class="col-lg-4 col-sm-6">
                  <div class="aq-footer-widget mb-90">
                     <h4 class="aq-footer-widget-title">Графік роботи</h4>
                     <div class="aq-footer-widget-menu">
                        <ul>
                           <li>Замовлення на сайті — цілодобово</li>
                           <li>Відповідаємо щодня з 9:00 до 20:00</li>
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
               <div class="row align-items-center">
                  <div class="col-md-6">
                     <div class="aq-copyright-text text-center text-md-start pb-20">
                        <p class="mb-0">© {{ date('Y') }} {{ config('app.name') }}</p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="aq-copyright-payment text-center text-md-end pb-20">
                        <img src="{{ asset('bazaro/img/payment/payment.png') }}" alt="Способи оплати">
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</footer>
