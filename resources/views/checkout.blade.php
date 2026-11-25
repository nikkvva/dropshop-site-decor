@extends('layouts.app')

@section('title', 'Оформлення замовлення | '.config('app.name'))
@push('head')
   <meta name="robots" content="noindex">
@endpush

@php
   $deliveryType = old('delivery_type', 'branch');
   $payment = old('payment_method', 'cod');
@endphp

@section('content')

   <div class="aq-breadcrumb-area pt-60 pb-60" data-bg-color="#F9F9F9">
      <div class="container">
         <div class="aq-breadcrumb-wrap text-center">
            <div class="pd-breadcrumb-list mb-10">
               <span><a href="{{ route('home') }}">Головна</a></span>
               <span>/</span>
               <span><a href="{{ route('cart') }}">Кошик</a></span>
               <span>/</span>
               <span>Оформлення</span>
            </div>
            <h1 class="aq-breadcrumb-title fs-44">Оформлення замовлення</h1>
         </div>
      </div>
   </div>

   <section class="aq-checkout-area pb-120 pt-80">
      <div class="container">
         <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}"
               data-cities-url="{{ route('np.cities') }}" data-points-url="{{ route('np.points') }}">
            @csrf
            <div class="row">
               <div class="col-lg-7">
                  <div class="aq-checkout-bill-area">
                     <h3 class="aq-checkout-bill-title">Отримувач</h3>
                     <div class="aq-checkout-bill-form">
                        <div class="aq-checkout-bill-inner">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="aq-checkout-input">
                                    <label for="customer_name">Прізвище та ім’я <span>*</span></label>
                                    <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" required maxlength="255" placeholder="Як у паспорті — для отримання посилки">
                                    @error('customer_name')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="aq-checkout-input">
                                    <label for="phone">Телефон <span>*</span></label>
                                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required maxlength="20" placeholder="+380 50 123 45 67">
                                    @error('phone')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="aq-checkout-input">
                                    <label for="email">Email</label>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" placeholder="Необов’язково">
                                    @error('email')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <h3 class="aq-checkout-bill-title mt-30">Доставка Новою поштою</h3>
                     <div class="aq-checkout-bill-form">
                        <div class="aq-checkout-bill-inner">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="aq-checkout-input">
                                    <label for="city">Місто <span>*</span></label>
                                    {{-- подсказки Новой Почты: public/js/checkout.js; без них поле работает как обычный текст --}}
                                    <input id="city" type="text" name="city" value="{{ old('city') }}" list="np-cities" autocomplete="off" required maxlength="255" placeholder="Почніть вводити назву">
                                    <input type="hidden" name="city_ref" value="{{ old('city_ref') }}">
                                    <datalist id="np-cities"></datalist>
                                    @error('city')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="aq-checkout-input">
                                    <label>Спосіб доставки <span>*</span></label>
                                    <div class="site-radio-row">
                                       @foreach (\App\Models\Order::DELIVERY_TYPES as $value => $label)
                                          <label class="site-radio">
                                             <input type="radio" name="delivery_type" value="{{ $value }}" @checked($deliveryType === $value)> {{ $label }}
                                          </label>
                                       @endforeach
                                    </div>
                                    @error('delivery_type')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="aq-checkout-input">
                                    <label for="delivery_point">Відділення або поштомат <span>*</span></label>
                                    <input id="delivery_point" type="text" name="delivery_point" value="{{ old('delivery_point') }}" list="np-points" autocomplete="off" required maxlength="255" placeholder="Номер або адреса">
                                    <datalist id="np-points"></datalist>
                                    @error('delivery_point')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="aq-checkout-input">
                                    <label for="comment">Коментар до замовлення</label>
                                    <textarea id="comment" name="comment" maxlength="2000" placeholder="Необов’язково">{{ old('comment') }}</textarea>
                                    @error('comment')<p class="site-field-error">{{ $message }}</p>@enderror
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="col-lg-5">
                  <div class="aq-checkout-place white-bg">
                     <h3 class="aq-checkout-place-title">Ваше замовлення</h3>
                     <div class="aq-order-info-list">
                        <ul>
                           <li class="aq-order-info-list-header">
                              <h4>Товар</h4>
                              <h4>Сума</h4>
                           </li>
                           @foreach ($lines as $line)
                              <li class="aq-order-info-list-desc">
                                 <p>{{ $line->product->name }} <span> × {{ $line->quantity }}</span></p>
                                 <span>{{ \App\Models\Product::money($line->sum) }}</span>
                              </li>
                           @endforeach
                           <li class="aq-order-info-list-shipping">
                              <span>Доставка</span>
                              <span>за тарифами Нової пошти</span>
                           </li>
                           <li class="aq-order-info-list-total">
                              <span>Разом</span>
                              <span>{{ \App\Models\Product::money($total) }}</span>
                           </li>
                        </ul>
                     </div>

                     <div class="aq-checkout-payment">
                        @foreach (\App\Models\Order::PAYMENT_METHODS as $value => $label)
                           <div class="aq-checkout-payment-item">
                              <input type="radio" id="payment_{{ $value }}" name="payment_method" value="{{ $value }}" @checked($payment === $value)>
                              <label for="payment_{{ $value }}">{{ $label }}</label>
                              <p class="site-payment-note">
                                 {{ $value === 'cod'
                                    ? 'Оплачуєте товар у відділенні при отриманні посилки.'
                                    : 'Після оформлення покажемо реквізити для оплати. Відправляємо після надходження коштів.' }}
                              </p>
                           </div>
                        @endforeach
                        @error('payment_method')<p class="site-field-error">{{ $message }}</p>@enderror
                     </div>

                     <div class="aq-checkout-btn-wrapper">
                        <button type="submit" class="aq-checkout-btn w-100">Підтвердити замовлення</button>
                     </div>
                  </div>
               </div>
            </div>
         </form>
      </div>
   </section>

@endsection

@push('scripts')
   <script src="{{ asset('js/checkout.js') }}"></script>
@endpush
