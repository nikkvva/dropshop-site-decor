@extends('layouts.app')

@section('title', 'Кошик | '.config('app.name'))
@push('head')
   <meta name="robots" content="noindex">
@endpush

@section('content')

   <div class="aq-breadcrumb-area pt-60 pb-60" data-bg-color="#F9F9F9">
      <div class="container">
         <div class="aq-breadcrumb-wrap text-center">
            <div class="pd-breadcrumb-list mb-10">
               <span><a href="{{ route('home') }}">Головна</a></span>
               <span>/</span>
               <span>Кошик</span>
            </div>
            <h1 class="aq-breadcrumb-title fs-44">Кошик</h1>
         </div>
      </div>
   </div>

   <section class="aq-cart-area pb-120 pt-80">
      <div class="container">
         @if (session('status'))
            <p class="site-alert site-alert-success mb-25">{{ session('status') }}</p>
         @endif
         @if (session('error'))
            <p class="site-alert site-alert-error mb-25">{{ session('error') }}</p>
         @endif

         @if ($lines->isEmpty())
            <div class="text-center py-5">
               <p class="mb-25">Кошик порожній.</p>
               <a href="{{ route('catalog') }}" class="aq-btn-black btn-square">Перейти до каталогу</a>
            </div>
         @else
            <div class="row">
               <div class="col-xl-9 col-lg-8">
                  {{-- одна форма на всю таблицу: количество → «Оновити», кнопка «Видалити» шлёт ту же форму на свой адрес (formaction) --}}
                  <form id="cart-form" method="POST" action="{{ route('cart.update') }}">
                     @csrf
                     {{-- Enter в поле количества жмёт первую кнопку формы — пусть это будет «Оновити», а не «Видалити» --}}
                     <button type="submit" class="visually-hidden" tabindex="-1" aria-hidden="true">Оновити</button>
                     <div class="aq-cart-list mb-25 mr-30">
                        <table class="table">
                           <thead>
                              <tr>
                                 <th colspan="2" class="aq-cart-header-product">Товар</th>
                                 <th class="aq-cart-header-price">Ціна</th>
                                 <th class="aq-cart-header-quantity">Кількість</th>
                                 <th></th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($lines as $line)
                                 @php($product = $line->product)
                                 <tr @class(['site-cart-unavailable' => ! $product->is_available])>
                                    <td class="aq-cart-img"><a href="{{ route('product', $product) }}"><img src="{{ $product->image() }}" alt="{{ $product->name }}"></a></td>
                                    <td class="aq-cart-title">
                                       <a href="{{ route('product', $product) }}">{{ $product->name }}</a>
                                       @unless ($product->is_available)
                                          <div class="site-out-of-stock">Немає в наявності</div>
                                       @endunless
                                    </td>
                                    <td class="aq-cart-price"><span>{{ $product::money($product->price) }}</span></td>
                                    <td class="aq-cart-quantity aq-product-details-quantity">
                                       <div class="aq-product-quantity mt-10 mb-10">
                                          <span class="aq-cart-minus"><svg width="10" height="2" viewBox="0 0 10 2" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                          <input class="aq-cart-input" type="text" inputmode="numeric" name="quantity[{{ $product->id }}]" value="{{ $line->quantity }}" aria-label="Кількість">
                                          <span class="aq-cart-plus"><svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 1V9M1 5H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                       </div>
                                    </td>
                                    <td class="aq-cart-action text-end">
                                       <button type="submit" class="aq-cart-action-btn" formaction="{{ route('cart.remove', $product) }}"><span>Видалити</span></button>
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                     <div class="aq-cart-bottom">
                        <div class="aq-cart-update mr-30 text-md-end">
                           <button type="submit" class="aq-cart-update-btn">Оновити кошик</button>
                        </div>
                     </div>
                  </form>
               </div>

               <div class="col-xl-3 col-lg-4 col-md-6">
                  <div class="aq-cart-checkout-wrapper">
                     <div class="aq-cart-checkout-top d-flex align-items-center justify-content-between">
                        <span class="aq-cart-checkout-top-title">Товари</span>
                        <span class="aq-cart-checkout-top-price">{{ \App\Models\Product::money($total) }}</span>
                     </div>
                     <div class="aq-cart-checkout-shipping">
                        <h2 class="aq-cart-checkout-shipping-title">Доставка</h2>
                        <p class="site-note">Новою поштою за тарифами перевізника, оплачується при отриманні.</p>
                     </div>
                     <div class="aq-cart-checkout-total d-flex align-items-center justify-content-between">
                        <span>Разом</span>
                        <span>{{ \App\Models\Product::money($total) }}</span>
                     </div>
                     <div class="aq-cart-checkout-proceed">
                        @if ($hasUnavailable)
                           <p class="site-out-of-stock mb-10">Видаліть товари, яких немає в наявності.</p>
                        @else
                           <a href="{{ route('checkout') }}" class="aq-cart-checkout-btn w-100">Оформити замовлення</a>
                        @endif
                     </div>
                  </div>
               </div>
            </div>
         @endif
      </div>
   </section>

@endsection

@push('scripts')
   <script>
      // Кнопки −/+ из шаблона меняют количество (jQuery-событие change) — сохраняем корзину сами, с паузой на серию кликов.
      let cartTimer;
      $(document).on('change', '#cart-form .aq-cart-input', function () {
         clearTimeout(cartTimer);
         cartTimer = setTimeout(() => document.getElementById('cart-form').submit(), 700);
      });
   </script>
@endpush
