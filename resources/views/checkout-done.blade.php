@extends('layouts.app')

@section('title', 'Замовлення прийнято | '.config('app.name'))
@push('head')
   <meta name="robots" content="noindex">
@endpush

@section('content')

   <section class="pb-120 pt-80">
      <div class="container">
         <div class="row justify-content-center">
            <div class="col-lg-7">
               <div class="site-done">
                  <h1 class="aq-breadcrumb-title fs-44 mb-15">Дякуємо! Замовлення № {{ $order->id }} прийнято</h1>
                  <p class="mb-25">Менеджер зателефонує на номер {{ $order->phone }}, щоб підтвердити замовлення.</p>

                  @if ($order->payment_method === 'prepaid')
                     <div class="site-payment-box mb-25">
                        <h3 class="mb-10">Оплата на картку</h3>
                        @if ($paymentDetails)
                           <p class="site-pre">{{ $paymentDetails }}</p>
                           <p>Сума: <strong>{{ \App\Models\Product::money($order->total) }}</strong>. У призначенні платежу вкажіть номер замовлення {{ $order->id }}.</p>
                        @else
                           <p>Менеджер надішле реквізити для оплати разом із підтвердженням замовлення.</p>
                        @endif
                     </div>
                  @endif

                  <table class="table site-done-items">
                     <tbody>
                        @foreach ($order->items as $item)
                           <tr>
                              <td>{{ $item->name }} × {{ $item->quantity }}</td>
                              <td class="text-end">{{ \App\Models\Product::money($item->price * $item->quantity) }}</td>
                           </tr>
                        @endforeach
                        <tr>
                           <td><strong>Разом</strong> (без доставки)</td>
                           <td class="text-end"><strong>{{ \App\Models\Product::money($order->total) }}</strong></td>
                        </tr>
                     </tbody>
                  </table>

                  <p class="mb-25">Доставка: {{ $order->city }}, {{ $order->delivery_point }}.</p>
                  <a href="{{ route('catalog') }}" class="aq-btn-black btn-square">Повернутися до каталогу</a>
               </div>
            </div>
         </div>
      </div>
   </section>

@endsection
